<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Match_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Busca matches com filtros
     */
    public function get_all($limit = 20, $offset = 0, $filters = []) {
        $this->db->select('m.*, 
                          l.titulo as licitacao_titulo, 
                          l.numero_edital, 
                          l.orgao_nome,
                          l.uf as licitacao_uf,
                          l.municipio as licitacao_municipio,
                          l.modalidade,
                          l.status as licitacao_status,
                          COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id)) as valor_estimado,
                          e.nome as empresa_nome,
                          e.cnpj as empresa_cnpj,
                          e.porte as empresa_porte,
                          e.uf as empresa_uf', FALSE);
        $this->db->from('matches m');
        $this->db->join('licitacoes l', 'l.id = m.licitacao_id');
        $this->db->join('empresas e', 'e.id = m.empresa_id');
        
        // Filtros
        if (!empty($filters['empresa_id'])) {
            $this->db->where('m.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('m.status', $filters['status']);
        }
        
        if (!empty($filters['score_min'])) {
            $this->db->where('m.score_total >=', $filters['score_min']);
        }
        
        if (!empty($filters['score_max'])) {
            $this->db->where('m.score_total <=', $filters['score_max']);
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('l.uf', $filters['uf']);
        }
        
        if (!empty($filters['modalidade'])) {
            $this->db->where('l.modalidade', $filters['modalidade']);
        }
        
        if (!empty($filters['valor_min'])) {
            $this->db->where('(COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id))) >=', $filters['valor_min'], FALSE);
        }
        
        if (!empty($filters['valor_max'])) {
            $this->db->where('(COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id))) <=', $filters['valor_max'], FALSE);
        }
        
        if (!empty($filters['data_inicio'])) {
            $this->db->where('m.data_criacao >=', $filters['data_inicio']);
        }
        
        if (!empty($filters['data_fim'])) {
            $this->db->where('m.data_criacao <=', $filters['data_fim'] . ' 23:59:59');
        }
        
        if (isset($filters['visualizado']) && $filters['visualizado'] !== '') {
            $this->db->where('m.visualizado', $filters['visualizado']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.titulo', $filters['search']);
            $this->db->or_like('e.nome', $filters['search']);
            $this->db->or_like('l.numero_edital', $filters['search']);
            $this->db->or_like('l.orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        // Ordenação dinâmica
        $order_by = $filters['order_by'] ?? 'score_desc';
        switch ($order_by) {
            case 'score_desc':
                $this->db->order_by('m.score_total', 'DESC');
                break;
            case 'score_asc':
                $this->db->order_by('m.score_total', 'ASC');
                break;
            case 'valor_desc':
                $this->db->order_by('valor_estimado', 'DESC');
                break;
            case 'valor_asc':
                $this->db->order_by('valor_estimado', 'ASC');
                break;
            case 'data_desc':
                $this->db->order_by('m.data_criacao', 'DESC');
                break;
            case 'data_asc':
                $this->db->order_by('m.data_criacao', 'ASC');
                break;
            case 'empresa_asc':
                $this->db->order_by('e.nome', 'ASC');
                break;
            default:
                $this->db->order_by('m.score_total', 'DESC');
        }
        
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    /**
     * Conta matches
     */
    public function count_all($filters = []) {
        $this->db->from('matches m');
        $this->db->join('licitacoes l', 'l.id = m.licitacao_id');
        $this->db->join('empresas e', 'e.id = m.empresa_id');
        
        if (!empty($filters['empresa_id'])) {
            $this->db->where('m.empresa_id', $filters['empresa_id']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('m.status', $filters['status']);
        }
        
        if (!empty($filters['score_min'])) {
            $this->db->where('m.score_total >=', $filters['score_min']);
        }
        
        if (!empty($filters['score_max'])) {
            $this->db->where('m.score_total <=', $filters['score_max']);
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('l.uf', $filters['uf']);
        }
        
        if (!empty($filters['modalidade'])) {
            $this->db->where('l.modalidade', $filters['modalidade']);
        }
        
        if (!empty($filters['valor_min'])) {
            $this->db->where('(COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id))) >=', $filters['valor_min'], FALSE);
        }
        
        if (!empty($filters['valor_max'])) {
            $this->db->where('(COALESCE(NULLIF(l.valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = l.id))) <=', $filters['valor_max'], FALSE);
        }
        
        if (!empty($filters['data_inicio'])) {
            $this->db->where('m.data_criacao >=', $filters['data_inicio']);
        }
        
        if (!empty($filters['data_fim'])) {
            $this->db->where('m.data_criacao <=', $filters['data_fim'] . ' 23:59:59');
        }
        
        if (isset($filters['visualizado']) && $filters['visualizado'] !== '') {
            $this->db->where('m.visualizado', $filters['visualizado']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.titulo', $filters['search']);
            $this->db->or_like('e.nome', $filters['search']);
            $this->db->or_like('l.numero_edital', $filters['search']);
            $this->db->or_like('l.orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        return $this->db->count_all_results();
    }

    /**
     * Busca match por ID
     */
    public function get_by_id($id) {
        $match = $this->db->select('m.id,
                                 m.licitacao_id,
                                 m.empresa_id,
                                 m.score_total,
                                 m.score_compatibilidade,
                                 m.score_experiencia,
                                 m.score_localizacao,
                                 m.score_valor,
                                 m.chance_vitoria,
                                 m.nivel_concorrencia,
                                 m.status as match_status,
                                 m.recomendacao_ia,
                                 m.pontos_fortes,
                                 m.pontos_fracos,
                                 m.visualizado,
                                 m.data_visualizacao,
                                 m.data_criacao,
                                 l.titulo as licitacao_titulo,
                                 l.numero_controle_pncp,
                                 l.situacao as licitacao_situacao,
                                 l.uf as licitacao_uf,
                                 l.municipio as licitacao_municipio,
                                 e.nome as empresa_nome,
                                 e.porte as empresa_porte,
                                 e.cnpj as empresa_cnpj,
                                 e.uf as empresa_uf,
                                 e.cidade as empresa_cidade,
                                 e.ativo as empresa_ativo')
            ->from('matches m')
            ->join('licitacoes l', 'l.id = m.licitacao_id')
            ->join('empresas e', 'e.id = m.empresa_id')
            ->where('m.id', $id)
            ->get()
            ->row();
        
        // Garantir que scores não sejam NULL e mapear status
        if ($match) {
            $match->score_compatibilidade = $match->score_compatibilidade ?? 0;
            $match->score_experiencia = $match->score_experiencia ?? 0;
            $match->score_localizacao = $match->score_localizacao ?? 0;
            $match->score_valor = $match->score_valor ?? 0;
            $match->status = $match->match_status; // Usar o status do match, não da licitação
        }
        
        return $match;
    }

    /**
     * Busca matches por licitação
     */
    public function get_by_licitacao($licitacao_id) {
        return $this->db->select('m.*, e.nome as empresa_nome, e.cnpj, e.porte, e.uf as empresa_uf')
            ->from('matches m')
            ->join('empresas e', 'e.id = m.empresa_id')
            ->where('m.licitacao_id', $licitacao_id)
            ->order_by('m.score_total', 'DESC')
            ->get()
            ->result();
    }

    /**
     * Estatísticas de matches
     */
    public function get_stats() {
        $total = $this->db->count_all('matches');
        
        $novos = $this->db->where('status', 'NOVO')
            ->where('visualizado', false)
            ->count_all_results('matches');
        
        $analisados = $this->db->where('status', 'ANALISADO')
            ->count_all_results('matches');
        
        $interessados = $this->db->where('status', 'INTERESSADO')
            ->count_all_results('matches');
        
        $propostas = $this->db->where('status', 'PROPOSTA_ENVIADA')
            ->count_all_results('matches');
        
        // Score médio
        $score_medio = $this->db->select_avg('score_total')
            ->get('matches')
            ->row()
            ->score_total ?: 0;
        
        // Por status
        $por_status = $this->db->select('status, COUNT(*) as total')
            ->group_by('status')
            ->get('matches')
            ->result();
        
        // Top empresas
        $top_empresas = $this->db->select('e.nome, e.id, COUNT(m.id) as total_matches, AVG(m.score_total) as score_medio')
            ->from('matches m')
            ->join('empresas e', 'e.id = m.empresa_id')
            ->group_by('m.empresa_id')
            ->order_by('total_matches', 'DESC')
            ->limit(5)
            ->get()
            ->result();
        
        return [
            'total' => $total,
            'novos' => $novos,
            'analisados' => $analisados,
            'interessados' => $interessados,
            'propostas' => $propostas,
            'score_medio' => round($score_medio, 1),
            'por_status' => $por_status,
            'top_empresas' => $top_empresas
        ];
    }

    /**
     * Atualizar status do match
     */
    public function atualizar_status($id, $status, $comentario = null) {
        $data = [
            'status' => $status
        ];
        
        if ($comentario) {
            $data['comentario_usuario'] = $comentario;
        }
        
        return $this->db->where('id', $id)->update('matches', $data);
    }

    /**
     * Marcar como visualizado
     */
    public function marcar_visualizado($id) {
        return $this->db->where('id', $id)->update('matches', [
            'visualizado' => true,
            'data_visualizacao' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Gerar matches para uma licitação
     */
    public function gerar_matches_licitacao($licitacao_id) {
        // Buscar empresas ativas
        $empresas = $this->db->where('ativo', true)->get('empresas')->result();
        
        $matches_criados = 0;
        
        foreach ($empresas as $empresa) {
            // Verificar se já existe match
            $existe = $this->db->where('licitacao_id', $licitacao_id)
                ->where('empresa_id', $empresa->id)
                ->count_all_results('matches');
            
            if ($existe == 0) {
                // Calcular score simplificado (será melhorado pela IA)
                $score = $this->_calcular_score_basico($licitacao_id, $empresa->id);
                
                if ($score >= 30) { // Só cria match se score mínimo for 30
                    $match_data = [
                        'id' => $this->_generate_uuid(),
                        'licitacao_id' => $licitacao_id,
                        'empresa_id' => $empresa->id,
                        'score_total' => $score,
                        'status' => 'NOVO',
                        'modelo_ia' => 'basico',
                        'versao_algoritmo' => '1.0'
                    ];
                    
                    $this->db->insert('matches', $match_data);
                    $matches_criados++;
                }
            }
        }
        
        // Atualizar flag na licitação
        $this->db->where('id', $licitacao_id)->update('licitacoes', ['tem_matches' => true]);
        
        return $matches_criados;
    }

    /**
     * Calcular score básico (sem IA)
     */
    private function _calcular_score_basico($licitacao_id, $empresa_id) {
        $score = 0;
        
        // Buscar dados
        $licitacao = $this->db->where('id', $licitacao_id)->get('licitacoes')->row();
        $empresa = $this->db->where('id', $empresa_id)->get('empresas')->row();
        
        if (!$licitacao || !$empresa) return 0;
        
        // Score por localização (30 pontos)
        if ($licitacao->uf == $empresa->uf) {
            $score += 30;
            if ($licitacao->municipio == $empresa->cidade) {
                $score += 10; // Bonus mesmo município
            }
        }
        
        // Score por porte (20 pontos)
        if ($licitacao->exclusiva_me_epp) {
            if (in_array($empresa->porte, ['MEI', 'ME', 'EPP'])) {
                $score += 20;
            } else {
                $score = 0; // Empresa não pode participar
            }
        } else {
            $score += 10; // Pode participar
        }
        
        // Score por processamento IA (20 pontos)
        if ($licitacao->processado_ia) {
            $score += 20;
        }
        
        // Score por empresa ativa (20 pontos)
        if ($empresa->ativo) {
            $score += 20;
        }
        
        return min($score, 100);
    }

    /**
     * Gerar UUID
     */
    private function _generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Conta matches novos (não visualizados)
     */
    public function count_novos() {
        return $this->db->where('status', 'NOVO')
            ->where('visualizado', false)
            ->count_all_results('matches');
    }
}
