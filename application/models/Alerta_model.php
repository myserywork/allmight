<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Alerta_model
 * 
 * Model para gerenciar alertas de licitações baseados em keywords
 * Sistema de monitoramento inteligente AllMight
 */
class Alerta_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // =====================================================
    // CRUD DE ALERTAS
    // =====================================================

    /**
     * Limpar todos os alertas
     */
    public function limpar_todos() {
        $this->db->from('alertas_licitacao');
        $count = $this->db->count_all_results();
        
        $this->db->truncate('alertas_licitacao');
        
        return $count;
    }

    /**
     * Buscar alertas com filtros
    */
    public function get_all($limit = 20, $offset = 0, $filters = []) {
        $this->db->select('a.*, 
                          e.nome as empresa_nome, 
                          e.cnpj as empresa_cnpj,
                          e.porte as empresa_porte,
                          l.titulo as licitacao_titulo,
                          l.objeto,
                          l.descricao,
                          l.orgao_nome as orgao,
                          l.numero_edital,
                          l.uf,
                          l.municipio,
                          l.modalidade,
                          l.situacao,
                          l.data_abertura_proposta,
                          l.link_edital,
                          l.link_portal,
                          COALESCE(l.valor_estimado, (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id)) as valor_estimado,
                          DATEDIFF(l.data_abertura_proposta, NOW()) as dias_para_abertura', FALSE);
        $this->db->from('alertas_licitacao a');
        $this->db->join('empresas e', 'e.id = a.empresa_id');
        $this->db->join('licitacoes l', 'l.id = a.licitacao_id');
        
        // Filtros
        if (!empty($filters['empresa_id'])) {
            $this->db->where('a.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->db->where_in('a.status', $filters['status']);
            } else {
                $this->db->where('a.status', $filters['status']);
            }
        }
        
        if (!empty($filters['prioridade'])) {
            $this->db->where('a.prioridade', $filters['prioridade']);
        }
        
        if (!empty($filters['recomendacao'])) {
            $this->db->where('a.recomendacao', $filters['recomendacao']);
        }
        
        if (isset($filters['visualizado']) && $filters['visualizado'] !== '') {
            $this->db->where('a.visualizado', $filters['visualizado']);
        }
        
        if (!empty($filters['score_min'])) {
            $this->db->where('a.score_total >=', $filters['score_min']);
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('l.uf', $filters['uf']);
        }
        
        if (!empty($filters['modalidade'])) {
            $this->db->where('l.modalidade', $filters['modalidade']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.titulo', $filters['search']);
            $this->db->or_like('l.numero_edital', $filters['search']);
            $this->db->or_like('l.orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        // Só alertas ativos (empresas ativas)
        $this->db->where('e.ativo', 1);
        
        // Ordenação
        $order_by = $filters['order_by'] ?? 'score_desc';
        switch ($order_by) {
            case 'score_desc':
                $this->db->order_by('a.score_total', 'DESC');
                break;
            case 'score_asc':
                $this->db->order_by('a.score_total', 'ASC');
                break;
            case 'data_desc':
                $this->db->order_by('a.data_criacao', 'DESC');
                break;
            case 'prioridade':
                $this->db->order_by("FIELD(a.prioridade, 'URGENTE', 'ALTA', 'MEDIA', 'BAIXA')");
                $this->db->order_by('a.score_total', 'DESC');
                break;
            case 'abertura':
                $this->db->order_by('l.data_abertura_proposta', 'ASC');
                break;
            default:
                $this->db->order_by('a.prioridade');
                $this->db->order_by('a.score_total', 'DESC');
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Contar alertas
     */
    public function count_all($filters = []) {
        $this->db->from('alertas_licitacao a');
        $this->db->join('empresas e', 'e.id = a.empresa_id');
        $this->db->join('licitacoes l', 'l.id = a.licitacao_id');
        
        if (!empty($filters['empresa_id'])) {
            $this->db->where('a.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            if (is_array($filters['status'])) {
                $this->db->where_in('a.status', $filters['status']);
            } else {
                $this->db->where('a.status', $filters['status']);
            }
        }
        
        if (!empty($filters['prioridade'])) {
            $this->db->where('a.prioridade', $filters['prioridade']);
        }
        
        if (isset($filters['visualizado']) && $filters['visualizado'] !== '') {
            $this->db->where('a.visualizado', $filters['visualizado']);
        }
        
        if (!empty($filters['score_min'])) {
            $this->db->where('a.score_total >=', $filters['score_min']);
        }
        
        $this->db->where('e.ativo', 1);
        
        return $this->db->count_all_results();
    }

    /**
     * Buscar alerta por ID
     */
    public function get_by_id($id) {
        return $this->db->select('a.*, 
                          e.nome as empresa_nome, 
                          e.cnpj as empresa_cnpj,
                          e.porte as empresa_porte,
                          e.keywords as empresa_keywords,
                          l.titulo as licitacao_titulo,
                          l.numero_edital,
                          l.orgao_nome,
                          l.uf,
                          l.municipio,
                          l.modalidade,
                          l.situacao,
                          l.objeto,
                          l.data_abertura_proposta,
                          COALESCE(l.valor_estimado, (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id)) as valor_estimado', FALSE)
            ->from('alertas_licitacao a')
            ->join('empresas e', 'e.id = a.empresa_id')
            ->join('licitacoes l', 'l.id = a.licitacao_id')
            ->where('a.id', $id)
            ->get()
            ->row();
    }

    /**
     * Verificar se alerta já existe para empresa+licitação
     */
    public function exists($empresa_id, $licitacao_id) {
        return $this->db->where('empresa_id', $empresa_id)
            ->where('licitacao_id', $licitacao_id)
            ->count_all_results('alertas_licitacao') > 0;
    }

    /**
     * Criar novo alerta
     */
    public function create($data) {
        $insert_data = [
            'empresa_id' => $data['empresa_id'],
            'licitacao_id' => $data['licitacao_id'],
            'score_total' => $data['score_total'] ?? 0,
            'score_keywords' => $data['score_keywords'] ?? 0,
            'score_segmento' => $data['score_segmento'] ?? 0,
            'score_localizacao' => $data['score_localizacao'] ?? 0,
            'score_porte' => $data['score_porte'] ?? 0,
            'score_valor' => $data['score_valor'] ?? 0,
            'keywords_match' => json_encode($data['keywords_match'] ?? [], JSON_UNESCAPED_UNICODE),
            'itens_match' => json_encode($data['itens_match'] ?? [], JSON_UNESCAPED_UNICODE),
            'analise_ia' => $data['analise_ia'] ?? null,
            'recomendacao' => $data['recomendacao'] ?? 'MEDIA',
            'prioridade' => $this->calcular_prioridade($data),
            'status' => 'NOVO'
        ];
        
        $this->db->insert('alertas_licitacao', $insert_data);
        return $this->db->insert_id();
    }

    /**
     * Atualizar alerta existente
     */
    public function update($id, $data) {
        $update_data = [];
        
        $campos_permitidos = ['score_total', 'score_keywords', 'score_segmento', 'score_localizacao', 
                              'score_porte', 'score_valor', 'keywords_match', 'itens_match', 
                              'analise_ia', 'recomendacao', 'status', 'prioridade', 'notas', 'visualizado'];
        
        foreach ($campos_permitidos as $campo) {
            if (isset($data[$campo])) {
                if (in_array($campo, ['keywords_match', 'itens_match']) && is_array($data[$campo])) {
                    $update_data[$campo] = json_encode($data[$campo], JSON_UNESCAPED_UNICODE);
                } else {
                    $update_data[$campo] = $data[$campo];
                }
            }
        }
        
        if (!empty($update_data)) {
            return $this->db->where('id', $id)->update('alertas_licitacao', $update_data);
        }
        
        return false;
    }

    /**
     * Marcar como visualizado
     */
    public function marcar_visualizado($id) {
        return $this->db->where('id', $id)
            ->update('alertas_licitacao', [
                'visualizado' => 1,
                'data_visualizacao' => date('Y-m-d H:i:s')
            ]);
    }

    /**
     * Atualizar status do alerta
     */
    public function update_status($id, $status) {
        $status_validos = ['NOVO', 'VISUALIZADO', 'INTERESSADO', 'DESCARTADO', 'EM_ANALISE', 'PROPOSTA_ENVIADA'];
        
        if (!in_array($status, $status_validos)) {
            return false;
        }
        
        return $this->db->where('id', $id)
            ->update('alertas_licitacao', ['status' => $status]);
    }

    /**
     * Calcular prioridade baseado nos dados
     */
    private function calcular_prioridade($data) {
        $score = $data['score_total'] ?? 0;
        $dias_abertura = $data['dias_para_abertura'] ?? 30;
        
        // Urgente: score alto + prazo curto
        if ($score >= 80 && $dias_abertura <= 3) {
            return 'URGENTE';
        }
        
        // Alta: score alto ou prazo muito curto
        if ($score >= 70 || $dias_abertura <= 2) {
            return 'ALTA';
        }
        
        // Média: score médio
        if ($score >= 50) {
            return 'MEDIA';
        }
        
        return 'BAIXA';
    }

    // =====================================================
    // ESTATÍSTICAS E DASHBOARD
    // =====================================================

    /**
     * Contar alertas novos (não visualizados)
     */
    public function count_novos($empresa_id = null) {
        $this->db->from('alertas_licitacao a')
            ->join('empresas e', 'e.id = a.empresa_id')
            ->where('e.ativo', 1)
            ->where('a.status', 'NOVO');
        
        if ($empresa_id) {
            $this->db->where('a.empresa_id', $empresa_id);
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Estatísticas gerais de alertas
     */
    public function get_stats($empresa_id = null) {
        $where_empresa = $empresa_id ? "AND a.empresa_id = '{$empresa_id}'" : "";
        
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN a.status = 'NOVO' THEN 1 ELSE 0 END) as novos,
                    SUM(CASE WHEN a.visualizado = 1 THEN 1 ELSE 0 END) as visualizados,
                    SUM(CASE WHEN a.visualizado = 0 THEN 1 ELSE 0 END) as nao_visualizados,
                    SUM(CASE WHEN a.status = 'INTERESSADO' THEN 1 ELSE 0 END) as interessados,
                    SUM(CASE WHEN a.status = 'EM_ANALISE' THEN 1 ELSE 0 END) as em_analise,
                    SUM(CASE WHEN a.status = 'PROPOSTA_ENVIADA' THEN 1 ELSE 0 END) as propostas,
                    SUM(CASE WHEN a.status = 'DESCARTADO' THEN 1 ELSE 0 END) as descartados,
                    SUM(CASE WHEN a.prioridade = 'URGENTE' THEN 1 ELSE 0 END) as urgentes,
                    SUM(CASE WHEN a.prioridade = 'ALTA' THEN 1 ELSE 0 END) as alta_prioridade,
                    AVG(a.score_total) as score_medio,
                    MAX(a.score_total) as score_maximo
                FROM alertas_licitacao a
                JOIN empresas e ON e.id = a.empresa_id
                WHERE e.ativo = 1 {$where_empresa}";
        
        $result = $this->db->query($sql)->row();
        
        // Alertas por recomendação
        $por_recomendacao = $this->db->select('recomendacao, COUNT(*) as total')
            ->from('alertas_licitacao a')
            ->join('empresas e', 'e.id = a.empresa_id')
            ->where('e.ativo', 1);
        
        if ($empresa_id) {
            $por_recomendacao = $this->db->where('a.empresa_id', $empresa_id);
        }
        
        $result->por_recomendacao = $this->db->group_by('recomendacao')
            ->get()
            ->result();
        
        return $result;
    }

    /**
     * Alertas recentes para o dashboard
     */
    public function get_recentes($limit = 10, $empresa_id = null) {
        $this->db->select('a.*, 
                          e.nome as empresa_nome,
                          l.titulo as licitacao_titulo,
                          l.numero_edital,
                          l.orgao_nome,
                          l.uf,
                          l.data_abertura_proposta,
                          DATEDIFF(l.data_abertura_proposta, NOW()) as dias_para_abertura', FALSE)
            ->from('alertas_licitacao a')
            ->join('empresas e', 'e.id = a.empresa_id')
            ->join('licitacoes l', 'l.id = a.licitacao_id')
            ->where('e.ativo', 1)
            ->order_by('a.data_criacao', 'DESC')
            ->limit($limit);
        
        if ($empresa_id) {
            $this->db->where('a.empresa_id', $empresa_id);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Alertas urgentes (prazo curto + score alto)
     */
    public function get_urgentes($limit = 10, $empresa_id = null) {
        $this->db->select('a.*, 
                          e.nome as empresa_nome,
                          l.titulo as licitacao_titulo,
                          l.objeto,
                          l.numero_edital,
                          l.data_abertura_proposta,
                          DATEDIFF(l.data_abertura_proposta, NOW()) as dias_para_abertura', FALSE)
            ->from('alertas_licitacao a')
            ->join('empresas e', 'e.id = a.empresa_id')
            ->join('licitacoes l', 'l.id = a.licitacao_id')
            ->where('e.ativo', 1)
            ->where('a.status !=', 'DESCARTADO')
            ->where('DATEDIFF(l.data_abertura_proposta, NOW()) >=', 0)
            ->where('DATEDIFF(l.data_abertura_proposta, NOW()) <=', 7) // Próximos 7 dias
            ->order_by('l.data_abertura_proposta', 'ASC')
            ->limit($limit);
        
        if ($empresa_id) {
            $this->db->where('a.empresa_id', $empresa_id);
        }
        
        return $this->db->get()->result();
    }

    // =====================================================
    // HISTÓRICO DE EXECUÇÕES
    // =====================================================

    /**
     * Registrar início de execução do monitoramento
     */
    public function iniciar_execucao($empresa_id = null, $tipo = 'AUTOMATICO') {
        $data = [
            'empresa_id' => $empresa_id,
            'tipo_execucao' => $tipo,
            'status' => 'INICIADO',
            'data_inicio' => date('Y-m-d H:i:s')
        ];
        
        $this->db->insert('monitoramento_execucoes', $data);
        return $this->db->insert_id();
    }

    /**
     * Finalizar execução do monitoramento
     */
    public function finalizar_execucao($execucao_id, $stats, $erro = null) {
        $data = [
            'status' => $erro ? 'ERRO' : 'CONCLUIDO',
            'licitacoes_analisadas' => $stats['analisadas'] ?? 0,
            'alertas_gerados' => $stats['gerados'] ?? 0,
            'alertas_atualizados' => $stats['atualizados'] ?? 0,
            'tempo_execucao_segundos' => $stats['tempo'] ?? 0,
            'erro_mensagem' => $erro,
            'data_fim' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->where('id', $execucao_id)->update('monitoramento_execucoes', $data);
    }

    /**
     * Buscar histórico de execuções
     */
    public function get_execucoes($limit = 20, $empresa_id = null) {
        $this->db->from('monitoramento_execucoes')
            ->order_by('data_inicio', 'DESC')
            ->limit($limit);
        
        if ($empresa_id) {
            $this->db->where('empresa_id', $empresa_id);
        }
        
        return $this->db->get()->result();
    }

    // =====================================================
    // MATCHING DE KEYWORDS
    // =====================================================

    /**
     * Executar matching de keywords para uma empresa
     */
    public function executar_matching($empresa_id, $licitacoes = null) {
        $this->load->model('Empresa_model');
        $this->load->model('Licitacao_model');
        
        $empresa = $this->Empresa_model->get_by_id($empresa_id);
        if (!$empresa || !$empresa->ativo) {
            return ['success' => false, 'message' => 'Empresa não encontrada ou inativa'];
        }
        
        $keywords_data = $this->Empresa_model->get_keywords($empresa_id);
        $keywords = $keywords_data['keywords'];
        
        if (empty($keywords)) {
            return ['success' => false, 'message' => 'Empresa não possui keywords configuradas'];
        }
        
        // Buscar configurações de monitoramento
        $config = $this->Empresa_model->get_monitoramento_config($empresa_id);
        $score_minimo = $config->score_minimo_alerta ?? 30;
        
        // Se não foram passadas licitações, buscar novas
        if (!$licitacoes) {
            // Buscar licitações divulgadas (abertas para participação)
            $filters = ['situacao' => 'Divulgada no PNCP'];
            
            if ($config && !empty($config->ufs_interesse)) {
                $filters['uf'] = $config->ufs_interesse;
            }
            
            $licitacoes = $this->Licitacao_model->get_all(500, 0, $filters);
        }
        
        $alertas_gerados = 0;
        $alertas_atualizados = 0;
        
        foreach ($licitacoes as $licitacao) {
            $resultado = $this->calcular_match($empresa, $keywords, $licitacao, $config);
            
            if ($resultado['score_total'] >= $score_minimo) {
                // Verificar se já existe
                if ($this->exists($empresa_id, $licitacao->id)) {
                    // Atualizar score se mudou significativamente
                    $this->update_by_empresa_licitacao($empresa_id, $licitacao->id, $resultado);
                    $alertas_atualizados++;
                } else {
                    // Criar novo alerta
                    $resultado['empresa_id'] = $empresa_id;
                    $resultado['licitacao_id'] = $licitacao->id;
                    $this->create($resultado);
                    $alertas_gerados++;
                }
            }
        }
        
        return [
            'success' => true,
            'gerados' => $alertas_gerados,
            'atualizados' => $alertas_atualizados,
            'analisadas' => count($licitacoes)
        ];
    }

    /**
     * Calcular match entre empresa e licitação
     */
    public function calcular_match($empresa, $keywords, $licitacao, $config = null) {
        $score_keywords = 0;
        $score_segmento = 0;
        $score_localizacao = 0;
        $score_porte = 0;
        $score_valor = 0;
        $keywords_match = [];
        $itens_match = [];
        
        // 1. SCORE DE KEYWORDS (até 50 pontos)
        $texto_licitacao = strtolower(
            ($licitacao->titulo ?? '') . ' ' .
            ($licitacao->objeto ?? '') . ' ' .
            ($licitacao->descricao ?? '')
        );
        
        // Buscar itens para análise mais profunda
        $this->load->model('Licitacao_model');
        $itens = $this->Licitacao_model->get_itens($licitacao->id);
        
        foreach ($itens as $item) {
            $texto_licitacao .= ' ' . strtolower($item->descricao ?? '');
        }
        
        // Remover acentos para comparação
        $texto_licitacao = $this->remover_acentos($texto_licitacao);
        
        $total_keywords = count($keywords);
        $matches_encontrados = 0;
        
        foreach ($keywords as $keyword) {
            $keyword_lower = strtolower($this->remover_acentos($keyword));
            
            if (strpos($texto_licitacao, $keyword_lower) !== false) {
                $keywords_match[] = $keyword;
                $matches_encontrados++;
                
                // Verificar em quais itens a keyword aparece
                foreach ($itens as $item) {
                    $item_texto = strtolower($this->remover_acentos($item->descricao ?? ''));
                    if (strpos($item_texto, $keyword_lower) !== false) {
                        if (!in_array($item->id, $itens_match)) {
                            $itens_match[] = $item->id;
                        }
                    }
                }
            }
        }
        
        if ($total_keywords > 0) {
            $percentual_match = ($matches_encontrados / $total_keywords) * 100;
            $score_keywords = min(50, $percentual_match * 0.5);
        }
        
        // 2. SCORE DE LOCALIZAÇÃO (até 20 pontos)
        if ($config && !empty($config->ufs_interesse)) {
            $ufs = is_string($config->ufs_interesse) ? json_decode($config->ufs_interesse, true) : $config->ufs_interesse;
            if (in_array($licitacao->uf, $ufs)) {
                $score_localizacao = 20;
            }
        } elseif ($empresa->uf && $licitacao->uf) {
            if ($empresa->uf === $licitacao->uf) {
                $score_localizacao = 20;
            } elseif ($this->mesma_regiao($empresa->uf, $licitacao->uf)) {
                $score_localizacao = 10;
            }
        }
        
        // 3. SCORE DE PORTE (até 15 pontos)
        // ME/EPP tem vantagem em licitações exclusivas
        if (in_array($empresa->porte, ['MEI', 'ME', 'EPP'])) {
            $texto_lower = strtolower($texto_licitacao);
            if (strpos($texto_lower, 'exclusiv') !== false || 
                strpos($texto_lower, 'me/epp') !== false ||
                strpos($texto_lower, 'microempresa') !== false) {
                $score_porte = 15;
            } else {
                $score_porte = 10;
            }
        } else {
            $score_porte = 5;
        }
        
        // 4. SCORE DE VALOR (até 15 pontos)
        $valor_licitacao = $licitacao->valor_estimado ?? 0;
        if ($config && $valor_licitacao > 0) {
            $valor_min = $config->valor_minimo ?? 0;
            $valor_max = $config->valor_maximo ?? PHP_INT_MAX;
            
            if ($valor_licitacao >= $valor_min && $valor_licitacao <= $valor_max) {
                $score_valor = 15;
            } elseif ($valor_licitacao >= $valor_min * 0.5 && $valor_licitacao <= $valor_max * 1.5) {
                $score_valor = 8;
            }
        } else {
            $score_valor = 10; // Score neutro se não há filtro de valor
        }
        
        // SCORE TOTAL
        $score_total = round($score_keywords + $score_segmento + $score_localizacao + $score_porte + $score_valor);
        
        // Determinar recomendação
        $recomendacao = 'BAIXA';
        if ($score_total >= 80) {
            $recomendacao = 'ALTA';
        } elseif ($score_total >= 60) {
            $recomendacao = 'MEDIA';
        } elseif ($score_total < 30) {
            $recomendacao = 'NAO_RECOMENDADO';
        }
        
        // Calcular dias para abertura
        $dias_abertura = 30;
        if ($licitacao->data_abertura_proposta) {
            $dias_abertura = (strtotime($licitacao->data_abertura_proposta) - time()) / 86400;
        }
        
        return [
            'score_total' => $score_total,
            'score_keywords' => round($score_keywords),
            'score_segmento' => round($score_segmento),
            'score_localizacao' => round($score_localizacao),
            'score_porte' => round($score_porte),
            'score_valor' => round($score_valor),
            'keywords_match' => $keywords_match,
            'itens_match' => $itens_match,
            'recomendacao' => $recomendacao,
            'dias_para_abertura' => round($dias_abertura)
        ];
    }

    /**
     * Atualizar alerta por empresa e licitação
     */
    private function update_by_empresa_licitacao($empresa_id, $licitacao_id, $data) {
        $update_data = [
            'score_total' => $data['score_total'],
            'score_keywords' => $data['score_keywords'],
            'score_localizacao' => $data['score_localizacao'],
            'score_porte' => $data['score_porte'],
            'score_valor' => $data['score_valor'],
            'keywords_match' => json_encode($data['keywords_match'], JSON_UNESCAPED_UNICODE),
            'itens_match' => json_encode($data['itens_match'], JSON_UNESCAPED_UNICODE),
            'recomendacao' => $data['recomendacao'],
            'prioridade' => $this->calcular_prioridade($data)
        ];
        
        return $this->db->where('empresa_id', $empresa_id)
            ->where('licitacao_id', $licitacao_id)
            ->update('alertas_licitacao', $update_data);
    }

    /**
     * Verificar se UFs são da mesma região
     */
    private function mesma_regiao($uf1, $uf2) {
        $regioes = [
            'norte' => ['AC', 'AP', 'AM', 'PA', 'RO', 'RR', 'TO'],
            'nordeste' => ['AL', 'BA', 'CE', 'MA', 'PB', 'PE', 'PI', 'RN', 'SE'],
            'centro_oeste' => ['DF', 'GO', 'MT', 'MS'],
            'sudeste' => ['ES', 'MG', 'RJ', 'SP'],
            'sul' => ['PR', 'RS', 'SC']
        ];
        
        foreach ($regioes as $regiao => $ufs) {
            if (in_array($uf1, $ufs) && in_array($uf2, $ufs)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Remover acentos de string
     */
    private function remover_acentos($string) {
        $acentos = [
            'á','à','â','ã','ä','Á','À','Â','Ã','Ä',
            'é','è','ê','ë','É','È','Ê','Ë',
            'í','ì','î','ï','Í','Ì','Î','Ï',
            'ó','ò','ô','õ','ö','Ó','Ò','Ô','Õ','Ö',
            'ú','ù','û','ü','Ú','Ù','Û','Ü',
            'ç','Ç','ñ','Ñ'
        ];
        
        $sem_acentos = [
            'a','a','a','a','a','A','A','A','A','A',
            'e','e','e','e','E','E','E','E',
            'i','i','i','i','I','I','I','I',
            'o','o','o','o','o','O','O','O','O','O',
            'u','u','u','u','U','U','U','U',
            'c','C','n','N'
        ];
        
        return str_replace($acentos, $sem_acentos, $string);
    }

    /**
     * Executar matching para todas as empresas com monitoramento ativo
     */
    public function executar_matching_global() {
        $this->load->model('Empresa_model');
        $this->load->model('Licitacao_model');
        
        // Buscar empresas com monitoramento ativo
        $empresas = $this->Empresa_model->get_empresas_monitoramento_ativo();
        
        if (empty($empresas)) {
            return [
                'success' => true,
                'novos_alertas' => 0,
                'total_verificadas' => 0,
                'message' => 'Nenhuma empresa com monitoramento ativo'
            ];
        }
        
        // Buscar licitações divulgadas (abertas para participação)
        $licitacoes = $this->Licitacao_model->get_all(1000, 0, ['situacao' => 'Divulgada no PNCP']);
        
        $total_novos = 0;
        $total_atualizados = 0;
        
        foreach ($empresas as $empresa) {
            $resultado = $this->executar_matching($empresa->id, $licitacoes);
            if ($resultado['success']) {
                $total_novos += $resultado['gerados'];
                $total_atualizados += $resultado['atualizados'];
            }
        }
        
        // Registrar execução
        $this->db->insert('monitoramento_execucoes', [
            'tipo_execucao' => 'MANUAL',
            'licitacoes_analisadas' => count($licitacoes),
            'alertas_gerados' => $total_novos,
            'alertas_atualizados' => $total_atualizados,
            'status' => 'CONCLUIDO',
            'data_inicio' => date('Y-m-d H:i:s'),
            'data_fim' => date('Y-m-d H:i:s'),
            'tempo_execucao_segundos' => 0
        ]);
        
        return [
            'success' => true,
            'novos_alertas' => $total_novos,
            'total_verificadas' => count($licitacoes),
            'empresas_processadas' => count($empresas)
        ];
    }
}
