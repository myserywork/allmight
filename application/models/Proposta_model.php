<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Proposta_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lista todas as propostas com filtros
     */
    public function get_all($limit = 20, $offset = 0, $filters = []) {
        $this->db->select('p.*, 
                          e.nome as empresa_nome,
                          e.cnpj as empresa_cnpj,
                          l.titulo as licitacao_titulo,
                          l.numero_controle_pncp,
                          l.orgao_nome,
                          COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id)) as licitacao_valor_estimado,
                          m.score_total', FALSE)
            ->from('propostas p')
            ->join('empresas e', 'e.id = p.empresa_id')
            ->join('licitacoes l', 'l.id = p.licitacao_id')
            ->join('matches m', 'm.id = p.match_id', 'left');
        
        if (!empty($filters['empresa_id'])) {
            $this->db->where('p.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('p.status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.titulo', $filters['search']);
            $this->db->or_like('l.titulo', $filters['search']);
            $this->db->or_like('e.nome', $filters['search']);
            $this->db->or_like('p.numero_proposta', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['gerado_ia'])) {
            $this->db->where('p.gerado_por_ia', $filters['gerado_ia'] === 'true');
        }
        
        $this->db->order_by('p.data_criacao', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    /**
     * Conta total de propostas com filtros
     */
    public function count_all($filters = []) {
        $this->db->from('propostas p')
            ->join('empresas e', 'e.id = p.empresa_id')
            ->join('licitacoes l', 'l.id = p.licitacao_id');
        
        if (!empty($filters['empresa_id'])) {
            $this->db->where('p.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('p.status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('p.titulo', $filters['search']);
            $this->db->or_like('l.titulo', $filters['search']);
            $this->db->or_like('e.nome', $filters['search']);
            $this->db->or_like('p.numero_proposta', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['gerado_ia'])) {
            $this->db->where('p.gerado_por_ia', $filters['gerado_ia'] === 'true');
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Busca proposta por ID
     */
    public function get_by_id($id) {
        return $this->db->select('p.*, 
                                 e.nome as empresa_nome,
                                 e.cnpj as empresa_cnpj,
                                 e.logo as empresa_logo,
                                 e.logradouro as empresa_logradouro,
                                 e.numero as empresa_numero,
                                 e.complemento as empresa_complemento,
                                 e.bairro as empresa_bairro,
                                 e.cidade as empresa_cidade,
                                 e.uf as empresa_uf,
                                 e.cep as empresa_cep,
                                 e.telefone as empresa_telefone,
                                 e.email as empresa_email,
                                 e.porte as empresa_porte,
                                 l.titulo as licitacao_titulo,
                                 l.numero_controle_pncp,
                                 l.orgao_nome,
                                 l.objeto,
                                 l.valor_estimado,
                                 m.score_total')
            ->from('propostas p')
            ->join('empresas e', 'e.id = p.empresa_id')
            ->join('licitacoes l', 'l.id = p.licitacao_id')
            ->join('matches m', 'm.id = p.match_id', 'left')
            ->where('p.id', $id)
            ->get()
            ->row();
    }

    /**
     * Busca proposta por match_id
     */
    public function get_by_match($match_id) {
        return $this->db->where('match_id', $match_id)
            ->order_by('data_criacao', 'DESC')
            ->get('propostas')
            ->row();
    }

    /**
     * Cria nova proposta
     */
    public function create($data) {
        $proposta = [
            'id' => $this->_generate_uuid(),
            'match_id' => $data['match_id'] ?? null,
            'empresa_id' => $data['empresa_id'],
            'licitacao_id' => $data['licitacao_id'],
            'titulo' => $data['titulo'] ?? null,
            'numero_proposta' => $data['numero_proposta'] ?? null,
            'versao' => $data['versao'] ?? 1,
            'valor_total' => $data['valor_total'] ?? 0,
            'desconto_percentual' => $data['desconto_percentual'] ?? null,
            'valor_desconto' => $data['valor_desconto'] ?? null,
            'valor_final' => $data['valor_final'] ?? $data['valor_total'] ?? 0,
            'prazo_entrega' => $data['prazo_entrega'] ?? null,
            'condicoes_pagamento' => $data['condicoes_pagamento'] ?? null,
            'validade_proposta' => $data['validade_proposta'] ?? null,
            'status' => $data['status'] ?? 'RASCUNHO',
            'conteudo_html' => $data['conteudo_html'] ?? null,
            'gerado_por_ia' => $data['gerado_por_ia'] ?? false,
            'prompt_ia' => $data['prompt_ia'] ?? null,
            'data_geracao_ia' => $data['data_geracao_ia'] ?? null,
            'observacoes_internas' => $data['observacoes_internas'] ?? null,
            'observacoes_cliente' => $data['observacoes_cliente'] ?? null,
            'criado_por_usuario_id' => $data['criado_por_usuario_id'] ?? null
        ];
        
        if ($this->db->insert('propostas', $proposta)) {
            return $proposta['id'];
        }
        
        return false;
    }

    /**
     * Atualiza proposta
     */
    public function update($id, $data) {
        $update_data = [];
        
        $allowed_fields = [
            'titulo', 'numero_proposta', 'versao', 'valor_total', 'desconto_percentual',
            'valor_desconto', 'valor_final', 'prazo_entrega', 'condicoes_pagamento',
            'validade_proposta', 'status', 'conteudo_html', 'observacoes_internas',
            'observacoes_cliente', 'proposta_tecnica_path', 'proposta_preco_path',
            'documentos_habilitacao_path'
        ];
        
        foreach ($allowed_fields as $field) {
            if (isset($data[$field])) {
                $update_data[$field] = $data[$field];
            }
        }
        
        if (!empty($update_data)) {
            $this->db->where('id', $id);
            return $this->db->update('propostas', $update_data);
        }
        
        return false;
    }

    /**
     * Deleta proposta
     */
    public function delete($id) {
        return $this->db->delete('propostas', ['id' => $id]);
    }

    /**
     * Atualiza status da proposta
     */
    public function atualizar_status($id, $status, $observacao = null) {
        $data = ['status' => $status];
        
        if ($status === 'ENVIADA') {
            $data['data_envio'] = date('Y-m-d H:i:s');
        }
        
        if ($observacao) {
            $data['observacoes_internas'] = $observacao;
        }
        
        $this->db->where('id', $id);
        return $this->db->update('propostas', $data);
    }

    /**
     * Estatísticas de propostas
     */
    public function get_stats() {
        $total = $this->db->count_all('propostas');
        
        $rascunhos = $this->db->where('status', 'RASCUNHO')
            ->count_all_results('propostas');
        
        $em_elaboracao = $this->db->where('status', 'EM_ELABORACAO')
            ->count_all_results('propostas');
        
        $aprovadas = $this->db->where('status', 'APROVADA')
            ->count_all_results('propostas');
        
        $enviadas = $this->db->where('status', 'ENVIADA')
            ->count_all_results('propostas');
        
        $vencedoras = $this->db->where('status', 'VENCEDORA')
            ->count_all_results('propostas');
        
        $geradas_ia = $this->db->where('gerado_por_ia', true)
            ->count_all_results('propostas');
        
        // Valor total das propostas
        $valor_total = $this->db->select_sum('valor_final')
            ->get('propostas')
            ->row()
            ->valor_final ?: 0;
        
        // Valor médio
        $valor_medio = $total > 0 ? $valor_total / $total : 0;
        
        // Por status
        $por_status = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')
            ->get('propostas')
            ->result();
        
        // Taxa de sucesso
        $taxa_sucesso = $enviadas > 0 ? ($vencedoras / $enviadas) * 100 : 0;
        
        return [
            'total' => $total,
            'rascunhos' => $rascunhos,
            'em_elaboracao' => $em_elaboracao,
            'aprovadas' => $aprovadas,
            'enviadas' => $enviadas,
            'vencedoras' => $vencedoras,
            'geradas_ia' => $geradas_ia,
            'valor_total' => $valor_total,
            'valor_medio' => $valor_medio,
            'por_status' => $por_status,
            'taxa_sucesso' => $taxa_sucesso,
            'percentual_ia' => $total > 0 ? ($geradas_ia / $total) * 100 : 0
        ];
    }

    /**
     * Gera proposta com IA (Gemini)
     */
    public function gerar_com_ia($match_id) {
        // Buscar dados do match
        $this->load->model('Match_model');
        $match = $this->Match_model->get_by_id($match_id);
        
        if (!$match) {
            return ['success' => false, 'message' => 'Match não encontrado'];
        }
        
        // Buscar dados completos da licitação e empresa
        $this->load->model('Licitacao_model');
        $licitacao = $this->Licitacao_model->get_by_id($match->licitacao_id);
        $itens = $this->Licitacao_model->get_itens($match->licitacao_id);
        
        $empresa = $this->db->get_where('empresas', ['id' => $match->empresa_id])->row();
        
        // Preparar prompt para Gemini
        $prompt = $this->_preparar_prompt_proposta($licitacao, $itens, $empresa, $match);
        
        // Chamar API Gemini
        $resposta_ia = $this->_chamar_gemini_api($prompt);
        
        if (!$resposta_ia['success']) {
            return $resposta_ia;
        }
        
        // Processar resposta
        $conteudo_html = $this->_processar_resposta_proposta($resposta_ia['conteudo']);
        
        // Criar proposta
        $proposta_id = $this->create([
            'match_id' => $match_id,
            'empresa_id' => $match->empresa_id,
            'licitacao_id' => $match->licitacao_id,
            'titulo' => 'Proposta para ' . $licitacao->titulo,
            'valor_total' => $licitacao->valor_estimado ?? 0,
            'valor_final' => $licitacao->valor_estimado ?? 0,
            'prazo_entrega' => '30 dias',
            'condicoes_pagamento' => 'Conforme edital',
            'validade_proposta' => '60 dias',
            'status' => 'RASCUNHO',
            'conteudo_html' => $conteudo_html,
            'gerado_por_ia' => true,
            'prompt_ia' => $prompt,
            'data_geracao_ia' => date('Y-m-d H:i:s')
        ]);
        
        if ($proposta_id) {
            return [
                'success' => true,
                'message' => 'Proposta gerada com sucesso!',
                'proposta_id' => $proposta_id
            ];
        }
        
        return ['success' => false, 'message' => 'Erro ao criar proposta'];
    }

    /**
     * Prepara prompt para geração de proposta
     */
    private function _preparar_prompt_proposta($licitacao, $itens, $empresa, $match) {
        // Preparar dados dos itens com cálculo de valores
        $itens_detalhados = [];
        $valor_total = 0;
        
        foreach ($itens as $item) {
            $valor_unit = $item->valor_unitario_estimado ?? 0;
            $valor_total_item = $valor_unit * $item->quantidade;
            $valor_total += $valor_total_item;
            
            $itens_detalhados[] = [
                'numero' => $item->numero_item ?? 0,
                'descricao' => $item->descricao ?? '',
                'quantidade' => $item->quantidade ?? 0,
                'unidade' => $item->unidade_medida ?? 'UN',
                'valor_unitario' => $valor_unit,
                'valor_total' => $valor_total_item
            ];
        }
        
        // Montar texto dos itens
        $itens_texto = '';
        foreach ($itens_detalhados as $item) {
            $itens_texto .= "\n" . $item['numero'] . " | ";
            $itens_texto .= $item['descricao'] . " | ";
            $itens_texto .= "Qtd: " . $item['quantidade'] . " " . $item['unidade'] . " | ";
            if ($item['valor_unitario'] > 0) {
                $itens_texto .= "R$ " . number_format($item['valor_unitario'], 2, ',', '.') . " | ";
                $itens_texto .= "Total: R$ " . number_format($item['valor_total'], 2, ',', '.');
            }
        }
        
        // URL da logo (se existir)
        $logo_url = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : '';
        
        // Data atual para contextualização
        $data_atual = date('d/m/Y');
        $data_hora_atual = date('d/m/Y H:i:s');
        $dia_semana = strftime('%A', time());
        
        $prompt = <<<PROMPT
Você é um especialista em elaboração de propostas comerciais para licitações públicas brasileiras.

**CONTEXTO TEMPORAL:**
- Data atual: {$data_atual}
- Data e hora da solicitação: {$data_hora_atual}
- Use esta data como referência para a proposta

Crie uma proposta comercial profissional, detalhada e completa em HTML para a seguinte licitação:

**DADOS DA EMPRESA:**
- Razão Social: {$empresa->nome}
- CNPJ: {$empresa->cnpj}
- Endereço Completo: {$empresa->logradouro}, {$empresa->numero} {$empresa->complemento}, {$empresa->bairro}, {$empresa->cidade}/{$empresa->uf}, CEP: {$empresa->cep}
- Telefone: {$empresa->telefone}
- E-mail: {$empresa->email}
- Porte: {$empresa->porte}
- Logo URL: {$logo_url}

**DADOS DA LICITAÇÃO:**
- Título: {$licitacao->titulo}
- Órgão Contratante: {$licitacao->orgao_nome}
- CNPJ Órgão: {$licitacao->orgao_cnpj}
- Objeto: {$licitacao->objeto}
- Modalidade: {$licitacao->modalidade}
- Número de Controle PNCP: {$licitacao->numero_controle_pncp}
- Data de Publicação: {$licitacao->data_publicacao}
- Data de Abertura: {$licitacao->data_abertura_proposta}
- Município: {$licitacao->municipio} - {$licitacao->uf}

**ITENS SOLICITADOS (" . count($itens) . " itens - Valor Total Estimado: R$ " . number_format($valor_total, 2, ',', '.') . "):**
{$itens_texto}

**INFORMAÇÕES ADICIONAIS:**
- Score de Compatibilidade do Match: {$match->score_total}%

**INSTRUÇÕES PARA GERAÇÃO:**

Crie uma proposta comercial em HTML formatado contendo obrigatoriamente as seguintes seções:

**1. CABEÇALHO COM LOGO (se disponível)**
   - Incluir logo da empresa usando <img src="{$logo_url}"> se disponível
   - Dados completos da empresa (razão social, CNPJ, endereço, telefone, email)
   - Data da proposta (use a data atual)
   - Número da proposta (sugerir formato: PROP-{$empresa->uf}-" . date('Y') . "-XXX)

**2. IDENTIFICAÇÃO DA LICITAÇÃO**
   - Ao [Nome do Órgão]
   - Referência: [Título da Licitação]
   - Modalidade: [Modalidade]
   - Número PNCP: [Número de Controle]

**3. APRESENTAÇÃO DA EMPRESA**
   - Introdução profissional da empresa
   - Porte empresarial e capacidade técnica
   - Experiência no segmento relacionado ao objeto
   - Principais clientes atendidos (criar exemplos realistas se necessário)
   - Certificações relevantes (ISO 9001, etc - criar se apropriado)

**4. PROPOSTA TÉCNICA DETALHADA**
   - Compreensão do objeto da licitação
   - Metodologia de execução dos serviços/fornecimento
   - Cronograma detalhado de execução
   - Recursos técnicos disponíveis
   - Equipe técnica envolvida (criar perfis realistas)
   - Garantias de qualidade oferecidas
   - Diferenciais competitivos

**5. PROPOSTA COMERCIAL COMPLETA**
   - Criar tabela HTML profissional com TODOS os " . count($itens) . " itens
   - Colunas: Item, Descrição, Unidade, Quantidade, Valor Unitário (R$), Valor Total (R$)
   - Use os valores fornecidos para cada item
   - Incluir subtotais, impostos se aplicável
   - VALOR TOTAL GERAL em destaque: R$ " . number_format($valor_total, 2, ',', '.') . "
   - Condições de pagamento (sugerir: 30 dias após entrega/medição)
   - Prazo de entrega (sugerir: conforme cronograma ou 30-60 dias)
   - Validade da proposta (sugerir: 60 dias)

**6. CONDIÇÕES E TERMOS**
   - Forma de fornecimento/prestação de serviço
   - Garantia dos produtos/serviços
   - Penalidades por atraso (conforme edital)
   - Reajuste de preços (se aplicável)

**7. QUALIFICAÇÃO E CAPACITAÇÃO**
   - Experiências anteriores similares (criar 3-5 exemplos realistas)
   - Atestados de capacidade técnica
   - Declaração de visita técnica (se necessário)

**8. DECLARAÇÕES OBRIGATÓRIAS**
   - Declaração de cumprimento dos requisitos de habilitação
   - Declaração de inexistência de fato impeditivo
   - Declaração de não emprego de menor
   - Declaração de elaboração independente de proposta
   - Declaração de enquadramento como ME/EPP (se aplicável ao porte)

**9. CONCLUSÃO**
   - Reafirmação do interesse
   - Agradecimento pela oportunidade
   - Colocação à disposição para esclarecimentos
   - Dados para contato

**FORMATAÇÃO HTML:**
- Use HTML5 semântico e bem estruturado
- Inclua CSS inline para formatação (papel timbrado simulado)
- Use fonte profissional (Arial, Helvetica)
- Cores sóbrias (azul escuro, cinza, preto)
- Tabelas com bordas, zebra striping
- Hierarquia clara de títulos (h1, h2, h3)
- Margens e espaçamentos adequados
- Quebras de página sugeridas com comentários <!-- QUEBRA DE PÁGINA -->
- Se logo disponível, use <img> no cabeçalho com style="max-width: 200px; height: auto;"

**IMPORTANTE:**
- Seja específico e detalhado
- Use valores reais fornecidos
- Crie conteúdo profissional e convincente
- Mantenha tom formal e respeitoso
- Todos os itens devem estar na tabela de preços
- Valores devem estar corretamente formatados em reais (R$)

Retorne APENAS o HTML completo da proposta, pronto para ser renderizado. NÃO inclua tags ```html ou explicações.
PROMPT;

        return $prompt;
    }

    /**
     * Chama API do Gemini (gemini-3-pro-preview)
     */
    private function _chamar_gemini_api($prompt) {
        // Validar prompt
        if (empty($prompt)) {
            return ['success' => false, 'message' => 'Prompt vazio'];
        }
        
        // Sanitizar prompt - remover caracteres que podem quebrar o JSON
        $prompt = mb_convert_encoding($prompt, 'UTF-8', 'UTF-8');
        $prompt = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $prompt);
        
        // API Key do .env
        $api_key = getenv('GEMINI_API_KEY');
        if (empty($api_key)) {
            return ['success' => false, 'message' => 'GEMINI_API_KEY não configurada no .env'];
        }
        
        // Usando gemini-3-pro-preview
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3-pro-preview:generateContent";
        
        $data = [
            'contents' => [[
                'parts' => [['text' => $prompt]]
            ]]
        ];
        
        $json_data = json_encode($data, JSON_UNESCAPED_UNICODE);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'message' => 'Erro ao codificar JSON: ' . json_last_error_msg()];
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutos timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);
        
        // Log para debug
        log_message('debug', "Gemini API Response Code: $http_code");
        
        if ($curl_error) {
            return ['success' => false, 'message' => "Erro cURL: $curl_error"];
        }
        
        if ($http_code !== 200) {
            $error_detail = json_decode($response, true);
            $error_msg = $error_detail['error']['message'] ?? "HTTP $http_code";
            log_message('error', "Gemini API Error: " . substr($response, 0, 500));
            return ['success' => false, 'message' => "Erro na API Gemini ($http_code): $error_msg"];
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            log_message('error', "Gemini JSON Parse Error: " . substr($response, 0, 500));
            return ['success' => false, 'message' => 'Resposta inválida da API (não é JSON)'];
        }
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return [
                'success' => true,
                'conteudo' => $result['candidates'][0]['content']['parts'][0]['text']
            ];
        }
        
        // Verificar se foi bloqueado por segurança
        if (isset($result['candidates'][0]['finishReason']) && $result['candidates'][0]['finishReason'] === 'SAFETY') {
            return ['success' => false, 'message' => 'Conteúdo bloqueado por filtros de segurança'];
        }
        
        log_message('error', "Gemini Invalid Response: " . json_encode($result));
        return ['success' => false, 'message' => 'Resposta inválida da API: ' . json_encode(array_keys($result))];
    }

    /**
     * Processa resposta da IA e limpa o HTML
     */
    private function _processar_resposta_proposta($resposta) {
        // Remover markdown code blocks se existirem
        $html = preg_replace('/^```html\s*/m', '', $resposta);
        $html = preg_replace('/^```\s*/m', '', $html);
        $html = trim($html);
        
        return $html;
    }

    /**
     * Gera proposta completa com pipeline automatizado
     * 1. Baixa todos os arquivos
     * 2. Extrai ZIPs recursivamente
     * 3. Extrai texto dos PDFs
     * 4. Gera proposta com IA usando contexto completo
     */
    public function gerar_proposta_completa($licitacao_id, $empresa_id, $opcoes = []) {
        $this->load->model('Arquivo_model');
        $this->load->model('Licitacao_model');
        
        $resultado = [
            'success' => false,
            'etapas' => [],
            'proposta_id' => null
        ];
        
        // ETAPA 1: Baixar todos os arquivos
        $resultado['etapas']['download'] = ['status' => 'iniciando', 'mensagem' => 'Baixando arquivos...'];
        
        $download_result = $this->Arquivo_model->download_todos_arquivos($licitacao_id);
        $resultado['etapas']['download'] = [
            'status' => 'concluido',
            'total' => $download_result['total'],
            'baixados' => $download_result['baixados'],
            'zips_encontrados' => count($download_result['zips_encontrados'] ?? [])
        ];
        
        // ETAPA 2: Extrair todos os ZIPs encontrados (recursivamente)
        $resultado['etapas']['extracao'] = ['status' => 'iniciando', 'mensagem' => 'Extraindo arquivos compactados...'];
        
        $total_extraidos = 0;
        $arquivos = $this->Arquivo_model->get_arquivos_licitacao($licitacao_id);
        
        foreach ($arquivos as $arquivo) {
            if ($arquivo->arquivo_baixado && file_exists($arquivo->arquivo_local_path)) {
                if ($this->Arquivo_model->is_archive($arquivo->arquivo_local_path)) {
                    $extract_result = $this->Arquivo_model->extrair_zip($arquivo->id);
                    if ($extract_result['success']) {
                        $total_extraidos += count($extract_result['arquivos_extraidos'] ?? []);
                    }
                }
            }
        }
        
        $resultado['etapas']['extracao'] = [
            'status' => 'concluido',
            'arquivos_extraidos' => $total_extraidos
        ];
        
        // ETAPA 3: Processar todos os PDFs para extrair texto
        $resultado['etapas']['texto'] = ['status' => 'iniciando', 'mensagem' => 'Extraindo texto dos documentos...'];
        
        $pdf_result = $this->Arquivo_model->processar_todos_pdfs($licitacao_id);
        $resultado['etapas']['texto'] = [
            'status' => 'concluido',
            'processados' => $pdf_result['processados'],
            'com_texto' => $pdf_result['com_texto']
        ];
        
        // ETAPA 4: Obter contexto completo dos documentos
        $resultado['etapas']['contexto'] = ['status' => 'iniciando', 'mensagem' => 'Preparando contexto...'];
        
        $contexto = $this->Arquivo_model->get_contexto_documentos($licitacao_id);
        $resultado['etapas']['contexto'] = [
            'status' => 'concluido',
            'documentos' => count($contexto['documentos']),
            'tem_edital' => $contexto['tem_edital'],
            'tem_termo_referencia' => $contexto['tem_termo_referencia']
        ];
        
        // ETAPA 5: Buscar dados necessários
        $licitacao = $this->Licitacao_model->get_by_id($licitacao_id);
        $itens = $this->Licitacao_model->get_itens($licitacao_id);
        $empresa = $this->db->get_where('empresas', ['id' => $empresa_id])->row();
        
        if (!$licitacao || !$empresa) {
            $resultado['message'] = 'Licitação ou empresa não encontrada';
            return $resultado;
        }
        
        // ETAPA 6: Gerar proposta com IA
        $resultado['etapas']['geracao'] = ['status' => 'iniciando', 'mensagem' => 'Gerando proposta com IA...'];
        
        $prompt = $this->_preparar_prompt_proposta_completa($licitacao, $itens, $empresa, $contexto, $opcoes);
        
        $resposta_ia = $this->_chamar_gemini_api($prompt);
        
        if (!$resposta_ia['success']) {
            $resultado['etapas']['geracao'] = ['status' => 'erro', 'mensagem' => $resposta_ia['message']];
            $resultado['message'] = 'Erro na geração com IA: ' . $resposta_ia['message'];
            return $resultado;
        }
        
        $conteudo_html = $this->_processar_resposta_proposta($resposta_ia['conteudo']);
        
        // ETAPA 7: Salvar proposta
        $resultado['etapas']['salvamento'] = ['status' => 'iniciando', 'mensagem' => 'Salvando proposta...'];
        
        $proposta_id = $this->create([
            'empresa_id' => $empresa_id,
            'licitacao_id' => $licitacao_id,
            'titulo' => 'Proposta para ' . $licitacao->titulo,
            'valor_total' => $licitacao->valor_estimado ?? 0,
            'valor_final' => $licitacao->valor_estimado ?? 0,
            'prazo_entrega' => $opcoes['prazo_entrega'] ?? '30 dias',
            'condicoes_pagamento' => $opcoes['condicoes_pagamento'] ?? 'Conforme edital',
            'validade_proposta' => $opcoes['validade_proposta'] ?? '60 dias',
            'status' => 'RASCUNHO',
            'conteudo_html' => $conteudo_html,
            'gerado_por_ia' => true,
            'prompt_ia' => $prompt,
            'data_geracao_ia' => date('Y-m-d H:i:s')
        ]);
        
        if ($proposta_id) {
            $resultado['success'] = true;
            $resultado['proposta_id'] = $proposta_id;
            $resultado['etapas']['geracao'] = ['status' => 'concluido'];
            $resultado['etapas']['salvamento'] = ['status' => 'concluido'];
            $resultado['message'] = 'Proposta gerada com sucesso!';
        } else {
            $resultado['etapas']['salvamento'] = ['status' => 'erro', 'mensagem' => 'Erro ao salvar proposta'];
            $resultado['message'] = 'Erro ao salvar proposta no banco de dados';
        }
        
        return $resultado;
    }
    
    /**
     * Prepara prompt COMPLETO usando TODO o contexto dos documentos
     * Envia todos os textos extraídos para análise profunda
     */
    private function _preparar_prompt_proposta_completa($licitacao, $itens, $empresa, $contexto, $opcoes = []) {
        // Preparar dados dos itens
        $itens_detalhados = [];
        $valor_total = 0;
        
        foreach ($itens as $item) {
            $valor_unit = $item->valor_unitario_estimado ?? 0;
            $valor_total_item = $valor_unit * $item->quantidade;
            $valor_total += $valor_total_item;
            
            $itens_detalhados[] = [
                'numero' => $item->numero_item ?? 0,
                'descricao' => $item->descricao ?? '',
                'quantidade' => $item->quantidade ?? 0,
                'unidade' => $item->unidade_medida ?? 'UN',
                'valor_unitario' => $valor_unit,
                'valor_total' => $valor_total_item
            ];
        }
        
        // Montar texto dos itens
        $itens_texto = '';
        foreach ($itens_detalhados as $item) {
            $itens_texto .= "\n{$item['numero']} | {$item['descricao']} | Qtd: {$item['quantidade']} {$item['unidade']}";
            if ($item['valor_unitario'] > 0) {
                $itens_texto .= " | R$ " . number_format($item['valor_unitario'], 2, ',', '.') . " | Total: R$ " . number_format($item['valor_total'], 2, ',', '.');
            }
        }
        
        // ============================================================
        // TODOS OS DOCUMENTOS COMPLETOS (sem limite de tamanho)
        // ============================================================
        $docs_completos = '';
        $total_caracteres = 0;
        
        foreach ($contexto['documentos'] as $idx => $doc) {
            $doc_num = $idx + 1;
            $texto_doc = $doc['texto'] ?? '';
            $total_caracteres += strlen($texto_doc);
            
            $docs_completos .= "\n\n";
            $docs_completos .= "╔══════════════════════════════════════════════════════════════════╗\n";
            $docs_completos .= "║ DOCUMENTO {$doc_num}: {$doc['titulo']}\n";
            $docs_completos .= "║ Tipo: {$doc['tipo']}\n";
            $docs_completos .= "║ Tamanho: " . strlen($texto_doc) . " caracteres\n";
            $docs_completos .= "╚══════════════════════════════════════════════════════════════════╝\n\n";
            $docs_completos .= $texto_doc;
            $docs_completos .= "\n\n--- FIM DO DOCUMENTO {$doc_num} ---\n";
        }
        
        // URL da logo
        $logo_url = $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : '';
        
        // Dados adicionais da empresa
        $empresa_cnae = $empresa->cnae_principal ?? 'N/A';
        $empresa_segmentos = '';
        if (!empty($empresa->segmentos)) {
            $segs = json_decode($empresa->segmentos, true);
            if (is_array($segs)) {
                $empresa_segmentos = implode(', ', $segs);
            }
        }
        $empresa_certificacoes = '';
        if (!empty($empresa->certificacoes)) {
            $certs = json_decode($empresa->certificacoes, true);
            if (is_array($certs)) {
                $empresa_certificacoes = implode(', ', $certs);
            }
        }
        $empresa_keywords = '';
        if (!empty($empresa->keywords)) {
            $kws = json_decode($empresa->keywords, true);
            if (is_array($kws)) {
                $empresa_keywords = implode(', ', $kws);
            }
        }
        
        $data_atual = date('d/m/Y');
        $data_hora = date('d/m/Y H:i:s');
        $num_itens = count($itens);
        $valor_formatado = number_format($valor_total, 2, ',', '.');
        $num_docs = count($contexto['documentos']);
        
        $prazo_entrega = $opcoes['prazo_entrega'] ?? '30 dias';
        $condicoes_pagamento = $opcoes['condicoes_pagamento'] ?? 'Conforme edital';
        $validade_proposta = $opcoes['validade_proposta'] ?? '60 dias';

        $prompt = <<<PROMPT
# SISTEMA DE GERAÇÃO DE PROPOSTAS COMERCIAIS PARA LICITAÇÕES PÚBLICAS

Você é um especialista sênior em elaboração de propostas comerciais para licitações públicas brasileiras, com vasta experiência em análise de editais e termos de referência.

## SUA MISSÃO

Analise PROFUNDAMENTE todos os documentos fornecidos abaixo ({$num_docs} documentos, {$total_caracteres} caracteres de conteúdo) e gere uma proposta comercial COMPLETA, PROFISSIONAL e VENCEDORA.

## CONTEXTO TEMPORAL
- Data de geração: {$data_hora}
- Esta proposta será usada para participação em licitação pública

================================================================================
                    SEÇÃO 1: DOCUMENTOS COMPLETOS DA LICITAÇÃO
                    (Edital, Termo de Referência, Anexos, etc.)
================================================================================

{$docs_completos}

================================================================================
                    SEÇÃO 2: PALAVRAS-CHAVE EXTRAÍDAS DOS DOCUMENTOS
================================================================================
PROMPT;

        if (!empty($contexto['keywords_unificadas'])) {
            $prompt .= "\n" . implode(', ', $contexto['keywords_unificadas']);
        } else {
            $prompt .= "\nNenhuma palavra-chave extraída automaticamente.";
        }

        $prompt .= <<<PROMPT


================================================================================
                    SEÇÃO 3: DADOS COMPLETOS DA EMPRESA PROPONENTE
================================================================================
DADOS CADASTRAIS:
- Razão Social: {$empresa->nome}
- Nome Fantasia: {$empresa->razao_social}
- CNPJ: {$empresa->cnpj}
- Inscrição Estadual: {$empresa->inscricao_estadual}
- Inscrição Municipal: {$empresa->inscricao_municipal}

ENDEREÇO COMPLETO:
- Logradouro: {$empresa->logradouro}, {$empresa->numero}
- Complemento: {$empresa->complemento}
- Bairro: {$empresa->bairro}
- Cidade/UF: {$empresa->cidade}/{$empresa->uf}
- CEP: {$empresa->cep}

CONTATOS:
- Telefone: {$empresa->telefone}
- E-mail: {$empresa->email}
- Site: {$empresa->site}

CLASSIFICAÇÃO:
- Porte: {$empresa->porte}
- CNAE Principal: {$empresa_cnae}
- Natureza Jurídica: {$empresa->natureza_juridica}
- Capital Social: R$ {$empresa->capital_social}
- Faturamento Anual: R$ {$empresa->faturamento_anual}

ESPECIALIDADES E CAPACIDADES:
- Segmentos de Atuação: {$empresa_segmentos}
- Certificações: {$empresa_certificacoes}
- Palavras-chave do negócio: {$empresa_keywords}

IDENTIDADE VISUAL:
- Logo URL: {$logo_url}

================================================================================
                    SEÇÃO 4: DADOS DA LICITAÇÃO
================================================================================
IDENTIFICAÇÃO:
- Título: {$licitacao->titulo}
- Número de Controle PNCP: {$licitacao->numero_controle_pncp}
- Modalidade: {$licitacao->modalidade}
- Situação: {$licitacao->situacao}

ÓRGÃO CONTRATANTE:
- Nome: {$licitacao->orgao_nome}
- CNPJ: {$licitacao->orgao_cnpj}
- Esfera: {$licitacao->orgao_esfera}
- Poder: {$licitacao->orgao_poder}

OBJETO:
{$licitacao->objeto}

LOCALIZAÇÃO:
- Município: {$licitacao->municipio}
- UF: {$licitacao->uf}

DATAS IMPORTANTES:
- Publicação: {$licitacao->data_publicacao}
- Abertura das Propostas: {$licitacao->data_abertura_proposta}
- Encerramento: {$licitacao->data_encerramento_proposta}

VALORES:
- Valor Estimado Total: R$ {$valor_formatado}
- Tipo de Contratação: {$licitacao->tipo_contratacao}
- Modo de Disputa: {$licitacao->modo_disputa}
- Critério de Julgamento: {$licitacao->criterio_julgamento}

================================================================================
                    SEÇÃO 5: ITENS SOLICITADOS ({$num_itens} itens)
================================================================================
{$itens_texto}

TOTALIZADOR: R$ {$valor_formatado}

================================================================================
                    SEÇÃO 6: PARÂMETROS DA PROPOSTA
================================================================================
- Prazo de Entrega: {$prazo_entrega}
- Condições de Pagamento: {$condicoes_pagamento}
- Validade da Proposta: {$validade_proposta}

================================================================================
                    SEÇÃO 7: INSTRUÇÕES DETALHADAS DE GERAÇÃO
================================================================================

## ANÁLISE PRÉVIA OBRIGATÓRIA

Antes de gerar a proposta, analise os documentos e identifique:

1. **REQUISITOS DO EDITAL**
   - Documentos de habilitação exigidos
   - Critérios de qualificação técnica
   - Declarações obrigatórias
   - Requisitos de visita técnica
   - Garantias exigidas

2. **ESPECIFICAÇÕES TÉCNICAS**
   - Detalhamento dos serviços/produtos
   - Padrões de qualidade exigidos
   - Normas técnicas aplicáveis
   - SLA/Níveis de serviço

3. **CONDIÇÕES COMERCIAIS**
   - Forma de pagamento especificada
   - Prazos de entrega/execução
   - Penalidades previstas
   - Reajustes

4. **CRITÉRIOS DE JULGAMENTO**
   - Menor preço / Técnica e preço
   - Pontuação técnica
   - Desempate

## ESTRUTURA DA PROPOSTA (OBRIGATÓRIA)

### 1. CAPA/CABEÇALHO
- Logo da empresa (usar: <img src="{$logo_url}" style="max-width:200px; height:auto;">)
- Dados completos da empresa
- Número da proposta: PROP-{$empresa->uf}-" . date('Ymd') . "-001
- Data: {$data_atual}
- Referência à licitação

### 2. CARTA DE APRESENTAÇÃO
- Saudação formal ao órgão contratante
- Apresentação da empresa
- Referência ao edital e seus anexos
- Declaração de conhecimento das condições

### 3. QUALIFICAÇÃO DA EMPRESA
- Histórico e experiência no ramo
- Estrutura organizacional
- Capacidade técnica e operacional
- Principais clientes atendidos (criar exemplos realistas baseados no segmento)
- Certificações e acreditações
- Diferenciais competitivos

### 4. PROPOSTA TÉCNICA (Detalhada)
- Compreensão do objeto (demonstrar entendimento profundo baseado nos documentos)
- Metodologia de execução/fornecimento
- Cronograma detalhado
- Equipe técnica (perfis realistas)
- Recursos materiais
- Plano de qualidade
- Gestão de riscos

### 5. PROPOSTA COMERCIAL
- Tabela HTML profissional com TODOS os {$num_itens} itens
- Colunas: Item | Descrição Completa | Unid | Qtd | Valor Unitário (R$) | Valor Total (R$)
- Subtotais por grupo se aplicável
- VALOR TOTAL em destaque: R$ {$valor_formatado}
- Composição de custos se solicitado no edital
- Planilha de formação de preços se aplicável

### 6. CONDIÇÕES COMERCIAIS
- Prazo de entrega: {$prazo_entrega}
- Condições de pagamento: {$condicoes_pagamento}
- Validade da proposta: {$validade_proposta}
- Garantia dos produtos/serviços
- Suporte técnico

### 7. DECLARAÇÕES (baseadas no que o edital exige)
- Declaração de cumprimento dos requisitos de habilitação
- Declaração de inexistência de fato impeditivo
- Declaração de não emprego de menores
- Declaração de elaboração independente de proposta
- Declaração ME/EPP se aplicável (porte da empresa: {$empresa->porte})
- Outras declarações identificadas no edital

### 8. ENCERRAMENTO
- Reafirmação do interesse na contratação
- Compromisso com qualidade e prazos
- Disponibilidade para esclarecimentos
- Assinatura do representante legal (nome e cargo)
- Local e data

## FORMATAÇÃO HTML

```css
/* Use CSS inline com estes estilos */
- Fonte: font-family: Arial, Helvetica, sans-serif; font-size: 11pt;
- Cores principais: #1a365d (azul escuro), #2d3748 (cinza escuro), #4a5568 (cinza médio)
- Cabeçalhos: color: #1a365d; border-bottom: 2px solid #1a365d;
- Tabelas: border-collapse: collapse; width: 100%;
- Células: border: 1px solid #cbd5e0; padding: 8px 12px;
- Zebra striping: background-color: #f7fafc para linhas ímpares
- Total: background-color: #1a365d; color: white; font-weight: bold;
- Margens: margin: 20px 40px;
```

## COMENTÁRIOS PARA IMPRESSÃO
Inclua `<!-- QUEBRA DE PÁGINA -->` entre as seções principais para facilitar impressão.

## QUALIDADE ESPERADA
- Linguagem formal e técnica
- Sem erros de português
- Informações precisas e verificáveis
- Valores corretamente formatados (R$ X.XXX,XX)
- Datas no formato brasileiro (DD/MM/AAAA)
- Referências ao edital quando apropriado

================================================================================
                              RETORNE APENAS O HTML
================================================================================

Gere APENAS o código HTML completo da proposta, sem explicações, sem markdown, sem blocos de código.
O HTML deve começar com <!DOCTYPE html> ou diretamente com as tags de conteúdo.
PROMPT;

        return $prompt;
    }

    /**
     * Gera UUID
     */
    private function _generate_uuid() {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}
