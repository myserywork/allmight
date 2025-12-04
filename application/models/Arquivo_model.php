<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model para gerenciamento avançado de arquivos de licitações
 * 
 * Funcionalidades:
 * - Download de arquivos do PNCP
 * - Extração de ZIPs recursivamente
 * - Extração de texto de PDFs
 * - Indexação de conteúdo para busca
 */
class Arquivo_model extends CI_Model {
    
    // Diretório base para arquivos
    private $base_path;
    
    public function __construct() {
        parent::__construct();
        $this->base_path = FCPATH . 'uploads/licitacoes/';
        
        // Garantir que o diretório existe
        if (!is_dir($this->base_path)) {
            mkdir($this->base_path, 0755, true);
        }
    }
    
    /**
     * Obter todos os arquivos de uma licitação com status detalhado
     */
    public function get_arquivos_licitacao($licitacao_id) {
        return $this->db
            ->where('licitacao_id', $licitacao_id)
            ->order_by('tipo_documento', 'ASC')
            ->order_by('sequencial_documento', 'ASC')
            ->get('licitacao_arquivos')
            ->result();
    }
    
    /**
     * Obter arquivo por ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('licitacao_arquivos')->row();
    }
    
    /**
     * Obter estatísticas de arquivos de uma licitação
     */
    public function get_stats_licitacao($licitacao_id) {
        $arquivos = $this->get_arquivos_licitacao($licitacao_id);
        
        $stats = [
            'total' => count($arquivos),
            'baixados' => 0,
            'processados' => 0,
            'com_texto' => 0,
            'tamanho_total' => 0,
            'tipos' => [],
            'arquivos_extraidos' => 0
        ];
        
        foreach ($arquivos as $arq) {
            if ($arq->arquivo_baixado) $stats['baixados']++;
            if ($arq->conteudo_analisado) $stats['processados']++;
            if (!empty($arq->texto_extraido)) $stats['com_texto']++;
            if ($arq->arquivo_tamanho) $stats['tamanho_total'] += $arq->arquivo_tamanho;
            if ($arq->arquivo_origem_id) $stats['arquivos_extraidos']++;
            
            $tipo = $arq->tipo_documento ?: 'Outros';
            if (!isset($stats['tipos'][$tipo])) {
                $stats['tipos'][$tipo] = 0;
            }
            $stats['tipos'][$tipo]++;
        }
        
        return $stats;
    }
    
    /**
     * Baixar um arquivo específico
     */
    public function download_arquivo($arquivo_id) {
        $arquivo = $this->get_by_id($arquivo_id);
        
        if (!$arquivo) {
            return ['success' => false, 'message' => 'Arquivo não encontrado'];
        }
        
        if ($arquivo->arquivo_baixado && file_exists($arquivo->arquivo_local_path)) {
            return [
                'success' => true, 
                'message' => 'Arquivo já baixado',
                'path' => $arquivo->arquivo_local_path,
                'arquivo' => $arquivo
            ];
        }
        
        // Criar diretório da licitação
        $dir_licitacao = $this->base_path . $arquivo->licitacao_id . '/';
        if (!is_dir($dir_licitacao)) {
            mkdir($dir_licitacao, 0755, true);
        }
        
        // Baixar arquivo
        $url = $arquivo->url_download ?: $arquivo->uri_original;
        
        if (!$url) {
            return ['success' => false, 'message' => 'URL de download não disponível'];
        }
        
        // Determinar nome do arquivo
        $filename = $this->sanitize_filename($arquivo->titulo);
        if (!$filename) {
            $filename = 'arquivo_' . $arquivo->id;
        }
        
        // Adicionar extensão se não tiver
        if (!preg_match('/\.\w{2,4}$/', $filename)) {
            $filename .= '.pdf'; // Assume PDF por padrão
        }
        
        $local_path = $dir_licitacao . $filename;
        
        // Baixar usando cURL
        $result = $this->download_file($url, $local_path);
        
        if ($result['success']) {
            // Detectar tipo real do arquivo pelos magic bytes
            $tipo_real = $this->detectar_tipo_real($local_path);
            
            // Se o arquivo foi salvo com extensão errada, corrigir
            if ($tipo_real === 'zip' && !preg_match('/\.zip$/i', $local_path)) {
                $novo_path = preg_replace('/\.\w+$/', '.zip', $local_path);
                rename($local_path, $novo_path);
                $local_path = $novo_path;
            }
            
            // Atualizar banco
            $this->db->where('id', $arquivo_id)->update('licitacao_arquivos', [
                'arquivo_local_path' => $local_path,
                'arquivo_baixado' => 1,
                'arquivo_tamanho' => filesize($local_path),
                'arquivo_hash' => md5_file($local_path),
                'data_download' => date('Y-m-d H:i:s')
            ]);
            
            // Detectar tipo real do arquivo
            $mime_type = $this->get_mime_type($local_path);
            
            return [
                'success' => true,
                'message' => 'Arquivo baixado com sucesso',
                'path' => $local_path,
                'size' => filesize($local_path),
                'mime_type' => $mime_type,
                'is_zip' => $this->is_archive($local_path),
                'is_pdf' => $this->is_pdf($local_path),
                'tipo_real' => $tipo_real,
                'arquivo' => $this->get_by_id($arquivo_id)
            ];
        }
        
        return $result;
    }
    
