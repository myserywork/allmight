<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        
        // Load necessários
        $this->load->database();
        $this->load->helper(['url', 'form', 'allmight']);
        $this->load->library(['session', 'form_validation']);
        $this->load->model('Empresa_model');
        
        // TODO: Adicionar verificação de autenticação
        // $this->check_auth();
    }

    /**
     * Dashboard principal
     */
    public function dashboard() {
        $data['page_title'] = 'Dashboard';
        $data['page_subtitle'] = 'Visão geral do sistema';
        
        // Estatísticas
        $data['stats'] = $this->get_dashboard_stats();
        
        // Licitações recentes
        $data['recent_licitacoes'] = $this->get_recent_licitacoes(5);
        
        // Matches em destaque
        $data['top_matches'] = $this->get_top_matches(5);
        
        // Gráficos
        $data['chart_data'] = $this->get_chart_data();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/dashboard', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Licitações - Listagem
     */
    public function licitacoes() {
        $this->load->model('Licitacao_model');
        
        $data['page_title'] = 'Licitações';
        $data['page_subtitle'] = 'Gerenciar licitações do sistema';
        
        // Paginação dinâmica
        $per_page = (int)($this->input->get('per_page') ?: 20);
        $per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20;
        $page = max(1, (int)($this->input->get('page') ?: 1));
        $offset = ($page - 1) * $per_page;
        
        // Filtros expandidos
        $filters = [
            'search' => $this->input->get('search'),
            'uf' => $this->input->get('uf'),
            'modalidade' => $this->input->get('modalidade'),
            'situacao' => $this->input->get('situacao'),
            'status' => $this->input->get('status'),
            'valor_min' => $this->input->get('valor_min'),
            'valor_max' => $this->input->get('valor_max'),
            'data_inicio' => $this->input->get('data_inicio'),
            'data_fim' => $this->input->get('data_fim'),
            'order_by' => $this->input->get('order_by') ?: 'data_desc',
            'per_page' => $per_page,
            'tem_itens' => $this->input->get('tem_itens'),
            'orgao' => $this->input->get('orgao')
        ];
        
        $data['licitacoes'] = $this->Licitacao_model->get_all($per_page, $offset, $filters);
        $data['total'] = $this->Licitacao_model->count_all($filters);
        $data['stats'] = $this->Licitacao_model->get_stats();
        $data['modalidades'] = $this->Licitacao_model->get_modalidades();
        $data['situacoes'] = $this->Licitacao_model->get_situacoes();
        
        // Buscar UFs disponíveis
        $data['ufs'] = $this->Licitacao_model->get_ufs();
        
        $data['filters'] = $filters;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($data['total'] / $per_page);
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/licitacoes/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Licitações - Detalhes
     */
    public function licitacao_detalhes($id) {
        $this->load->model('Licitacao_model');
        
        $licitacao = $this->Licitacao_model->get_by_id($id);
        
        if (!$licitacao) {
            $this->session->set_flashdata('error', 'Licitação não encontrada!');
            redirect('admin/licitacoes');
        }
        
        // Busca itens e arquivos relacionados
        $itens = $this->Licitacao_model->get_itens($id);
        $arquivos = $this->Licitacao_model->get_arquivos($id);
        
        // Decodifica JSON se necessário
        if ($licitacao->dados_completos_json) {
            $licitacao->dados_completos = json_decode($licitacao->dados_completos_json);
        }
        
        // Calcula valor total dos itens se não houver valor estimado
        $valor_total_itens = 0;
        if ($itens) {
            foreach ($itens as $item) {
                if (!empty($item->valor_total_estimado)) {
                    $valor_total_itens += $item->valor_total_estimado;
                }
            }
        }
        
        $data['page_title'] = 'Detalhes da Licitação';
        $data['page_subtitle'] = $licitacao->numero_edital ?: $licitacao->titulo;
        $data['licitacao'] = $licitacao;
        $data['itens'] = $itens;
        $data['arquivos'] = $arquivos;
        $data['valor_total_itens'] = $valor_total_itens;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/licitacoes/detalhes', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Empresas - Listagem
     */
    public function empresas() {
        $data['page_title'] = 'Empresas';
        $data['page_subtitle'] = 'Gerenciar empresas cadastradas';
        
        // Paginação e filtros
        $per_page = 20;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;
        
        $filters = [
            'search' => $this->input->get('search'),
            'uf' => $this->input->get('uf'),
            'porte' => $this->input->get('porte'),
            'ativo' => $this->input->get('ativo')
        ];
        
        $data['empresas'] = $this->Empresa_model->get_all($per_page, $offset, $filters);
        $data['total'] = $this->Empresa_model->count_all($filters);
        $data['stats'] = $this->Empresa_model->get_stats();
        $data['filters'] = $filters;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Empresas - Nova empresa
     */
    public function empresa_nova() {
        $data['page_title'] = 'Nova Empresa';
        $data['page_subtitle'] = 'Cadastrar nova empresa';
        $data['empresa'] = null;
        $data['perfil'] = null;
        $data['keywords'] = [];
        $data['keywords_geradas_ia'] = false;
        $data['monitoramento_config'] = null;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Empresas - Editar
     */
    public function empresa_editar($id) {
        $empresa = $this->Empresa_model->get_by_id($id);
        
        if (!$empresa) {
            $this->session->set_flashdata('error', 'Empresa não encontrada!');
            redirect('admin/empresas');
        }
        
        // Decodifica JSON fields
        if ($empresa->cnae_secundarios) {
            $empresa->cnae_secundarios = json_decode($empresa->cnae_secundarios, true);
        }
        if ($empresa->segmentos) {
            $empresa->segmentos = json_decode($empresa->segmentos, true);
        }
        if ($empresa->certificacoes) {
            $empresa->certificacoes = json_decode($empresa->certificacoes, true);
        }
        
        // Busca perfil da empresa
        $perfil = $this->Empresa_model->get_perfil($id);
        
        // Busca keywords e configuração de monitoramento
        $keywords_data = $this->Empresa_model->get_keywords($id);
        $monitoramento_config = $this->Empresa_model->get_monitoramento_config($id);
        
        $data['page_title'] = 'Editar Empresa';
        $data['page_subtitle'] = $empresa->nome;
        $data['empresa'] = $empresa;
        $data['perfil'] = $perfil;
        $data['keywords'] = $keywords_data['keywords'] ?? [];
        $data['keywords_geradas_ia'] = $keywords_data['geradas_ia'] ?? false;
        $data['monitoramento_config'] = $monitoramento_config;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Empresas - Salvar (Create/Update)
     */
    public function empresa_salvar() {
        // Validação
        $this->form_validation->set_rules('nome', 'Nome', 'required');
        $this->form_validation->set_rules('razao_social', 'Razão Social', 'required');
        $this->form_validation->set_rules('cnpj', 'CNPJ', 'required');
        
        if ($this->form_validation->run() == FALSE) {
            $errors = validation_errors();
            $this->session->set_flashdata('error', $errors);
            redirect($this->input->post('id') ? 'admin/empresa_editar/' . $this->input->post('id') : 'admin/empresa_nova');
            return;
        }
        
        // Prepara dados
        $data = [
            'nome' => $this->input->post('nome'),
            'razao_social' => $this->input->post('razao_social'),
            'cnpj' => $this->input->post('cnpj'),
            'inscricao_estadual' => $this->input->post('inscricao_estadual'),
            'inscricao_municipal' => $this->input->post('inscricao_municipal'),
            'email' => $this->input->post('email'),
            'telefone' => $this->input->post('telefone'),
            'site' => $this->input->post('site'),
            'cep' => $this->input->post('cep'),
            'logradouro' => $this->input->post('logradouro'),
            'numero' => $this->input->post('numero'),
            'complemento' => $this->input->post('complemento'),
            'bairro' => $this->input->post('bairro'),
            'cidade' => $this->input->post('cidade'),
            'uf' => $this->input->post('uf'),
            'porte' => $this->input->post('porte'),
            'natureza_juridica' => $this->input->post('natureza_juridica'),
            'cnae_principal' => $this->input->post('cnae_principal'),
            'cnae_secundarios' => $this->input->post('cnae_secundarios') ?: [],
            'faturamento_anual' => $this->input->post('faturamento_anual'),
            'capital_social' => $this->input->post('capital_social'),
            'segmentos' => $this->input->post('segmentos') ?: [],
            'certificacoes' => $this->input->post('certificacoes') ?: [],
            'ativo' => $this->input->post('ativo') ? 1 : 0
        ];
        
        $id = $this->input->post('id');
        
        // Processar upload do logo
        if (isset($_FILES['logo']) && $_FILES['logo']['tmp_name']) {
            $logo_result = $this->_upload_logo($_FILES['logo'], $id);
            if ($logo_result['success']) {
                $data['logo'] = $logo_result['filename'];
                
                // Se está atualizando, deletar logo antigo
                if ($id) {
                    $empresa_atual = $this->Empresa_model->get_by_id($id);
                    if ($empresa_atual && $empresa_atual->logo && $empresa_atual->logo != $logo_result['filename']) {
                        $old_path = FCPATH . 'uploads/logos/' . $empresa_atual->logo;
                        if (file_exists($old_path)) {
                            unlink($old_path);
                        }
                    }
                }
            }
        }
        
        // Verificar se deve remover o logo
        if ($this->input->post('remover_logo') == '1') {
            if ($id) {
                $empresa_atual = $this->Empresa_model->get_by_id($id);
                if ($empresa_atual && $empresa_atual->logo) {
                    $old_path = FCPATH . 'uploads/logos/' . $empresa_atual->logo;
                    if (file_exists($old_path)) {
                        unlink($old_path);
                    }
                }
            }
            $data['logo'] = null;
        }
        
        if ($id) {
            // Update
            $this->Empresa_model->update($id, $data);
            $this->session->set_flashdata('success', 'Empresa atualizada com sucesso!');
        } else {
            // Create
            $id = $this->Empresa_model->create($data);
            $this->session->set_flashdata('success', 'Empresa cadastrada com sucesso!');
        }
        
        // Salva ou atualiza perfil da empresa
        $perfil_data = [
            'curriculo_completo' => $this->input->post('curriculo_completo'),
            'anos_experiencia' => $this->input->post('anos_experiencia'),
            'numero_projetos_realizados' => $this->input->post('numero_projetos_realizados'),
            'valor_total_contratos' => $this->input->post('valor_total_contratos')
        ];
        
        // Monta introdução e explicação básicas se não existir perfil
        $perfil_existente = $this->Empresa_model->get_perfil($id);
        if (!$perfil_existente) {
            $this->Empresa_model->create_perfil_basico($id, $data);
        }
        
        // Atualiza com os dados do formulário
        $this->Empresa_model->save_perfil($id, $perfil_data);
        
        // ===== SALVAR KEYWORDS E CONFIGURAÇÃO DE MONITORAMENTO =====
        $keywords_json = $this->input->post('keywords');
        if ($keywords_json) {
            $keywords = json_decode($keywords_json, true);
            if (is_array($keywords)) {
                $keywords_geradas_ia = $this->input->post('keywords_geradas_ia') ? 1 : 0;
                $this->Empresa_model->save_keywords($id, $keywords, $keywords_geradas_ia);
            }
        }
        
        // Configuração de monitoramento
        $monitoramento_ativo = $this->input->post('monitoramento_ativo') ? 1 : 0;
        $ufs_interesse_json = $this->input->post('ufs_interesse');
        $ufs_interesse = $ufs_interesse_json ? json_decode($ufs_interesse_json, true) : [];
        
        $monitoramento_config = [
            'ufs_interesse' => $ufs_interesse,
            'valor_minimo' => $this->input->post('valor_minimo_monitoramento') ?: null,
            'valor_maximo' => $this->input->post('valor_maximo_monitoramento') ?: null,
            'score_minimo' => $this->input->post('score_minimo_alerta') ?: 50,
            'alerta_sistema' => $this->input->post('alerta_sistema') ? 1 : 0,
            'alerta_email' => $this->input->post('alerta_email') ? 1 : 0
        ];
        
        $this->Empresa_model->save_monitoramento_config($id, $monitoramento_ativo, $monitoramento_config);
        
        redirect('admin/empresas');
    }

    /**
     * Empresas - Deletar
     */
    public function empresa_deletar($id) {
        $empresa = $this->Empresa_model->get_by_id($id);
        
        if (!$empresa) {
            $this->session->set_flashdata('error', 'Empresa não encontrada!');
        } else {
            $this->Empresa_model->delete($id);
            $this->session->set_flashdata('success', 'Empresa deletada com sucesso!');
        }
        
        redirect('admin/empresas');
    }

    /**
     * Empresas - Toggle Status
     */
    public function empresa_toggle_status($id) {
        $this->Empresa_model->toggle_status($id);
        $this->session->set_flashdata('success', 'Status atualizado com sucesso!');
        redirect('admin/empresas');
    }

    // =========================================================================
    // DOCUMENTOS DA EMPRESA
    // =========================================================================

    /**
     * Documentos - Listagem por empresa
     */
    public function empresa_documentos($empresa_id) {
        $this->load->model('Documento_model');
        
        $empresa = $this->Empresa_model->get_by_id($empresa_id);
        
        if (!$empresa) {
            $this->session->set_flashdata('error', 'Empresa não encontrada!');
            redirect('admin/empresas');
            return;
        }
        
        $data['page_title'] = 'Documentos';
        $data['page_subtitle'] = 'Documentos da empresa ' . $empresa->nome;
        
        // Filtros
        $filtros = [
            'categoria' => $this->input->get('categoria'),
            'tipo' => $this->input->get('tipo'),
            'status' => $this->input->get('status'),
            'vencidos' => $this->input->get('vencidos'),
            'a_vencer' => $this->input->get('a_vencer')
        ];
        
        $data['empresa'] = $empresa;
        $data['documentos'] = $this->Documento_model->get_by_empresa($empresa_id, $filtros);
        $data['stats'] = $this->Documento_model->get_stats($empresa_id);
        $data['tipos'] = $this->Documento_model->get_tipos();
        $data['filtros'] = $filtros;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/documentos', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Documentos - Form de Upload
     */
    public function documento_upload($empresa_id) {
        $this->load->model('Documento_model');
        
        $empresa = $this->Empresa_model->get_by_id($empresa_id);
        
        if (!$empresa) {
            $this->session->set_flashdata('error', 'Empresa não encontrada!');
            redirect('admin/empresas');
            return;
        }
        
        $data['page_title'] = 'Novo Documento';
        $data['page_subtitle'] = 'Upload de documento para ' . $empresa->nome;
        $data['empresa'] = $empresa;
        $data['tipos'] = $this->Documento_model->get_tipos();
        $data['documento'] = null;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/documento_form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Documentos - Salvar (criar ou atualizar)
     */
    public function documento_salvar() {
        $this->load->model('Documento_model');
        
        $id = $this->input->post('id');
        $empresa_id = $this->input->post('empresa_id');
        
        // Validação
        $this->form_validation->set_rules('empresa_id', 'Empresa', 'required');
        $this->form_validation->set_rules('tipo', 'Tipo', 'required');
        $this->form_validation->set_rules('nome', 'Nome', 'required|max_length[255]');
        
        if ($this->form_validation->run() == FALSE) {
            $this->session->set_flashdata('error', validation_errors());
            redirect('admin/documento_upload/' . $empresa_id);
            return;
        }
        
        $dados = [
            'empresa_id' => $empresa_id,
            'tipo' => $this->input->post('tipo'),
            'nome' => $this->input->post('nome'),
            'descricao' => $this->input->post('descricao'),
            'numero_documento' => $this->input->post('numero_documento'),
            'data_emissao' => $this->input->post('data_emissao') ?: null,
            'data_validade' => $this->input->post('data_validade') ?: null,
            'orgao_emissor' => $this->input->post('orgao_emissor'),
            'observacoes' => $this->input->post('observacoes'),
            'alerta_vencimento' => $this->input->post('alerta_vencimento') ? 1 : 0
        ];
        
        // Processar arquivo se enviado
        $arquivo = isset($_FILES['arquivo']) && !empty($_FILES['arquivo']['tmp_name']) ? $_FILES['arquivo'] : null;
        
        if ($id) {
            // Atualizar
            $result = $this->Documento_model->update($id, $dados, $arquivo);
        } else {
            // Criar
            if (!$arquivo) {
                $this->session->set_flashdata('error', 'É necessário enviar um arquivo!');
                redirect('admin/documento_upload/' . $empresa_id);
                return;
            }
            $result = $this->Documento_model->create($dados, $arquivo);
        }
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('admin/empresa_documentos/' . $empresa_id);
    }

    /**
     * Documentos - Editar
     */
    public function documento_editar($id) {
        $this->load->model('Documento_model');
        
        $documento = $this->Documento_model->get_by_id($id);
        
        if (!$documento) {
            $this->session->set_flashdata('error', 'Documento não encontrado!');
            redirect('admin/empresas');
            return;
        }
        
        $empresa = $this->Empresa_model->get_by_id($documento->empresa_id);
        
        $data['page_title'] = 'Editar Documento';
        $data['page_subtitle'] = 'Editar documento de ' . $empresa->nome;
        $data['empresa'] = $empresa;
        $data['documento'] = $documento;
        $data['tipos'] = $this->Documento_model->get_tipos();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/empresas/documento_form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Documentos - Excluir
     */
    public function documento_excluir($id) {
        $this->load->model('Documento_model');
        
        $documento = $this->Documento_model->get_by_id($id);
        
        if (!$documento) {
            $this->session->set_flashdata('error', 'Documento não encontrado!');
            redirect('admin/empresas');
            return;
        }
        
        $empresa_id = $documento->empresa_id;
        
        $result = $this->Documento_model->delete($id);
        
        if ($result['success']) {
            $this->session->set_flashdata('success', $result['message']);
        } else {
            $this->session->set_flashdata('error', $result['message']);
        }
        
        redirect('admin/empresa_documentos/' . $empresa_id);
    }

    /**
     * Documentos - Download
     */
    public function documento_download($id) {
        $this->load->model('Documento_model');
        
        $documento = $this->Documento_model->get_by_id($id);
        
        if (!$documento || !$documento->arquivo) {
            show_404();
            return;
        }
        
        $path = $this->Documento_model->get_arquivo_path($documento);
        
        if (!file_exists($path)) {
            show_404();
            return;
        }
        
        // Forçar download
        $this->load->helper('download');
        force_download($documento->arquivo_original ?: basename($documento->arquivo), file_get_contents($path));
    }

    /**
     * Documentos - Visualizar (inline para PDF/imagens)
     */
    public function documento_visualizar($id) {
        $this->load->model('Documento_model');
        
        $documento = $this->Documento_model->get_by_id($id);
        
        if (!$documento || !$documento->arquivo) {
            show_404();
            return;
        }
        
        $path = $this->Documento_model->get_arquivo_path($documento);
        
        if (!file_exists($path)) {
            show_404();
            return;
        }
        
        // Exibir inline
        header('Content-Type: ' . ($documento->mime_type ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . ($documento->arquivo_original ?: basename($documento->arquivo)) . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
    }

    /**
     * Documentos - API para listar (AJAX)
     */
    public function api_documentos($empresa_id) {
        $this->load->model('Documento_model');
        
        $filtros = [
            'categoria' => $this->input->get('categoria'),
            'tipo' => $this->input->get('tipo'),
            'status' => $this->input->get('status')
        ];
        
        $documentos = $this->Documento_model->get_by_empresa($empresa_id, $filtros);
        $stats = $this->Documento_model->get_stats($empresa_id);
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'documentos' => $documentos,
            'stats' => $stats
        ]);
    }

    /**
     * API - Buscar CEP
     */
    public function buscar_cep() {
        $cep = $this->input->post('cep');
        $result = $this->Empresa_model->buscar_cep($cep);
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * API - Buscar CNPJ
     */
    public function buscar_cnpj() {
        $cnpj = $this->input->post('cnpj');
        
        // Remove caracteres especiais do CNPJ
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
        
        if (strlen($cnpj) !== 14) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'CNPJ inválido']);
            return;
        }
        
        // Busca dados na API do CNPJ.ws
        $url = "https://publica.cnpj.ws/cnpj/{$cnpj}";
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json, text/plain, */*',
            'accept-language: pt-BR,pt;q=0.9',
            'origin: https://www.cnpj.ws',
            'referer: https://www.cnpj.ws/',
            'user-agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200 || !$response) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao buscar dados do CNPJ']);
            return;
        }
        
        $data = json_decode($response, true);
        
        if (!$data) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Erro ao processar dados do CNPJ']);
            return;
        }
        
        // Mapeia porte do CNPJ.ws para nosso sistema
        $porte_map = [
            '00' => 'MEI',
            '01' => 'ME',
            '03' => 'EPP',
            '05' => 'MEDIA',
            '06' => 'GRANDE'
        ];
        
        $porte_id = isset($data['porte']['id']) ? $data['porte']['id'] : '05';
        $porte = isset($porte_map[$porte_id]) ? $porte_map[$porte_id] : 'MEDIA';
        
        // Extrai inscrições estaduais
        $inscricoes_estaduais = $data['estabelecimento']['inscricoes_estaduais'] ?? [];
        $inscricao_estadual = '';
        $inscricao_municipal = '';
        
        // Pega a primeira inscrição estadual ativa
        foreach ($inscricoes_estaduais as $insc) {
            if (isset($insc['ativo']) && $insc['ativo'] && !empty($insc['inscricao_estadual'])) {
                $inscricao_estadual = $insc['inscricao_estadual'];
                break;
            }
        }
        
        // Prepara resposta
        $result = [
            'success' => true,
            'data' => [
                'razao_social' => $data['razao_social'] ?? '',
                'nome' => $data['estabelecimento']['nome_fantasia'] ?? $data['razao_social'] ?? '',
                'capital_social' => $data['capital_social'] ?? 0,
                'porte' => $porte,
                'natureza_juridica' => $data['natureza_juridica']['descricao'] ?? '',
                'cnae_principal' => $data['estabelecimento']['atividade_principal']['id'] ?? '',
                'cnae_descricao' => $data['estabelecimento']['atividade_principal']['descricao'] ?? '',
                'inscricao_estadual' => $inscricao_estadual,
                'inscricao_municipal' => $inscricao_municipal,
                'email' => $data['estabelecimento']['email'] ?? '',
                'telefone' => ($data['estabelecimento']['ddd1'] ?? '') . ($data['estabelecimento']['telefone1'] ?? ''),
                'cep' => $data['estabelecimento']['cep'] ?? '',
                'logradouro' => trim(($data['estabelecimento']['tipo_logradouro'] ?? '') . ' ' . ($data['estabelecimento']['logradouro'] ?? '')),
                'numero' => $data['estabelecimento']['numero'] ?? '',
                'complemento' => $data['estabelecimento']['complemento'] ?? '',
                'bairro' => $data['estabelecimento']['bairro'] ?? '',
                'cidade' => $data['estabelecimento']['cidade']['nome'] ?? '',
                'uf' => $data['estabelecimento']['estado']['sigla'] ?? '',
                'data_inicio_atividade' => $data['estabelecimento']['data_inicio_atividade'] ?? '',
                'situacao_cadastral' => $data['estabelecimento']['situacao_cadastral'] ?? '',
                // CNAE secundários
                'cnae_secundarios' => array_map(function($item) {
                    return [
                        'codigo' => $item['id'],
                        'descricao' => $item['descricao']
                    ];
                }, $data['estabelecimento']['atividades_secundarias'] ?? []),
                // Inscrições estaduais
                'inscricoes_estaduais' => $inscricoes_estaduais
            ]
        ];
        
        header('Content-Type: application/json');
        echo json_encode($result);
    }

    /**
     * Matches - Listagem COMPLETA
     */
    public function matches() {
        $this->load->model('Match_model');
        
        $data['page_title'] = 'Matches';
        $data['page_subtitle'] = 'Licitações compatíveis com empresas';
        
        $per_page = $this->input->get('per_page') ?: 20;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;
        
        $filters = [
            'empresa_id' => $this->input->get('empresa_id'),
            'status' => $this->input->get('status'),
            'score_min' => $this->input->get('score_min'),
            'score_max' => $this->input->get('score_max'),
            'uf' => $this->input->get('uf'),
            'search' => $this->input->get('search'),
            'modalidade' => $this->input->get('modalidade'),
            'valor_min' => $this->input->get('valor_min'),
            'valor_max' => $this->input->get('valor_max'),
            'data_inicio' => $this->input->get('data_inicio'),
            'data_fim' => $this->input->get('data_fim'),
            'visualizado' => $this->input->get('visualizado'),
            'order_by' => $this->input->get('order_by') ?: 'score_desc'
        ];
        
        $data['matches'] = $this->Match_model->get_all($per_page, $offset, $filters);
        $data['total'] = $this->Match_model->count_all($filters);
        $data['stats'] = $this->Match_model->get_stats();
        $data['filters'] = $filters;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($data['total'] / $per_page);
        
        // Buscar empresas para filtro
        $data['empresas'] = $this->db->select('id, nome')->where('ativo', true)->order_by('nome')->get('empresas')->result();
        
        // Buscar UFs disponíveis
        $data['ufs'] = $this->db->distinct()->select('uf')->where('uf IS NOT NULL')->order_by('uf')->get('licitacoes')->result();
        
        // Buscar modalidades disponíveis
        $data['modalidades'] = $this->db->distinct()->select('modalidade')->where('modalidade IS NOT NULL')->order_by('modalidade')->get('licitacoes')->result();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/matches/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Match - Detalhes
     */
    public function match_detalhes($id) {
        $this->load->model(['Match_model', 'Licitacao_model']);
        
        $match = $this->Match_model->get_by_id($id);
        
        if (!$match) {
            $this->session->set_flashdata('error', 'Match não encontrado!');
            redirect('admin/matches');
            return;
        }
        
        // Marcar como visualizado
        if (!$match->visualizado) {
            $this->Match_model->marcar_visualizado($id);
        }
        
        // Buscar dados completos da licitação
        $licitacao = $this->Licitacao_model->get_by_id($match->licitacao_id);
        
        // Buscar dados completos da empresa
        $empresa = $this->db->get_where('empresas', ['id' => $match->empresa_id])->row();
        
        // Verificar se tem proposta criada
        $proposta = $this->db->get_where('propostas', ['match_id' => $id])->row();
        
        $data['page_title'] = 'Detalhes do Match';
        $data['page_subtitle'] = $match->empresa_nome;
        $data['match'] = $match;
        $data['licitacao'] = $licitacao;
        $data['empresa'] = $empresa;
        $data['proposta'] = $proposta;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/matches/detalhes', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Gerar matches para uma licitação
     */
    public function gerar_matches($licitacao_id) {
        $this->load->model('Match_model');
        
        $total = $this->Match_model->gerar_matches_licitacao($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'message' => "$total matches gerados com sucesso!",
            'total' => $total
        ]);
    }

    /**
     * Atualizar status do match
     */
    public function match_atualizar_status() {
        $this->load->model('Match_model');
        
        $id = $this->input->post('match_id');
        $status = $this->input->post('status');
        $comentario = $this->input->post('comentario');
        
        $result = $this->Match_model->atualizar_status($id, $status, $comentario);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
    }

    // ========================================================================
    // PROPOSTAS
    // ========================================================================

    /**
     * Propostas - Listagem
     */
    public function propostas() {
        $this->load->model('Proposta_model');
        
        $data['page_title'] = 'Propostas';
        $data['page_subtitle'] = 'Gerenciar propostas comerciais';
        
        $per_page = 20;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;
        
        $filters = [
            'empresa_id' => $this->input->get('empresa_id'),
            'status' => $this->input->get('status'),
            'search' => $this->input->get('search'),
            'gerado_ia' => $this->input->get('gerado_ia')
        ];
        
        $data['propostas'] = $this->Proposta_model->get_all($per_page, $offset, $filters);
        $data['total'] = $this->Proposta_model->count_all($filters);
        $data['stats'] = $this->Proposta_model->get_stats();
        $data['filters'] = $filters;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($data['total'] / $per_page);
        
        // Buscar empresas para filtro
        $data['empresas'] = $this->db->select('id, nome')->where('ativo', true)->order_by('nome')->get('empresas')->result();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/propostas/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Nova Proposta
     */
    public function proposta_nova($match_id) {
        $this->load->model(['Proposta_model', 'Match_model', 'Licitacao_model']);
        
        // Buscar dados do match
        $match = $this->Match_model->get_by_id($match_id);
        
        if (!$match) {
            $this->session->set_flashdata('error', 'Match não encontrado!');
            redirect('admin/matches');
            return;
        }
        
        // Buscar dados da licitação e empresa
        $licitacao = $this->Licitacao_model->get_by_id($match->licitacao_id);
        $empresa = $this->db->get_where('empresas', ['id' => $match->empresa_id])->row();
        
        $data['page_title'] = 'Nova Proposta';
        $data['page_subtitle'] = 'Criar proposta comercial';
        $data['match'] = $match;
        $data['licitacao'] = $licitacao;
        $data['empresa'] = $empresa;
        $data['proposta'] = null; // Nova proposta
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/propostas/form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Editar Proposta
     */
    public function proposta_editar($id) {
        $this->load->model(['Proposta_model', 'Match_model', 'Licitacao_model']);
        
        $proposta = $this->Proposta_model->get_by_id($id);
        
        if (!$proposta) {
            $this->session->set_flashdata('error', 'Proposta não encontrada!');
            redirect('admin/propostas');
            return;
        }
        
        // Buscar match, licitação e empresa
        $match = null;
        if ($proposta->match_id) {
            $match = $this->Match_model->get_by_id($proposta->match_id);
        }
        
        $licitacao = $this->Licitacao_model->get_by_id($proposta->licitacao_id);
        $empresa = $this->db->get_where('empresas', ['id' => $proposta->empresa_id])->row();
        
        $data['page_title'] = 'Editar Proposta';
        $data['page_subtitle'] = $proposta->titulo;
        $data['proposta'] = $proposta;
        $data['match'] = $match;
        $data['licitacao'] = $licitacao;
        $data['empresa'] = $empresa;
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/propostas/form', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Salvar Proposta
     */
    public function proposta_salvar() {
        $this->load->model('Proposta_model');
        
        $proposta_id = $this->input->post('proposta_id');
        
        $data = [
            'match_id' => $this->input->post('match_id'),
            'empresa_id' => $this->input->post('empresa_id'),
            'licitacao_id' => $this->input->post('licitacao_id'),
            'titulo' => $this->input->post('titulo'),
            'numero_proposta' => $this->input->post('numero_proposta'),
            'valor_total' => $this->input->post('valor_total'),
            'desconto_percentual' => $this->input->post('desconto_percentual'),
            'valor_desconto' => $this->input->post('valor_desconto'),
            'valor_final' => $this->input->post('valor_final'),
            'prazo_entrega' => $this->input->post('prazo_entrega'),
            'condicoes_pagamento' => $this->input->post('condicoes_pagamento'),
            'validade_proposta' => $this->input->post('validade_proposta'),
            'conteudo_html' => $this->input->post('conteudo_html'),
            'status' => $this->input->post('status'),
            'observacoes_internas' => $this->input->post('observacoes_internas'),
            'observacoes_cliente' => $this->input->post('observacoes_cliente')
        ];
        
        if ($proposta_id) {
            // Atualizar
            $result = $this->Proposta_model->update($proposta_id, $data);
            if ($result) {
                $this->session->set_flashdata('success', 'Proposta atualizada com sucesso!');
                redirect('admin/proposta/editar/' . $proposta_id);
            } else {
                $this->session->set_flashdata('error', 'Erro ao atualizar proposta!');
                redirect('admin/proposta/editar/' . $proposta_id);
            }
        } else {
            // Criar
            $new_id = $this->Proposta_model->create($data);
            if ($new_id) {
                $this->session->set_flashdata('success', 'Proposta criada com sucesso!');
                redirect('admin/proposta/editar/' . $new_id);
            } else {
                $this->session->set_flashdata('error', 'Erro ao criar proposta!');
                redirect('admin/proposta/nova/' . $data['match_id']);
            }
        }
    }

    /**
     * Gerar proposta com IA
     */
    public function proposta_gerar_ia($match_id) {
        // Desabilitar exibição de erros para não quebrar JSON
        error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
        ini_set('display_errors', '0');
        
        header('Content-Type: application/json');
        
        try {
            $this->load->model('Proposta_model');
            
            $result = $this->Proposta_model->gerar_com_ia($match_id);
            
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao gerar proposta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Preview da proposta
     */
    public function proposta_preview($id) {
        $this->load->model('Proposta_model');
        
        $proposta = $this->Proposta_model->get_by_id($id);
        
        if (!$proposta) {
            show_404();
            return;
        }
        
        $data['proposta'] = $proposta;
        
        $this->load->view('admin/propostas/preview', $data);
    }

    /**
     * Exportar proposta para PDF
     */
    public function proposta_exportar_pdf($id) {
        $this->load->model('Proposta_model');
        
        $proposta = $this->Proposta_model->get_by_id($id);
        
        if (!$proposta) {
            show_404();
            return;
        }
        
        // TODO: Implementar geração de PDF com TCPDF/mPDF
        echo "Exportação PDF em desenvolvimento...";
    }

    /**
     * Exportar proposta para DOCX
     */
    public function proposta_exportar_docx($id) {
        $this->load->model('Proposta_model');
        
        $proposta = $this->Proposta_model->get_by_id($id);
        
        if (!$proposta) {
            show_404();
            return;
        }
        
        // TODO: Implementar geração de DOCX com PHPWord
        echo "Exportação DOCX em desenvolvimento...";
    }

    /**
     * Deletar proposta
     */
    public function proposta_deletar($id) {
        $this->load->model('Proposta_model');
        
        $result = $this->Proposta_model->delete($id);
        
        if ($result) {
            $this->session->set_flashdata('success', 'Proposta deletada com sucesso!');
        } else {
            $this->session->set_flashdata('error', 'Erro ao deletar proposta!');
        }
        
        redirect('admin/propostas');
    }

    /**
     * Atualizar status da proposta
     */
    public function proposta_atualizar_status() {
        $this->load->model('Proposta_model');
        
        $id = $this->input->post('proposta_id');
        $status = $this->input->post('status');
        $observacao = $this->input->post('observacao');
        
        $result = $this->Proposta_model->atualizar_status($id, $status, $observacao);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Status atualizado!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status']);
        }
    }

    // ========================================================================
    // MÉTODOS PRIVADOS - DATA RETRIEVAL
    // ========================================================================

    private function get_dashboard_stats() {
        return [
            'total_licitacoes' => $this->db->count_all('licitacoes'),
            'licitacoes_abertas' => $this->db->where('situacao', 'Aberta')->count_all_results('licitacoes'),
            'total_empresas' => $this->db->count_all('empresas'),
            'total_matches' => $this->db->count_all('matches'),
            'valor_total_estimado' => $this->db->select_sum('valor_estimado')->get('licitacoes')->row()->valor_estimado ?: 0,
            'propostas_em_andamento' => $this->db->where('status', 'em_elaboracao')->count_all_results('propostas')
        ];
    }

    private function get_recent_licitacoes($limit = 5) {
        return $this->db
            ->select('id, numero_edital, titulo, orgao_nome, modalidade, valor_estimado, data_publicacao, situacao')
            ->order_by('data_publicacao', 'DESC')
            ->limit($limit)
            ->get('licitacoes')
            ->result();
    }

    private function get_top_matches($limit = 5) {
        return $this->db
            ->select('m.*, l.titulo, l.numero_edital, e.nome as empresa_nome')
            ->from('matches m')
            ->join('licitacoes l', 'l.id = m.licitacao_id')
            ->join('empresas e', 'e.id = m.empresa_id')
            ->order_by('m.score_total', 'DESC')
            ->limit($limit)
            ->get()
            ->result();
    }

    private function get_chart_data() {
        // Licitações por UF
        $licitacoes_por_uf = $this->db
            ->select('uf, COUNT(*) as total')
            ->group_by('uf')
            ->order_by('total', 'DESC')
            ->limit(10)
            ->get('licitacoes')
            ->result();

        // Licitações por mês (últimos 6 meses)
        $licitacoes_por_mes = $this->db
            ->select('DATE_FORMAT(data_publicacao, "%Y-%m") as mes, COUNT(*) as total')
            ->where('data_publicacao >=', date('Y-m-d', strtotime('-6 months')))
            ->group_by('mes')
            ->order_by('mes', 'ASC')
            ->get('licitacoes')
            ->result();

        return [
            'por_uf' => $licitacoes_por_uf,
            'por_mes' => $licitacoes_por_mes
        ];
    }

    private function get_licitacoes($limit, $offset, $filters = []) {
        $this->db->select('id, numero_edital, titulo, orgao_nome, uf, modalidade, valor_estimado, data_publicacao, situacao');
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('titulo', $filters['search']);
            $this->db->or_like('numero_edital', $filters['search']);
            $this->db->or_like('orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('uf', $filters['uf']);
        }
        
        if (!empty($filters['modalidade'])) {
            $this->db->where('modalidade', $filters['modalidade']);
        }
        
        if (!empty($filters['situacao'])) {
            $this->db->where('situacao', $filters['situacao']);
        }
        
        return $this->db
            ->order_by('data_publicacao', 'DESC')
            ->limit($limit, $offset)
            ->get('licitacoes')
            ->result();
    }

    private function count_licitacoes($filters = []) {
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('titulo', $filters['search']);
            $this->db->or_like('numero_edital', $filters['search']);
            $this->db->or_like('orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('uf', $filters['uf']);
        }
        
        if (!empty($filters['modalidade'])) {
            $this->db->where('modalidade', $filters['modalidade']);
        }
        
        if (!empty($filters['situacao'])) {
            $this->db->where('situacao', $filters['situacao']);
        }
        
        return $this->db->count_all_results('licitacoes');
    }

    private function get_licitacao_by_id($id) {
        return $this->db->where('id', $id)->get('licitacoes')->row();
    }

    private function get_licitacao_itens($licitacao_id) {
        return $this->db->where('licitacao_id', $licitacao_id)->get('licitacao_itens')->result();
    }

    private function get_licitacao_arquivos($licitacao_id) {
        return $this->db->where('licitacao_id', $licitacao_id)->get('licitacao_arquivos')->result();
    }

    private function get_licitacao_historico($licitacao_id) {
        return $this->db
            ->where('licitacao_id', $licitacao_id)
            ->order_by('data_evento', 'DESC')
            ->get('licitacao_historico')
            ->result();
    }

    private function get_empresas() {
        return $this->db
            ->select('id, nome, cnpj, porte, uf, cidade, ativo')
            ->order_by('nome', 'ASC')
            ->get('empresas')
            ->result();
    }

    private function get_matches() {
        return $this->db
            ->select('m.*, l.titulo, l.numero_edital, l.valor_estimado, e.nome as empresa_nome')
            ->from('matches m')
            ->join('licitacoes l', 'l.id = m.licitacao_id')
            ->join('empresas e', 'e.id = m.empresa_id')
            ->order_by('m.score_total', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Análises de IA - Listagem
     */
    public function analises() {
        $this->load->model('Analise_model');
        
        $data['page_title'] = 'Análises de IA';
        $data['page_subtitle'] = 'Processamento inteligente de licitações';
        
        $per_page = 20;
        $page = $this->input->get('page') ?: 1;
        $offset = ($page - 1) * $per_page;
        
        $filters = [
            'processado' => $this->input->get('processado'),
            'uf' => $this->input->get('uf'),
            'complexidade' => $this->input->get('complexidade'),
            'status' => $this->input->get('status'),
            'search' => $this->input->get('search')
        ];
        
        $data['licitacoes'] = $this->Analise_model->get_licitacoes_para_analise($per_page, $offset, $filters);
        $data['total'] = $this->Analise_model->count_licitacoes_para_analise($filters);
        $data['stats'] = $this->Analise_model->get_stats();
        
        // Adicionar contagem de empresas para o chat
        $data['stats']['total_empresas'] = $this->Empresa_model->count_all(['ativo' => 1]);
        
        $data['filters'] = $filters;
        $data['page'] = $page;
        $data['per_page'] = $per_page;
        $data['total_pages'] = ceil($data['total'] / $per_page);
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/analises/index', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * API: Buscar detalhes da análise
     */
    public function api_analise_detalhes($id) {
        $this->load->model('Analise_model');
        
        $analise = $this->Analise_model->get_analise($id);
        
        if ($analise) {
            echo json_encode([
                'success' => true,
                'data' => $analise
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Análise não encontrada'
            ]);
        }
    }

    /**
     * Analisar licitação com IA
     */
    public function analisar_licitacao($id) {
        $this->load->model('Analise_model');
        $this->load->model('Licitacao_model');
        
        $licitacao = $this->Licitacao_model->get_by_id($id);
        
        if (!$licitacao) {
            echo json_encode(['success' => false, 'message' => 'Licitação não encontrada']);
            return;
        }
        
        // Buscar itens
        $itens = $this->Licitacao_model->get_itens($id);
        
        // Preparar dados para IA
        $prompt = $this->_preparar_prompt_analise($licitacao, $itens);
        
        // Chamar Gemini API
        $resultado = $this->_chamar_gemini_api($prompt);
        
        if ($resultado['success']) {
            // Processar resposta da IA
            $dados_analise = $this->_processar_resposta_ia($resultado['data']);
            
            // Salvar análise
            $this->Analise_model->marcar_como_processada($id, $dados_analise);
            
            echo json_encode([
                'success' => true,
                'message' => 'Análise concluída com sucesso!',
                'data' => $dados_analise
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar análise: ' . $resultado['error']
            ]);
        }
    }

    /**
     * Preparar prompt para análise
     */
    private function _preparar_prompt_analise($licitacao, $itens) {
        $itens_texto = '';
        if ($itens) {
            foreach ($itens as $item) {
                $itens_texto .= "- Item {$item->numero_item}: {$item->descricao}\n";
            }
        }
        
        // Data atual para contextualização
        $data_atual = date('d/m/Y');
        $data_hora_atual = date('d/m/Y H:i:s');
        
        $prompt = "**CONTEXTO TEMPORAL:**\n";
        $prompt .= "Data atual: {$data_atual}\n";
        $prompt .= "Data e hora da análise: {$data_hora_atual}\n\n";
        $prompt .= "Analise a seguinte licitação e forneça uma análise estruturada:\n\n";
        $prompt .= "TÍTULO: {$licitacao->titulo}\n";
        $prompt .= "MODALIDADE: {$licitacao->modalidade}\n";
        $prompt .= "ÓRGÃO: {$licitacao->orgao_nome}\n";
        $prompt .= "LOCAL: {$licitacao->municipio} - {$licitacao->uf}\n\n";
        
        if ($licitacao->objeto) {
            $prompt .= "OBJETO:\n{$licitacao->objeto}\n\n";
        }
        
        if ($licitacao->descricao) {
            $prompt .= "DESCRIÇÃO:\n{$licitacao->descricao}\n\n";
        }
        
        if ($itens_texto) {
            $prompt .= "ITENS:\n{$itens_texto}\n\n";
        }
        
        $prompt .= "CRITÉRIOS DE CLASSIFICAÇÃO:\n\n";
        
        $prompt .= "COMPLEXIDADE (escolha UMA opção):\n";
        $prompt .= "- BAIXA: Compras simples, materiais de consumo padronizados, sem especificações técnicas complexas\n";
        $prompt .= "- MEDIA: Serviços comuns, equipamentos com especificações técnicas moderadas, requer conhecimento específico\n";
        $prompt .= "- ALTA: Obras, sistemas complexos, serviços especializados, múltiplas especificações técnicas\n";
        $prompt .= "- MUITO_ALTA: Grandes obras de infraestrutura, sistemas críticos, tecnologia de ponta, alta especialização\n\n";
        
        $prompt .= "PRIORIDADE (1-10, sendo 10 a mais alta):\n";
        $prompt .= "Considere:\n";
        $prompt .= "- Valor estimado (quanto maior, mais prioridade)\n";
        $prompt .= "- Número de itens (mais itens = mais oportunidades)\n";
        $prompt .= "- Abrangência do público (ampla participação ou exclusiva ME/EPP)\n";
        $prompt .= "- Urgência aparente (termos como 'emergencial', 'imediato')\n";
        $prompt .= "- Complexidade técnica (complexidade alta = prioridade alta para empresas especializadas)\n\n";
        
        $prompt .= "RESPONDA APENAS COM JSON VÁLIDO no seguinte formato:\n";
        $prompt .= "{\n";
        $prompt .= '  "complexidade": "BAIXA|MEDIA|ALTA|MUITO_ALTA",'."\n";
        $prompt .= '  "prioridade": 1-10,'."\n";
        $prompt .= '  "palavras_chave": ["palavra1", "palavra2", ...] (máximo 10 palavras-chave relevantes),'."\n";
        $prompt .= '  "categorias": ["categoria1", "categoria2", ...] (áreas de atuação: TI, Construção, Saúde, etc),'."\n";
        $prompt .= '  "resumo": "Resumo executivo em 2-3 frases",'."\n";
        $prompt .= '  "requisitos_principais": ["req1", "req2", ...] (principais requisitos técnicos ou operacionais),'."\n";
        $prompt .= '  "segmentos_alvo": ["segmento1", "segmento2", ...] (tipos de empresas que podem participar),'."\n";
        $prompt .= '  "justificativa_complexidade": "Explicação da complexidade escolhida",'."\n";
        $prompt .= '  "justificativa_prioridade": "Explicação da prioridade escolhida"'."\n";
        $prompt .= "}\n\n";
        $prompt .= "IMPORTANTE: Retorne APENAS o JSON, sem texto adicional antes ou depois.\n";
        
        return $prompt;
    }

    /**
     * Chamar API do Gemini
     */
    private function _chamar_gemini_api($prompt) {
        $api_key = getenv('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $api_key
        ]);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'data' => $result['candidates'][0]['content']['parts'][0]['text']
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Erro na API: ' . $response
        ];
    }

    /**
     * Processar resposta da IA
     */
    private function _processar_resposta_ia($resposta_texto) {
        // Limpar markdown code blocks se houver
        $resposta_texto = preg_replace('/```json\s*/', '', $resposta_texto);
        $resposta_texto = preg_replace('/```\s*/', '', $resposta_texto);
        $resposta_texto = trim($resposta_texto);
        
        // Extrair JSON da resposta
        $json_match = [];
        if (preg_match('/\{[\s\S]*\}/', $resposta_texto, $json_match)) {
            $json_text = $json_match[0];
            $dados = json_decode($json_text, true);
            
            if ($dados) {
                return [
                    'complexidade' => strtoupper($dados['complexidade'] ?? 'MEDIA'),
                    'prioridade' => intval($dados['prioridade'] ?? 5),
                    'palavras_chave' => $dados['palavras_chave'] ?? [],
                    'categorias' => $dados['categorias'] ?? [],
                    'resumo' => $dados['resumo'] ?? '',
                    'requisitos_principais' => $dados['requisitos_principais'] ?? [],
                    'segmentos_alvo' => $dados['segmentos_alvo'] ?? [],
                    'justificativa_complexidade' => $dados['justificativa_complexidade'] ?? '',
                    'justificativa_prioridade' => $dados['justificativa_prioridade'] ?? ''
                ];
            }
        }
        
        // Fallback se não conseguir parsear
        return [
            'complexidade' => 'MEDIA',
            'prioridade' => 5,
            'palavras_chave' => [],
            'categorias' => [],
            'resumo' => 'Erro ao processar análise: ' . substr($resposta_texto, 0, 200),
            'requisitos_principais' => [],
            'segmentos_alvo' => [],
            'justificativa_complexidade' => 'Não foi possível processar',
            'justificativa_prioridade' => 'Não foi possível processar'
        ];
    }

    /**
     * Upload de logo da empresa
     */
    private function _upload_logo($file, $empresa_id = null) {
        $upload_path = FCPATH . 'uploads/logos/';
        
        // Criar pasta se não existir
        if (!is_dir($upload_path)) {
            mkdir($upload_path, 0755, true);
        }
        
        // Validar tipo
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowed_types)) {
            return ['success' => false, 'error' => 'Tipo de arquivo não permitido'];
        }
        
        // Validar tamanho (2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Arquivo muito grande'];
        }
        
        // Gerar nome único
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . uniqid() . '_' . time() . '.' . $ext;
        $destination = $upload_path . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            return ['success' => true, 'filename' => $filename];
        }
        
        return ['success' => false, 'error' => 'Erro ao salvar arquivo'];
    }

    // =========================================================================
    // CHAT IA - Conversar sobre Licitações
    // =========================================================================

    /**
     * API: Chat com IA sobre licitação - VERSÃO AVANÇADA
     */
    public function chat_ia() {
        // Pegar dados JSON da requisição
        $json = file_get_contents('php://input');
        $dados = json_decode($json, true);
        
        if (!$dados || !isset($dados['licitacao_id']) || !isset($dados['pergunta'])) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            return;
        }
        
        $licitacao_id = $dados['licitacao_id'];
        $pergunta = $dados['pergunta'];
        $contexto = $dados['contexto'] ?? [];
        
        // Carregar todos os models necessários
        $this->load->model('Licitacao_model');
        $this->load->model('Documento_model');
        $this->load->model('Match_model');
        
        // Buscar licitação
        $licitacao = $this->Licitacao_model->get_by_id($licitacao_id);
        
        if (!$licitacao) {
            echo json_encode(['success' => false, 'message' => 'Licitação não encontrada']);
            return;
        }
        
        // Buscar itens e arquivos da licitação
        $itens = $this->Licitacao_model->get_itens($licitacao_id);
        $arquivos = $this->Licitacao_model->get_arquivos($licitacao_id);
        
        // Buscar TODAS as empresas ativas com seus dados completos
        $empresas = $this->Empresa_model->get_all(null, 0, ['ativo' => 1]);
        
        // Para cada empresa, buscar documentos
        $empresas_completas = [];
        foreach ($empresas as $empresa) {
            $docs = $this->Documento_model->get_by_empresa($empresa->id);
            $perfil = $this->Empresa_model->get_perfil($empresa->id);
            
            $empresas_completas[] = [
                'empresa' => $empresa,
                'documentos' => $docs,
                'perfil' => $perfil
            ];
        }
        
        // Buscar matches existentes para esta licitação
        $matches = [];
        try {
            $matches = $this->Match_model->get_by_licitacao($licitacao_id);
        } catch (Exception $e) {
            // Se não existir a tabela ou der erro, continua sem matches
        }
        
        // Montar prompt super inteligente
        $prompt = $this->_montar_prompt_chat_avancado($licitacao, $itens, $arquivos, $empresas_completas, $matches, $pergunta, $contexto);
        
        // Chamar API Gemini
        $resultado = $this->_chamar_gemini_api_chat($prompt);
        
        if ($resultado['success']) {
            echo json_encode([
                'success' => true,
                'resposta' => $resultado['resposta']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao processar pergunta'
            ]);
        }
    }

    /**
     * Montar prompt AVANÇADO para chat com contexto completo
     */
    private function _montar_prompt_chat_avancado($licitacao, $itens, $arquivos, $empresas_completas, $matches, $pergunta, $contexto) {
        // Data e hora atual
        $data_atual = date('d/m/Y');
        $hora_atual = date('H:i:s');
        $dia_semana = $this->_get_dia_semana();
        
        $prompt = "# ASSISTENTE ESPECIALIZADO EM LICITAÇÕES PÚBLICAS\n\n";
        $prompt .= "Você é um assistente de IA altamente especializado em licitações públicas brasileiras. ";
        $prompt .= "Seu papel é ser um consultor estratégico que ajuda empresas a participarem de licitações com sucesso.\n\n";
        
        // ==================== CONTEXTO TEMPORAL ====================
        $prompt .= "## 📅 CONTEXTO TEMPORAL\n";
        $prompt .= "- **Data atual:** {$data_atual} ({$dia_semana})\n";
        $prompt .= "- **Hora atual:** {$hora_atual}\n";
        
        // Calcular prazo até abertura
        $data_abertura = $licitacao->data_abertura ?? $licitacao->data_proposta ?? null;
        if ($data_abertura) {
            $diff = $this->_calcular_diferenca_datas($data_abertura);
            $prompt .= "- **Status do prazo:** {$diff}\n";
        }
        $prompt .= "\n";
        
        // ==================== DADOS DA LICITAÇÃO ====================
        $prompt .= "## 📋 LICITAÇÃO EM ANÁLISE\n";
        $prompt .= "### Informações Básicas\n";
        $prompt .= "| Campo | Valor |\n";
        $prompt .= "|-------|-------|\n";
        $prompt .= "| **Título** | " . ($licitacao->titulo ?? 'N/I') . " |\n";
        $prompt .= "| **Número do Edital** | " . ($licitacao->numero_edital ?? 'N/I') . " |\n";
        $prompt .= "| **Modalidade** | " . ($licitacao->modalidade ?? 'N/I') . " |\n";
        $prompt .= "| **Órgão** | " . ($licitacao->orgao_nome ?? 'N/I') . " |\n";
        $prompt .= "| **UASG** | " . ($licitacao->uasg ?? 'N/I') . " |\n";
        $prompt .= "| **Local** | " . ($licitacao->municipio ?? 'N/I') . " - " . ($licitacao->uf ?? '') . " |\n";
        $prompt .= "| **Situação** | " . ($licitacao->situacao ?? 'N/I') . " |\n";
        
        $valor_estimado = $licitacao->valor_total_estimado ?? $licitacao->valor_estimado ?? $licitacao->valor_total ?? 0;
        if ($valor_estimado > 0) {
            $prompt .= "| **Valor Estimado** | R$ " . number_format($valor_estimado, 2, ',', '.') . " |\n";
        }
        
        if ($data_abertura) {
            $prompt .= "| **Data de Abertura** | " . date('d/m/Y H:i', strtotime($data_abertura)) . " |\n";
        }
        
        $prompt .= "\n### Objeto da Licitação\n";
        $prompt .= ($licitacao->objeto ?? $licitacao->descricao ?? 'Não informado') . "\n\n";
        
        // Itens da licitação
        if ($itens && count($itens) > 0) {
            $prompt .= "### Itens da Licitação (" . count($itens) . " itens)\n";
            $prompt .= "| # | Descrição | Qtd | Valor Unit. | Valor Total |\n";
            $prompt .= "|---|-----------|-----|-------------|-------------|\n";
            
            $total_itens = count($itens);
            $itens_mostrar = array_slice($itens, 0, 15);
            $valor_total_itens = 0;
            
            foreach ($itens_mostrar as $item) {
                $num = $item->numero_item ?? $item->numero ?? '?';
                $desc = substr($item->descricao ?? 'N/I', 0, 50) . (strlen($item->descricao ?? '') > 50 ? '...' : '');
                $qtd = $item->quantidade ?? 'N/I';
                $v_unit = isset($item->valor_unitario) && $item->valor_unitario > 0 ? 'R$ ' . number_format($item->valor_unitario, 2, ',', '.') : '-';
                $v_total = isset($item->valor_total_estimado) && $item->valor_total_estimado > 0 ? 'R$ ' . number_format($item->valor_total_estimado, 2, ',', '.') : '-';
                $valor_total_itens += $item->valor_total_estimado ?? 0;
                $prompt .= "| {$num} | {$desc} | {$qtd} | {$v_unit} | {$v_total} |\n";
            }
            
            if ($total_itens > 15) {
                $prompt .= "| ... | *+" . ($total_itens - 15) . " itens adicionais* | | | |\n";
            }
            
            if ($valor_total_itens > 0) {
                $prompt .= "\n**Valor total dos itens listados:** R$ " . number_format($valor_total_itens, 2, ',', '.') . "\n";
            }
            $prompt .= "\n";
        }
        
        // Arquivos/Documentos da licitação
        if ($arquivos && count($arquivos) > 0) {
            $prompt .= "### Documentos Anexos ao Edital\n";
            foreach ($arquivos as $arq) {
                $nome = $arq->nome_arquivo ?? $arq->nome ?? $arq->titulo ?? 'Documento';
                $tipo = $arq->tipo ?? '';
                $prompt .= "- 📄 {$nome}" . ($tipo ? " ({$tipo})" : "") . "\n";
            }
            $prompt .= "\n";
        }
        
        // ==================== EMPRESAS CADASTRADAS ====================
        $prompt .= "## 🏢 EMPRESAS CADASTRADAS NO SISTEMA\n\n";
        
        if (empty($empresas_completas)) {
            $prompt .= "*Nenhuma empresa cadastrada no sistema.*\n\n";
        } else {
            foreach ($empresas_completas as $index => $emp_data) {
                $emp = $emp_data['empresa'];
                $docs = $emp_data['documentos'];
                $perfil = $emp_data['perfil'];
                
                $prompt .= "### " . ($index + 1) . ". " . ($emp->nome ?? 'Empresa sem nome') . "\n";
                $prompt .= "**Dados Cadastrais:**\n";
                $prompt .= "- CNPJ: " . ($emp->cnpj ?? 'N/I') . "\n";
                $prompt .= "- Razão Social: " . ($emp->razao_social ?? 'N/I') . "\n";
                $prompt .= "- Porte: " . ($emp->porte ?? 'N/I') . "\n";
                $prompt .= "- Local: " . ($emp->cidade ?? 'N/I') . "/" . ($emp->uf ?? '') . "\n";
                $prompt .= "- CNAE Principal: " . ($emp->cnae_principal ?? 'N/I') . "\n";
                
                if (!empty($emp->segmentos)) {
                    $segmentos = is_string($emp->segmentos) ? json_decode($emp->segmentos, true) : $emp->segmentos;
                    if ($segmentos) {
                        $prompt .= "- Segmentos de Atuação: " . implode(', ', $segmentos) . "\n";
                    }
                }
                
                // Perfil/Currículo
                if ($perfil) {
                    $prompt .= "\n**Perfil da Empresa:**\n";
                    if (!empty($perfil->curriculo_completo)) {
                        $prompt .= substr($perfil->curriculo_completo, 0, 500) . (strlen($perfil->curriculo_completo) > 500 ? '...' : '') . "\n";
                    }
                    if (!empty($perfil->anos_experiencia)) {
                        $prompt .= "- Anos de experiência: {$perfil->anos_experiencia}\n";
                    }
                    if (!empty($perfil->numero_projetos_realizados)) {
                        $prompt .= "- Projetos realizados: {$perfil->numero_projetos_realizados}\n";
                    }
                }
                
                // Documentos da empresa
                if ($docs && count($docs) > 0) {
                    $prompt .= "\n**Documentos Disponíveis:**\n";
                    
                    // Agrupar por categoria
                    $docs_por_categoria = [];
                    foreach ($docs as $doc) {
                        $categoria = $this->_get_categoria_documento($doc->tipo ?? 'OUTROS');
                        if (!isset($docs_por_categoria[$categoria])) {
                            $docs_por_categoria[$categoria] = [];
                        }
                        $docs_por_categoria[$categoria][] = $doc;
                    }
                    
                    foreach ($docs_por_categoria as $categoria => $docs_cat) {
                        $prompt .= "  *{$categoria}:*\n";
                        foreach ($docs_cat as $doc) {
                            $status_doc = $this->_verificar_status_documento($doc);
                            $prompt .= "    - {$doc->nome}: {$status_doc}\n";
                        }
                    }
                } else {
                    $prompt .= "\n⚠️ *Nenhum documento cadastrado para esta empresa*\n";
                }
                
                $prompt .= "\n---\n\n";
            }
        }
        
        // ==================== MATCHES EXISTENTES ====================
        if (!empty($matches)) {
            $prompt .= "## 🔗 MATCHES JÁ CALCULADOS\n";
            $prompt .= "Empresas já analisadas para compatibilidade com esta licitação:\n\n";
            
            foreach ($matches as $match) {
                $score = $match->score ?? $match->pontuacao ?? 0;
                $status = $match->status ?? 'pendente';
                $empresa_nome = $match->empresa_nome ?? 'Empresa';
                $prompt .= "- **{$empresa_nome}**: Score {$score}% - Status: {$status}\n";
            }
            $prompt .= "\n";
        }
        
        // ==================== HISTÓRICO DA CONVERSA ====================
        if (!empty($contexto) && count($contexto) > 1) {
            $prompt .= "## 💬 HISTÓRICO DA CONVERSA\n";
            foreach (array_slice($contexto, -8) as $msg) {
                $tipo = $msg['tipo'] === 'user' ? '👤 Usuário' : '🤖 Assistente';
                $texto = substr($msg['texto'], 0, 300) . (strlen($msg['texto']) > 300 ? '...' : '');
                $prompt .= "{$tipo}: {$texto}\n\n";
            }
        }
        
        // ==================== PERGUNTA ATUAL ====================
        $prompt .= "## ❓ PERGUNTA DO USUÁRIO\n";
        $prompt .= "**{$pergunta}**\n\n";
        
        // ==================== INSTRUÇÕES PARA A IA ====================
        $prompt .= "## 📝 INSTRUÇÕES CRÍTICAS - LEIA COM ATENÇÃO\n\n";
        
        $prompt .= "### ⚠️ REGRA FUNDAMENTAL:\n";
        $prompt .= "Você DEVE responder APENAS com base nas **empresas REAIS listadas acima** (seção 'EMPRESAS CADASTRADAS NO SISTEMA').\n";
        $prompt .= "**NÃO invente empresas. NÃO dê respostas genéricas. NÃO sugira 'procurar empresas'.**\n";
        $prompt .= "Se não houver empresas cadastradas, diga claramente: 'Não há empresas cadastradas no sistema para analisar.'\n\n";
        
        // Contar empresas
        $total_empresas = count($empresas_completas);
        $prompt .= "### 📊 CONTEXTO ATUAL:\n";
        $prompt .= "- Total de empresas cadastradas: **{$total_empresas}**\n";
        if ($total_empresas > 0) {
            $nomes_empresas = array_map(function($e) { return $e['empresa']->nome ?? 'N/I'; }, $empresas_completas);
            $prompt .= "- Empresas disponíveis: **" . implode(', ', $nomes_empresas) . "**\n";
        }
        $prompt .= "\n";
        
        $prompt .= "### 🎯 COMO RESPONDER:\n\n";
        
        $prompt .= "**Quando perguntarem sobre empresas compatíveis/adequadas:**\n";
        $prompt .= "1. Analise CADA empresa cadastrada acima individualmente\n";
        $prompt .= "2. Para cada empresa, avalie:\n";
        $prompt .= "   - ✅ Porte (ME/EPP/Médio/Grande) vs exigências da licitação\n";
        $prompt .= "   - ✅ Segmentos de atuação vs objeto da licitação\n";
        $prompt .= "   - ✅ CNAE compatível com o fornecimento/serviço\n";
        $prompt .= "   - ✅ Localização vs local de execução\n";
        $prompt .= "   - ✅ Documentos disponíveis e sua validade\n";
        $prompt .= "3. Dê uma CONCLUSÃO CLARA: 'A empresa X é/não é compatível porque...'\n";
        $prompt .= "4. Se nenhuma empresa for compatível, diga claramente\n\n";
        
        $prompt .= "**Quando perguntarem sobre documentos:**\n";
        $prompt .= "1. Liste os documentos típicos para esta modalidade de licitação\n";
        $prompt .= "2. Para CADA empresa cadastrada, verifique:\n";
        $prompt .= "   - Quais documentos ela TEM (listados acima)\n";
        $prompt .= "   - Quais estão VÁLIDOS, VENCIDOS ou A VENCER\n";
        $prompt .= "   - Quais estão FALTANDO\n";
        $prompt .= "3. Seja específico: 'A empresa X possui CND Federal válida até DD/MM/YYYY'\n\n";
        
        $prompt .= "**Quando perguntarem sobre prazos:**\n";
        $prompt .= "1. Use a DATA ATUAL informada acima\n";
        $prompt .= "2. Calcule quantos dias úteis/corridos até a abertura\n";
        $prompt .= "3. Sugira um cronograma de preparação\n\n";
        
        $prompt .= "### 📋 FORMATO DA RESPOSTA:\n";
        $prompt .= "- Use **negrito** para nomes de empresas e pontos críticos\n";
        $prompt .= "- Use listas para organizar análises\n";
        $prompt .= "- Seja DIRETO e ESPECÍFICO - mencione empresas pelo NOME\n";
        $prompt .= "- Inclua uma recomendação final clara\n";
        $prompt .= "- Use emojis para destacar status (✅ ok, ⚠️ atenção, ❌ problema)\n\n";
        
        $prompt .= "### 🚫 O QUE NÃO FAZER:\n";
        $prompt .= "- NÃO dê respostas genéricas sobre 'como escolher empresas'\n";
        $prompt .= "- NÃO sugira 'verificar o CNPJ' - você já TEM os dados\n";
        $prompt .= "- NÃO fale sobre empresas hipotéticas\n";
        $prompt .= "- NÃO peça mais informações se elas já estão acima\n\n";
        
        $prompt .= "Responda de forma DIRETA, ESPECÍFICA e ACIONÁVEL, sempre referenciando as empresas REAIS cadastradas no sistema.\n";
        
        return $prompt;
    }

    /**
     * Função auxiliar: Dia da semana em português
     */
    private function _get_dia_semana() {
        $dias = [
            'Sunday' => 'Domingo',
            'Monday' => 'Segunda-feira',
            'Tuesday' => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday' => 'Quinta-feira',
            'Friday' => 'Sexta-feira',
            'Saturday' => 'Sábado'
        ];
        return $dias[date('l')] ?? date('l');
    }

    /**
     * Função auxiliar: Calcular diferença de datas
     */
    private function _calcular_diferenca_datas($data_alvo) {
        $hoje = new DateTime();
        $alvo = new DateTime($data_alvo);
        $diff = $hoje->diff($alvo);
        
        if ($alvo < $hoje) {
            return "⚠️ ENCERRADA há {$diff->days} dias";
        } elseif ($diff->days == 0) {
            return "🔴 HOJE! Abertura em poucas horas";
        } elseif ($diff->days == 1) {
            return "🟠 AMANHÃ - Urgente!";
        } elseif ($diff->days <= 3) {
            return "🟡 Faltam {$diff->days} dias - Atenção ao prazo!";
        } elseif ($diff->days <= 7) {
            return "🟢 Faltam {$diff->days} dias";
        } else {
            return "✅ Faltam {$diff->days} dias - Tempo confortável";
        }
    }

    /**
     * Função auxiliar: Categoria do documento
     */
    private function _get_categoria_documento($tipo) {
        $categorias = [
            'CND_FEDERAL' => 'Certidões',
            'CND_ESTADUAL' => 'Certidões',
            'CND_MUNICIPAL' => 'Certidões',
            'CND_TRABALHISTA' => 'Certidões',
            'CND_FGTS' => 'Certidões',
            'CERTIDAO_FALENCIA' => 'Certidões',
            'CARTAO_CNPJ' => 'Receita Federal',
            'CONTRATO_SOCIAL' => 'Societário',
            'ALTERACAO_CONTRATUAL' => 'Societário',
            'ATESTADO_CAPACIDADE' => 'Habilitação Técnica',
            'REGISTRO_CONSELHO' => 'Habilitação Técnica',
            'BALANCO_PATRIMONIAL' => 'Financeiro',
            'DRE' => 'Financeiro'
        ];
        return $categorias[$tipo] ?? 'Outros';
    }

    /**
     * Função auxiliar: Verificar status do documento
     */
    private function _verificar_status_documento($doc) {
        if (empty($doc->data_validade)) {
            return "✅ Sem validade definida";
        }
        
        $hoje = new DateTime();
        $validade = new DateTime($doc->data_validade);
        $diff = $hoje->diff($validade);
        
        if ($validade < $hoje) {
            return "❌ VENCIDO há {$diff->days} dias";
        } elseif ($diff->days <= 7) {
            return "🔴 Vence em {$diff->days} dias - URGENTE";
        } elseif ($diff->days <= 30) {
            return "🟡 Vence em {$diff->days} dias - Atenção";
        } elseif ($diff->days <= 90) {
            return "🟢 Válido por mais {$diff->days} dias";
        } else {
            return "✅ Válido até " . $validade->format('d/m/Y');
        }
    }

    /**
     * Montar prompt para chat (versão legada - mantida para compatibilidade)
     */
    private function _montar_prompt_chat($licitacao, $itens, $arquivos, $pergunta, $contexto) {
        // Redireciona para a versão avançada com arrays vazios para empresas e matches
        return $this->_montar_prompt_chat_avancado($licitacao, $itens, $arquivos, [], [], $pergunta, $contexto);
    }

    /**
     * Chamar API do Gemini para Chat
     */
    private function _chamar_gemini_api_chat($prompt) {
        $api_key = getenv('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 4096,
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                return [
                    'success' => true,
                    'resposta' => $result['candidates'][0]['content']['parts'][0]['text']
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Erro na API'
        ];
    }

    /**
     * Processar licitações em lote
     */
    public function processar_lote_ia() {
        $this->load->model('Analise_model');
        $this->load->model('Licitacao_model');
        
        // Buscar licitações pendentes (máximo 10 por vez)
        $licitacoes = $this->Analise_model->get_licitacoes_para_analise(10, 0, ['processado' => 'nao']);
        
        if (empty($licitacoes)) {
            echo json_encode([
                'success' => true,
                'message' => 'Nenhuma licitação pendente para processar'
            ]);
            return;
        }
        
        $processadas = 0;
        $erros = 0;
        
        foreach ($licitacoes as $licitacao) {
            // Buscar itens
            $itens = $this->Licitacao_model->get_itens($licitacao->id);
            
            // Preparar prompt
            $prompt = $this->_preparar_prompt_analise($licitacao, $itens);
            
            // Chamar API
            $resultado = $this->_chamar_gemini_api($prompt);
            
            if ($resultado['success']) {
                $dados_analise = $this->_processar_resposta_ia($resultado['data']);
                $this->Analise_model->marcar_como_processada($licitacao->id, $dados_analise);
                $processadas++;
            } else {
                $erros++;
            }
            
            // Pequena pausa para não sobrecarregar a API
            usleep(500000); // 0.5 segundo
        }
        
        echo json_encode([
            'success' => true,
            'message' => "Processamento concluído: {$processadas} licitações analisadas" . ($erros > 0 ? ", {$erros} erros" : "")
        ]);
    }

    // =========================================================================
    // MONITORAMENTO E KEYWORDS
    // =========================================================================

    /**
     * Gerar keywords com IA baseado nos dados da empresa
     */
    public function gerar_keywords_ia() {
        header('Content-Type: application/json');
        
        // Pegar dados JSON da requisição
        $json = file_get_contents('php://input');
        $dados = json_decode($json, true);
        
        if (!$dados) {
            echo json_encode(['success' => false, 'message' => 'Dados não recebidos']);
            return;
        }
        
        // Verificar se tem empresa_id OU dados diretos do formulário
        $empresa = null;
        $perfil = null;
        
        if (isset($dados['empresa_id']) && !empty($dados['empresa_id'])) {
            // Buscar empresa do banco
            $empresa = $this->Empresa_model->get_by_id($dados['empresa_id']);
            
            if (!$empresa) {
                echo json_encode(['success' => false, 'message' => 'Empresa não encontrada']);
                return;
            }
            
            // Buscar perfil da empresa
            $perfil = $this->Empresa_model->get_perfil($dados['empresa_id']);
        } else {
            // Usar dados enviados diretamente do formulário
            $empresa = (object) [
                'nome' => $dados['nome'] ?? '',
                'razao_social' => $dados['razao_social'] ?? '',
                'cnae_principal' => $dados['cnae_principal'] ?? '',
                'cnae_secundarios' => isset($dados['cnae_secundarios']) ? json_encode($dados['cnae_secundarios']) : '',
                'segmentos' => isset($dados['segmentos']) ? json_encode($dados['segmentos']) : '',
                'certificacoes' => isset($dados['certificacoes']) ? json_encode($dados['certificacoes']) : '',
                'porte' => $dados['porte'] ?? '',
                'uf' => $dados['uf'] ?? '',
                'cidade' => $dados['cidade'] ?? ''
            ];
            
            // Criar perfil fake com currículo se enviado
            if (!empty($dados['curriculo'])) {
                $perfil = (object) ['curriculo_completo' => $dados['curriculo']];
            }
        }
        
        // Validar que tem algum dado útil
        if (empty($empresa->nome) && empty($empresa->razao_social) && empty($empresa->cnae_principal)) {
            echo json_encode(['success' => false, 'message' => 'Preencha pelo menos nome, razão social ou CNAE']);
            return;
        }
        
        // Montar prompt para geração de keywords
        $prompt = $this->_montar_prompt_keywords($empresa, $perfil);
        
        // Chamar API Gemini
        $resultado = $this->_chamar_gemini_api_keywords($prompt);
        
        if ($resultado['success']) {
            echo json_encode([
                'success' => true,
                'keywords' => $resultado['keywords']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $resultado['message'] ?? 'Erro ao gerar keywords'
            ]);
        }
    }

    /**
     * Montar prompt para geração de keywords
     */
    private function _montar_prompt_keywords($empresa, $perfil) {
        $prompt = "Você é um especialista em licitações públicas brasileiras. ";
        $prompt .= "Sua tarefa é gerar uma lista de KEYWORDS (palavras-chave) que serão usadas para encontrar licitações relevantes para esta empresa.\n\n";
        
        $prompt .= "## DADOS DA EMPRESA\n";
        $prompt .= "- **Nome:** " . ($empresa->nome ?? 'N/A') . "\n";
        $prompt .= "- **Razão Social:** " . ($empresa->razao_social ?? 'N/A') . "\n";
        $prompt .= "- **CNAE Principal:** " . ($empresa->cnae_principal ?? 'N/A') . "\n";
        
        // CNAEs secundários
        $cnaes = $empresa->cnae_secundarios ?? '';
        if (is_string($cnaes) && !empty($cnaes)) {
            $cnaes_arr = json_decode($cnaes, true);
            if ($cnaes_arr && is_array($cnaes_arr)) {
                $prompt .= "- **CNAEs Secundários:** " . implode(', ', $cnaes_arr) . "\n";
            }
        }
        
        // Segmentos
        $segmentos = $empresa->segmentos ?? '';
        if (is_string($segmentos) && !empty($segmentos)) {
            $segmentos_arr = json_decode($segmentos, true);
            if ($segmentos_arr && is_array($segmentos_arr)) {
                $prompt .= "- **Segmentos:** " . implode(', ', $segmentos_arr) . "\n";
            }
        }
        
        // Certificações
        $certificacoes = $empresa->certificacoes ?? '';
        if (is_string($certificacoes) && !empty($certificacoes)) {
            $cert_arr = json_decode($certificacoes, true);
            if ($cert_arr && is_array($cert_arr)) {
                $prompt .= "- **Certificações:** " . implode(', ', $cert_arr) . "\n";
            }
        }
        
        $prompt .= "- **Porte:** " . ($empresa->porte ?? 'N/A') . "\n";
        $prompt .= "- **UF:** " . ($empresa->uf ?? 'N/A') . "\n";
        $prompt .= "- **Cidade:** " . ($empresa->cidade ?? 'N/A') . "\n";
        
        // Currículo da empresa
        if ($perfil && !empty($perfil->curriculo_completo)) {
            $prompt .= "\n## CURRÍCULO DA EMPRESA\n";
            $prompt .= substr($perfil->curriculo_completo, 0, 3000) . "\n";
        }
        
        $prompt .= "\n## INSTRUÇÕES\n";
        $prompt .= "Com base nos dados acima, gere uma lista de 15 a 30 keywords (palavras-chave) que serão usadas para buscar licitações relevantes.\n\n";
        
        $prompt .= "### REGRAS PARA AS KEYWORDS:\n";
        $prompt .= "1. Use termos que APARECEM em editais de licitação\n";
        $prompt .= "2. Inclua:\n";
        $prompt .= "   - Nomes de produtos/serviços específicos\n";
        $prompt .= "   - Termos técnicos da área de atuação\n";
        $prompt .= "   - Variações de nomenclatura (ex: 'computador', 'microcomputador', 'desktop')\n";
        $prompt .= "   - Categorias gerais e específicas\n";
        $prompt .= "3. NÃO inclua:\n";
        $prompt .= "   - Palavras muito genéricas como 'serviço', 'produto', 'material'\n";
        $prompt .= "   - Nome da empresa\n";
        $prompt .= "   - Siglas que não sejam conhecidas no mercado\n\n";
        
        $prompt .= "### FORMATO DE RESPOSTA:\n";
        $prompt .= "Retorne APENAS um array JSON com as keywords, sem explicações.\n";
        $prompt .= "Exemplo: [\"keyword1\", \"keyword2\", \"keyword3\"]\n";
        
        return $prompt;
    }

    /**
     * Chamar API Gemini para keywords
     */
    private function _chamar_gemini_api_keywords($prompt) {
        $api_key = getenv('GEMINI_API_KEY');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';
        
        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 2048
            ]
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $api_key
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code !== 200) {
            return ['success' => false, 'message' => 'Erro na API: HTTP ' . $http_code];
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            return ['success' => false, 'message' => 'Resposta inválida da API'];
        }
        
        $texto = $result['candidates'][0]['content']['parts'][0]['text'];
        
        // Tentar extrair array JSON do texto
        preg_match('/\[.*\]/s', $texto, $matches);
        
        if (!empty($matches[0])) {
            $keywords = json_decode($matches[0], true);
            if (is_array($keywords)) {
                // Limpar e validar keywords
                $keywords = array_filter($keywords, function($k) {
                    return is_string($k) && strlen(trim($k)) >= 2 && strlen(trim($k)) <= 100;
                });
                $keywords = array_map('trim', $keywords);
                $keywords = array_unique($keywords);
                $keywords = array_values($keywords);
                
                return ['success' => true, 'keywords' => $keywords];
            }
        }
        
        return ['success' => false, 'message' => 'Não foi possível processar as keywords'];
    }

    /**
     * Dashboard de Monitoramento
     */
    public function monitoramento() {
        $this->load->model('Alerta_model');
        
        $data['page_title'] = 'Monitoramento';
        $data['page_subtitle'] = 'Alertas de licitações por keywords';
        
        // Filtros
        $filtros = [
            'empresa_id' => $this->input->get('empresa_id'),
            'status' => $this->input->get('status'),
            'score_minimo' => $this->input->get('score_minimo')
        ];
        
        // Buscar alertas
        $data['alertas'] = $this->Alerta_model->get_all(100, 0, $filtros);
        
        // Estatísticas
        $data['stats'] = $this->Alerta_model->get_stats();
        
        // Alertas urgentes
        $data['alertas_urgentes'] = $this->Alerta_model->get_urgentes(5);
        
        // Empresas para filtro
        $data['empresas'] = $this->Empresa_model->get_all();
        
        $this->load->view('templates/header', $data);
        $this->load->view('templates/sidebar', $data);
        $this->load->view('admin/monitoramento', $data);
        $this->load->view('templates/footer', $data);
    }

    /**
     * Executar matching de licitações com keywords
     */
    public function executar_matching() {
        header('Content-Type: application/json');
        
        $this->load->model('Alerta_model');
        $this->load->model('Licitacao_model');
        
        try {
            // Executar matching para todas as empresas com monitoramento ativo
            $resultado = $this->Alerta_model->executar_matching_global();
            
            echo json_encode([
                'success' => true,
                'novos_alertas' => $resultado['novos_alertas'],
                'total_verificadas' => $resultado['total_verificadas'],
                'message' => "Matching executado: {$resultado['novos_alertas']} novos alertas gerados"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao executar matching: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Limpar todos os alertas
     */
    public function limpar_alertas() {
        header('Content-Type: application/json');
        
        $this->load->model('Alerta_model');
        
        try {
            $deletados = $this->Alerta_model->limpar_todos();
            
            echo json_encode([
                'success' => true,
                'deletados' => $deletados,
                'message' => "{$deletados} alertas removidos"
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro ao limpar alertas: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Marcar alerta como visualizado
     */
    public function alerta_visualizar($id) {
        header('Content-Type: application/json');
        
        $this->load->model('Alerta_model');
        
        $resultado = $this->Alerta_model->marcar_visualizado($id);
        
        echo json_encode([
            'success' => $resultado ? true : false
        ]);
    }

    /**
     * Marcar alerta como descartado
     */
    public function alerta_descartar($id) {
        header('Content-Type: application/json');
        
        $this->load->model('Alerta_model');
        
        $resultado = $this->Alerta_model->descartar($id);
        
        echo json_encode([
            'success' => $resultado ? true : false
        ]);
    }

    /**
     * Obter detalhes de um alerta
     */
    public function alerta_detalhes($id) {
        header('Content-Type: application/json');
        
        $this->load->model('Alerta_model');
        
        $alerta = $this->Alerta_model->get_by_id($id);
        
        if (!$alerta) {
            echo json_encode(['success' => false, 'message' => 'Alerta não encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'alerta' => $alerta
        ]);
    }

    /**
     * Obter itens de uma licitação (API JSON)
     */
    public function get_itens_licitacao($licitacao_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Licitacao_model');
        
        $itens = $this->Licitacao_model->get_itens($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'itens' => $itens
        ]);
    }

    /**
     * Obter arquivos de uma licitação (API JSON)
     */
    public function get_arquivos_licitacao($licitacao_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Licitacao_model');
        
        $arquivos = $this->Licitacao_model->get_arquivos($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'arquivos' => $arquivos
        ]);
    }
    
    // ========== GERENCIADOR DE ARQUIVOS AVANÇADO ==========
    
    /**
     * API: Estatísticas dos arquivos de uma licitação
     */
    public function arquivos_stats($licitacao_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $stats = $this->Arquivo_model->get_stats_licitacao($licitacao_id);
        $arquivos = $this->Arquivo_model->get_arquivos_licitacao($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'arquivos' => $arquivos
        ]);
    }
    
    /**
     * API: Baixar um arquivo específico
     */
    public function arquivo_download($arquivo_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $result = $this->Arquivo_model->download_arquivo($arquivo_id);
        
        echo json_encode($result);
    }
    
    /**
     * API: Baixar todos os arquivos de uma licitação
     */
    public function arquivos_download_todos($licitacao_id) {
        header('Content-Type: application/json');
        set_time_limit(300); // 5 minutos para downloads
        
        $this->load->model('Arquivo_model');
        
        $result = $this->Arquivo_model->download_todos_arquivos($licitacao_id);
        
        // Se encontrou ZIPs, extrair automaticamente
        if (!empty($result['zips_encontrados'])) {
            $result['extracao'] = [];
            foreach ($result['zips_encontrados'] as $zip_info) {
                $extracao = $this->Arquivo_model->extrair_zip($zip_info['id']);
                $result['extracao'][] = $extracao;
            }
        }
        
        echo json_encode($result);
    }
    
    /**
     * API: Extrair arquivo ZIP
     */
    public function arquivo_extrair($arquivo_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $result = $this->Arquivo_model->extrair_zip($arquivo_id);
        
        echo json_encode($result);
    }
    
    /**
     * API: Extrair texto de um PDF
     */
    public function arquivo_extrair_texto($arquivo_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $result = $this->Arquivo_model->extrair_texto_pdf($arquivo_id);
        
        echo json_encode($result);
    }
    
    /**
     * API: Processar todos os PDFs de uma licitação
     */
    public function arquivos_processar_pdfs($licitacao_id) {
        header('Content-Type: application/json');
        set_time_limit(600); // 10 minutos para processamento
        
        $this->load->model('Arquivo_model');
        
        // Primeiro garantir que todos estão baixados
        $download_result = $this->Arquivo_model->download_todos_arquivos($licitacao_id);
        
        // Depois processar PDFs
        $result = $this->Arquivo_model->processar_todos_pdfs($licitacao_id);
        $result['downloads'] = $download_result;
        
        echo json_encode($result);
    }
    
    /**
     * API: Obter contexto completo dos documentos para proposta
     */
    public function arquivos_contexto($licitacao_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $contexto = $this->Arquivo_model->get_contexto_documentos($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'contexto' => $contexto
        ]);
    }
    
    /**
     * API: Buscar texto nos arquivos
     */
    public function arquivos_buscar($licitacao_id) {
        header('Content-Type: application/json');
        
        $termo = $this->input->get_post('termo');
        
        if (empty($termo)) {
            echo json_encode(['success' => false, 'message' => 'Termo de busca não informado']);
            return;
        }
        
        $this->load->model('Arquivo_model');
        
        $arquivos = $this->Arquivo_model->buscar_por_texto($licitacao_id, $termo);
        
        echo json_encode([
            'success' => true,
            'termo' => $termo,
            'resultados' => count($arquivos),
            'arquivos' => $arquivos
        ]);
    }
    
    /**
     * API: Visualizar texto extraído de um arquivo
     */
    public function arquivo_texto($arquivo_id) {
        header('Content-Type: application/json');
        
        $this->load->model('Arquivo_model');
        
        $arquivo = $this->Arquivo_model->get_by_id($arquivo_id);
        
        if (!$arquivo) {
            echo json_encode(['success' => false, 'message' => 'Arquivo não encontrado']);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'arquivo' => [
                'id' => $arquivo->id,
                'titulo' => $arquivo->titulo,
                'tipo' => $arquivo->tipo_documento,
                'texto_extraido' => $arquivo->texto_extraido,
                'palavras_chave' => json_decode($arquivo->palavras_chave, true),
                'conteudo_analisado' => (bool)$arquivo->conteudo_analisado
            ]
        ]);
    }
    
    /**
     * API: Download e processamento completo (pipeline)
     */
    public function arquivos_processar_completo($licitacao_id) {
        header('Content-Type: application/json');
        set_time_limit(900); // 15 minutos
        
        $this->load->model('Arquivo_model');
        
        $pipeline = [
            'etapa_1_download' => null,
            'etapa_2_extracao_zips' => [],
            'etapa_3_processamento_pdfs' => null,
            'etapa_4_contexto' => null
        ];
        
        // Etapa 1: Download de todos os arquivos
        $pipeline['etapa_1_download'] = $this->Arquivo_model->download_todos_arquivos($licitacao_id);
        
        // Etapa 2: Extrair ZIPs encontrados
        if (!empty($pipeline['etapa_1_download']['zips_encontrados'])) {
            foreach ($pipeline['etapa_1_download']['zips_encontrados'] as $zip_info) {
                $extracao = $this->Arquivo_model->extrair_zip($zip_info['id']);
                $pipeline['etapa_2_extracao_zips'][] = $extracao;
            }
        }
        
        // Etapa 3: Processar todos os PDFs
        $pipeline['etapa_3_processamento_pdfs'] = $this->Arquivo_model->processar_todos_pdfs($licitacao_id);
        
        // Etapa 4: Gerar contexto unificado
        $pipeline['etapa_4_contexto'] = $this->Arquivo_model->get_contexto_documentos($licitacao_id);
        
        // Estatísticas finais
        $stats = $this->Arquivo_model->get_stats_licitacao($licitacao_id);
        
        echo json_encode([
            'success' => true,
            'message' => 'Processamento completo realizado',
            'pipeline' => $pipeline,
            'stats_finais' => $stats
        ]);
    }
    
    /**
     * API: Gerar proposta completa com pipeline automatizado
     * POST /admin/gerar_proposta_completa/{licitacao_id}
     */
    public function gerar_proposta_completa($licitacao_id) {
        // Desabilitar erros HTML
        ini_set('display_errors', 0);
        error_reporting(0);
        
        header('Content-Type: application/json');
        set_time_limit(1200); // 20 minutos para todo o pipeline
        
        try {
            // Obter empresa_id do POST ou usar a primeira empresa cadastrada
            $empresa_id = $this->input->post('empresa_id');
            
            if (empty($empresa_id)) {
                // Buscar primeira empresa
                $empresa = $this->db->limit(1)->get('empresas')->row();
                if ($empresa) {
                    $empresa_id = $empresa->id;
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Nenhuma empresa encontrada. Cadastre uma empresa primeiro.'
                    ]);
                    return;
                }
            }
            
            // Opções de geração
            $opcoes = [
                'prazo_entrega' => $this->input->post('prazo_entrega') ?: '30 dias',
                'condicoes_pagamento' => $this->input->post('condicoes_pagamento') ?: 'Conforme edital',
                'validade_proposta' => $this->input->post('validade_proposta') ?: '60 dias'
            ];
            
            $this->load->model('Proposta_model');
            
            $resultado = $this->Proposta_model->gerar_proposta_completa($licitacao_id, $empresa_id, $opcoes);
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * API: Listar empresas disponíveis para seleção
     */
    public function empresas_lista_simples() {
        header('Content-Type: application/json');
        
        $empresas = $this->db
            ->select('id, nome, cnpj, porte')
            ->order_by('nome', 'ASC')
            ->get('empresas')
            ->result();
        
        echo json_encode([
            'success' => true,
            'empresas' => $empresas
        ]);
    }
}