    /**
     * Baixar todos os arquivos de uma licitação
     */
    public function download_todos_arquivos($licitacao_id) {
        $arquivos = $this->get_arquivos_licitacao($licitacao_id);
        
        $resultados = [
            'success' => true,
            'total' => count($arquivos),
            'baixados' => 0,
            'erros' => 0,
            'zips_encontrados' => [],
            'detalhes' => []
        ];
        
        foreach ($arquivos as $arquivo) {
            $result = $this->download_arquivo($arquivo->id);
            
            if ($result['success']) {
                $resultados['baixados']++;
                
                if (isset($result['is_zip']) && $result['is_zip']) {
                    $resultados['zips_encontrados'][] = [
                        'id' => $arquivo->id,
                        'path' => $result['path']
                    ];
                }
            } else {
                $resultados['erros']++;
            }
            
            $resultados['detalhes'][] = [
                'arquivo_id' => $arquivo->id,
                'titulo' => $arquivo->titulo,
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }
        
        return $resultados;
    }
    
    /**
     * Extrair arquivo ZIP recursivamente
     */
    public function extrair_zip($arquivo_id) {
        $arquivo = $this->get_by_id($arquivo_id);
        
        if (!$arquivo || !$arquivo->arquivo_baixado || !file_exists($arquivo->arquivo_local_path)) {
            return ['success' => false, 'message' => 'Arquivo não disponível para extração'];
        }
        
        if (!$this->is_archive($arquivo->arquivo_local_path)) {
            return ['success' => false, 'message' => 'Arquivo não é um arquivo compactado'];
        }
        
        $extract_dir = dirname($arquivo->arquivo_local_path) . '/extraidos_' . $arquivo->id . '/';
        
        if (!is_dir($extract_dir)) {
            mkdir($extract_dir, 0755, true);
        }
        
        // Extrair
        $result = $this->extract_archive($arquivo->arquivo_local_path, $extract_dir);
        
        if (!$result['success']) {
            return $result;
        }
        
        // Processar arquivos extraídos
        $arquivos_extraidos = $this->processar_arquivos_extraidos(
            $extract_dir, 
            $arquivo->licitacao_id, 
            $arquivo->id
        );
        
        return [
            'success' => true,
            'message' => 'Arquivo extraído com sucesso',
            'arquivos_extraidos' => $arquivos_extraidos,
            'diretorio' => $extract_dir
        ];
    }
    
    /**
     * Processar arquivos extraídos e inserir no banco
     */
    private function processar_arquivos_extraidos($diretorio, $licitacao_id, $arquivo_origem_id, $nivel = 0) {
        $arquivos = [];
        $max_nivel = 3; // Evitar loops infinitos
        
        if ($nivel > $max_nivel) {
            return $arquivos;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($diretorio, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filepath = $file->getPathname();
                $filename = $file->getFilename();
                $extension = strtolower($file->getExtension());
                
                // Detectar tipo
                $tipo = $this->detectar_tipo_arquivo($filepath);
                
                // Inserir no banco
                $data = [
                    'licitacao_id' => $licitacao_id,
                    'titulo' => $filename,
                    'tipo_documento' => $tipo,
                    'tipo_documento_descricao' => 'Extraído de arquivo compactado',
                    'arquivo_local_path' => $filepath,
                    'arquivo_tamanho' => filesize($filepath),
                    'arquivo_hash' => md5_file($filepath),
                    'arquivo_baixado' => 1,
                    'data_download' => date('Y-m-d H:i:s'),
                    'arquivo_origem_id' => $arquivo_origem_id
                ];
                
                $this->db->insert('licitacao_arquivos', $data);
                $novo_id = $this->db->insert_id();
                
                $arquivo_info = [
                    'id' => $novo_id,
                    'titulo' => $filename,
                    'tipo' => $tipo,
                    'path' => $filepath,
                    'tamanho' => filesize($filepath)
                ];
                
                $arquivos[] = $arquivo_info;
                
                // Se for outro ZIP, extrair recursivamente
                if ($this->is_archive($filepath)) {
                    $sub_extraidos = $this->extrair_zip($novo_id);
                    if ($sub_extraidos['success'] && !empty($sub_extraidos['arquivos_extraidos'])) {
                        $arquivos = array_merge($arquivos, $sub_extraidos['arquivos_extraidos']);
                    }
                }
            }
        }
        
        return $arquivos;
    }
    
    /**
     * Extrair texto de um PDF
     */
    public function extrair_texto_pdf($arquivo_id) {
        $arquivo = $this->get_by_id($arquivo_id);
        
        if (!$arquivo || !$arquivo->arquivo_baixado || !file_exists($arquivo->arquivo_local_path)) {
            return ['success' => false, 'message' => 'Arquivo não disponível'];
        }
        
        if (!$this->is_pdf($arquivo->arquivo_local_path)) {
            return ['success' => false, 'message' => 'Arquivo não é um PDF'];
        }
        
        // Tentar extrair texto usando pdftotext (se disponível)
        $texto = $this->extract_pdf_text($arquivo->arquivo_local_path);
        
        if ($texto === false || empty(trim($texto))) {
            // Tentar método alternativo com PHP
            $texto = $this->extract_pdf_text_php($arquivo->arquivo_local_path);
        }
        
        if (!empty(trim($texto))) {
            // Salvar texto no banco
            $this->db->where('id', $arquivo_id)->update('licitacao_arquivos', [
                'texto_extraido' => $texto,
                'conteudo_analisado' => 1
            ]);
            
            // Extrair palavras-chave
            $keywords = $this->extrair_keywords_texto($texto);
            if (!empty($keywords)) {
                $this->db->where('id', $arquivo_id)->update('licitacao_arquivos', [
                    'palavras_chave' => json_encode($keywords)
                ]);
            }
            
            return [
                'success' => true,
                'message' => 'Texto extraído com sucesso',
                'texto' => $texto,
                'caracteres' => strlen($texto),
                'keywords' => $keywords ?? []
            ];
        }
        
        return [
            'success' => false, 
            'message' => 'Não foi possível extrair texto do PDF (pode ser um PDF de imagem)'
        ];
    }
    
    /**
     * Processar todos os PDFs de uma licitação
     */
    public function processar_todos_pdfs($licitacao_id) {
        $arquivos = $this->db
            ->where('licitacao_id', $licitacao_id)
            ->where('arquivo_baixado', 1)
            ->where('conteudo_analisado', 0)
            ->get('licitacao_arquivos')
            ->result();
        
        $resultados = [
            'success' => true,
            'total' => count($arquivos),
            'processados' => 0,
            'com_texto' => 0,
            'erros' => 0,
            'detalhes' => []
        ];
        
        foreach ($arquivos as $arquivo) {
            if (!$this->is_pdf($arquivo->arquivo_local_path)) {
                continue;
            }
            
            $result = $this->extrair_texto_pdf($arquivo->id);
            $resultados['processados']++;
            
            if ($result['success']) {
                $resultados['com_texto']++;
            } else {
                $resultados['erros']++;
            }
            
            $resultados['detalhes'][] = [
                'arquivo_id' => $arquivo->id,
                'titulo' => $arquivo->titulo,
                'success' => $result['success'],
                'message' => $result['message']
            ];
        }
        
        return $resultados;
    }
    
    /**
     * Obter contexto completo dos documentos para proposta
     */
    public function get_contexto_documentos($licitacao_id) {
        $arquivos = $this->db
            ->where('licitacao_id', $licitacao_id)
            ->where('texto_extraido IS NOT NULL', null, false)
            ->order_by('tipo_documento', 'ASC')
            ->get('licitacao_arquivos')
            ->result();
        
        $contexto = [
            'tem_edital' => false,
            'tem_termo_referencia' => false,
            'tem_minuta_contrato' => false,
            'documentos' => [],
            'texto_completo' => '',
            'keywords_unificadas' => [],
            'resumo' => ''
        ];
        
        foreach ($arquivos as $arquivo) {
            $tipo_lower = strtolower($arquivo->tipo_documento ?: '');
            
            if (strpos($tipo_lower, 'edital') !== false) {
                $contexto['tem_edital'] = true;
            }
            if (strpos($tipo_lower, 'termo') !== false || strpos($tipo_lower, 'referência') !== false) {
                $contexto['tem_termo_referencia'] = true;
            }
            if (strpos($tipo_lower, 'contrato') !== false || strpos($tipo_lower, 'minuta') !== false) {
                $contexto['tem_minuta_contrato'] = true;
            }
            
            $doc = [
                'id' => $arquivo->id,
                'titulo' => $arquivo->titulo,
                'tipo' => $arquivo->tipo_documento,
                'texto' => $arquivo->texto_extraido,
                'resumo' => $this->gerar_resumo($arquivo->texto_extraido, 500)
            ];
            
            $contexto['documentos'][] = $doc;
            $contexto['texto_completo'] .= "\n\n=== " . $arquivo->titulo . " ===\n" . $arquivo->texto_extraido;
            
            // Merge keywords
            if ($arquivo->palavras_chave) {
                $kws = json_decode($arquivo->palavras_chave, true) ?: [];
                $contexto['keywords_unificadas'] = array_unique(array_merge($contexto['keywords_unificadas'], $kws));
            }
        }
        
        // Gerar resumo geral
        if (!empty($contexto['texto_completo'])) {
            $contexto['resumo'] = $this->gerar_resumo($contexto['texto_completo'], 2000);
        }
        
        return $contexto;
    }
    
    // ========== MÉTODOS AUXILIARES ==========
    
    /**
     * Download de arquivo via cURL
     */
    private function download_file($url, $local_path) {
        $ch = curl_init();
        $fp = fopen($local_path, 'w+');
        
        if (!$fp) {
            return ['success' => false, 'message' => 'Não foi possível criar arquivo local'];
        }
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_FILE => $fp,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 120,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        
        $success = curl_exec($ch);
        $error = curl_error($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        fclose($fp);
        
        if (!$success || $http_code >= 400) {
            unlink($local_path);
            return ['success' => false, 'message' => "Erro no download: HTTP $http_code - $error"];
        }
        
        return ['success' => true];
    }
    
    /**
     * Extrair arquivo compactado
     */
    private function extract_archive($archive_path, $extract_to) {
        $extension = strtolower(pathinfo($archive_path, PATHINFO_EXTENSION));
        
        if ($extension === 'zip') {
            $zip = new ZipArchive();
            if ($zip->open($archive_path) === true) {
                $zip->extractTo($extract_to);
                $zip->close();
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'Erro ao abrir arquivo ZIP'];
        }
        
        if ($extension === 'rar') {
            // Tentar usar unrar se disponível
            $cmd = "unrar x -o+ " . escapeshellarg($archive_path) . " " . escapeshellarg($extract_to);
            exec($cmd, $output, $return_code);
            
            if ($return_code === 0) {
                return ['success' => true];
            }
            return ['success' => false, 'message' => 'Erro ao extrair RAR ou unrar não disponível'];
        }
        
        return ['success' => false, 'message' => 'Formato de arquivo não suportado'];
    }
    
    /**
     * Extrair texto de PDF usando pdftotext
     */
    private function extract_pdf_text($pdf_path) {
        // Verificar se pdftotext está disponível
        $pdftotext = 'pdftotext'; // ou caminho completo
        
        // Criar arquivo temporário para output
        $temp_file = sys_get_temp_dir() . '/pdf_' . uniqid() . '.txt';
        
        // Comando para extrair texto
        $cmd = sprintf('%s -layout %s %s 2>&1', 
            escapeshellcmd($pdftotext),
            escapeshellarg($pdf_path),
            escapeshellarg($temp_file)
        );
        
        exec($cmd, $output, $return_code);
        
        if ($return_code === 0 && file_exists($temp_file)) {
            $text = file_get_contents($temp_file);
            unlink($temp_file);
            return $text;
        }
        
        // Tentar com xpdf no Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $xpdf_path = 'C:/xampp/xpdf/bin64/pdftotext.exe';
            if (file_exists($xpdf_path)) {
                $cmd = sprintf('%s -layout %s %s 2>&1', 
                    escapeshellarg($xpdf_path),
                    escapeshellarg($pdf_path),
                    escapeshellarg($temp_file)
                );
                exec($cmd, $output, $return_code);
                
                if ($return_code === 0 && file_exists($temp_file)) {
                    $text = file_get_contents($temp_file);
                    unlink($temp_file);
                    return $text;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Extrair texto de PDF usando PHP puro (método básico)
     */
    private function extract_pdf_text_php($pdf_path) {
        // Primeiro tentar com a biblioteca PdfParser (smalot/pdfparser)
        try {
            // Carregar autoload do Composer
            $autoload = FCPATH . 'vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
                
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($pdf_path);
                $text = $pdf->getText();
                
                if (!empty(trim($text))) {
                    return $text;
                }
            }
        } catch (\Exception $e) {
            log_message('debug', 'PdfParser error: ' . $e->getMessage());
        }
        
        // Fallback: método básico de extração
        $content = file_get_contents($pdf_path);
        $text = '';
        
        // Tentar extrair streams de texto
        if (preg_match_all('/stream\s*\n(.+?)\nendstream/s', $content, $matches)) {
            foreach ($matches[1] as $stream) {
                // Tentar decodificar FlateDecode
                $decoded = @gzuncompress($stream);
                if ($decoded !== false) {
                    // Extrair texto entre parênteses (strings PDF)
                    if (preg_match_all('/\(([^)]+)\)/', $decoded, $text_matches)) {
                        $text .= implode(' ', $text_matches[1]) . "\n";
                    }
                    // Extrair texto entre < > (hex strings)
                    if (preg_match_all('/<([0-9A-Fa-f]+)>/', $decoded, $hex_matches)) {
                        foreach ($hex_matches[1] as $hex) {
                            $text .= hex2bin($hex) . ' ';
                        }
                    }
                }
            }
        }
        
        // Limpar texto
        $text = preg_replace('/[^\x20-\x7E\xA0-\xFF\n\r]/', '', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }
    
    /**
     * Extrair keywords de um texto
     */
    private function extrair_keywords_texto($texto) {
        // Palavras comuns a ignorar
        $stopwords = ['de', 'da', 'do', 'das', 'dos', 'e', 'ou', 'a', 'o', 'as', 'os', 'um', 'uma',
            'para', 'com', 'sem', 'por', 'em', 'no', 'na', 'nos', 'nas', 'ao', 'aos', 'à', 'às',
            'que', 'se', 'não', 'mais', 'como', 'mas', 'foi', 'ser', 'são', 'está', 'tem',
            'será', 'deve', 'pode', 'sobre', 'entre', 'quando', 'onde', 'qual', 'quais',
            'seu', 'sua', 'seus', 'suas', 'este', 'esta', 'esse', 'essa', 'isso', 'isto',
            'artigo', 'item', 'inciso', 'parágrafo', 'alínea', 'lei', 'decreto'];
        
        // Tokenizar
        $texto = mb_strtolower($texto);
        $texto = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $texto);
        $palavras = preg_split('/\s+/', $texto);
        
        // Contar frequência
        $frequencia = [];
        foreach ($palavras as $palavra) {
            if (strlen($palavra) > 3 && !in_array($palavra, $stopwords)) {
                $frequencia[$palavra] = ($frequencia[$palavra] ?? 0) + 1;
            }
        }
        
        // Ordenar por frequência
        arsort($frequencia);
        
        // Retornar top 30 keywords
        return array_slice(array_keys($frequencia), 0, 30);
    }
    
    /**
     * Gerar resumo de texto
     */
    private function gerar_resumo($texto, $max_chars = 500) {
        if (strlen($texto) <= $max_chars) {
            return $texto;
        }
        
        // Tentar cortar em uma frase completa
        $resumo = substr($texto, 0, $max_chars);
        $ultimo_ponto = strrpos($resumo, '.');
        
        if ($ultimo_ponto !== false && $ultimo_ponto > $max_chars * 0.7) {
            $resumo = substr($resumo, 0, $ultimo_ponto + 1);
        } else {
            $resumo .= '...';
        }
        
        return $resumo;
    }
    
    /**
     * Detectar tipo real do arquivo pelos magic bytes
     */
    private function detectar_tipo_real($filepath) {
        if (!file_exists($filepath)) return 'unknown';
        
        $fh = fopen($filepath, 'rb');
        $bytes = fread($fh, 8);
        fclose($fh);
        
        // ZIP (PK..)
        if (substr($bytes, 0, 4) === "PK\x03\x04") return 'zip';
        
        // PDF (%PDF)
        if (substr($bytes, 0, 4) === '%PDF') return 'pdf';
        
        // RAR (Rar!)
        if (substr($bytes, 0, 4) === "Rar!") return 'rar';
        
        // 7z
        if (substr($bytes, 0, 6) === "7z\xBC\xAF\x27\x1C") return '7z';
        
        // GZip
        if (substr($bytes, 0, 2) === "\x1f\x8b") return 'gz';
        
        // JPEG
        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") return 'jpg';
        
        // PNG
        if (substr($bytes, 0, 4) === "\x89PNG") return 'png';
        
        // Word DOC
        if (substr($bytes, 0, 4) === "\xD0\xCF\x11\xE0") return 'doc';
        
        // Word DOCX / XLSX / PPTX (são ZIPs na verdade)
        // Já detectado como ZIP acima
        
        return 'unknown';
    }
    
    /**
     * Verificar se é arquivo compactado
     */
    public function is_archive($filepath) {
        if (!file_exists($filepath)) return false;
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if (in_array($extension, ['zip', 'rar', '7z', 'tar', 'gz'])) {
            return true;
        }
        
        // Verificar magic bytes
        $fh = fopen($filepath, 'rb');
        $bytes = fread($fh, 4);
        fclose($fh);
        
        // ZIP magic number
        if ($bytes === "PK\x03\x04") return true;
        
        // RAR magic number
        if (substr($bytes, 0, 3) === "Rar") return true;
        
        return false;
    }
    
    /**
     * Verificar se é PDF
     */
    private function is_pdf($filepath) {
        if (!file_exists($filepath)) return false;
        
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        if ($extension === 'pdf') return true;
        
        // Verificar magic bytes
        $fh = fopen($filepath, 'rb');
        $bytes = fread($fh, 4);
        fclose($fh);
        
        return $bytes === '%PDF';
    }
    
    /**
     * Obter MIME type real do arquivo
     */
    private function get_mime_type($filepath) {
        if (function_exists('mime_content_type')) {
            return mime_content_type($filepath);
        }
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $filepath);
        finfo_close($finfo);
        
        return $mime;
    }
    
    /**
     * Detectar tipo de arquivo
     */
    private function detectar_tipo_arquivo($filepath) {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $filename = strtolower(basename($filepath));
        
        // Por extensão
        $tipos = [
            'pdf' => 'Documento PDF',
            'doc' => 'Documento Word',
            'docx' => 'Documento Word',
            'xls' => 'Planilha Excel',
            'xlsx' => 'Planilha Excel',
            'odt' => 'Documento OpenDocument',
            'ods' => 'Planilha OpenDocument',
            'jpg' => 'Imagem',
            'jpeg' => 'Imagem',
            'png' => 'Imagem',
            'zip' => 'Arquivo Compactado',
            'rar' => 'Arquivo Compactado'
        ];
        
        if (isset($tipos[$extension])) {
            return $tipos[$extension];
        }
        
        // Por nome
        if (strpos($filename, 'edital') !== false) return 'Edital';
        if (strpos($filename, 'termo') !== false) return 'Termo de Referência';
        if (strpos($filename, 'contrato') !== false) return 'Minuta de Contrato';
        if (strpos($filename, 'ata') !== false) return 'Ata';
        if (strpos($filename, 'anexo') !== false) return 'Anexo';
        
        return 'Outros';
    }
    
    /**
     * Sanitizar nome de arquivo
     */
    private function sanitize_filename($filename) {
        // Remover caracteres especiais
        $filename = preg_replace('/[^\w\.\-\s]/u', '', $filename);
        $filename = preg_replace('/\s+/', '_', $filename);
        $filename = trim($filename, '_');
        
        return $filename ?: null;
    }
    
    /**
     * Buscar arquivos por texto
     */
    public function buscar_por_texto($licitacao_id, $termo) {
        return $this->db
            ->where('licitacao_id', $licitacao_id)
            ->like('texto_extraido', $termo)
            ->get('licitacao_arquivos')
            ->result();
    }
}
