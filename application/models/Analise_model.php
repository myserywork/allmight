<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Analise_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Busca licitações para análise
     */
    public function get_licitacoes_para_analise($limit = 20, $offset = 0, $filters = []) {
        $this->db->select('l.*, 
                          COUNT(DISTINCT li.id) as total_itens,
                          SUM(li.valor_total_estimado) as valor_total_calculado');
        $this->db->from('licitacoes l');
        $this->db->join('licitacao_itens li', 'li.licitacao_id = l.id', 'left');
        
        // Filtros
        if (!empty($filters['processado'])) {
            if ($filters['processado'] === 'sim') {
                $this->db->where('l.processado_ia', true);
            } else {
                $this->db->where('l.processado_ia', false);
            }
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('l.uf', $filters['uf']);
        }
        
        if (!empty($filters['complexidade'])) {
            $this->db->where('l.complexidade_estimada', $filters['complexidade']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('l.status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.titulo', $filters['search']);
            $this->db->or_like('l.numero_edital', $filters['search']);
            $this->db->or_like('l.orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        $this->db->where('l.ativo', true);
        $this->db->group_by('l.id');
        $this->db->order_by('l.processado_ia', 'ASC');
        $this->db->order_by('l.prioridade', 'DESC');
        $this->db->order_by('l.data_publicacao', 'DESC');
        $this->db->limit($limit, $offset);
        
        return $this->db->get()->result();
    }

    /**
     * Conta licitações para análise
     */
    public function count_licitacoes_para_analise($filters = []) {
        $this->db->from('licitacoes l');
        
        if (!empty($filters['processado'])) {
            if ($filters['processado'] === 'sim') {
                $this->db->where('l.processado_ia', true);
            } else {
                $this->db->where('l.processado_ia', false);
            }
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('l.uf', $filters['uf']);
        }
        
        if (!empty($filters['complexidade'])) {
            $this->db->where('l.complexidade_estimada', $filters['complexidade']);
        }
        
        if (!empty($filters['status'])) {
            $this->db->where('l.status', $filters['status']);
        }
        
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('l.titulo', $filters['search']);
            $this->db->or_like('l.numero_edital', $filters['search']);
            $this->db->or_like('l.orgao_nome', $filters['search']);
            $this->db->group_end();
        }
        
        $this->db->where('l.ativo', true);
        
        return $this->db->count_all_results();
    }

    /**
     * Estatísticas de análises
     */
    public function get_stats() {
        $total = $this->db->count_all('licitacoes');
        
        $processadas = $this->db->where('processado_ia', true)
            ->count_all_results('licitacoes');
        
        $pendentes = $this->db->where('processado_ia', false)
            ->count_all_results('licitacoes');
        
        $com_matches = $this->db->where('tem_matches', true)
            ->count_all_results('licitacoes');
        
        // Por complexidade
        $por_complexidade = $this->db->select('complexidade_estimada, COUNT(*) as total')
            ->where('complexidade_estimada IS NOT NULL')
            ->group_by('complexidade_estimada')
            ->get('licitacoes')
            ->result();
        
        return [
            'total' => $total,
            'processadas' => $processadas,
            'pendentes' => $pendentes,
            'com_matches' => $com_matches,
            'percentual_processado' => $total > 0 ? round(($processadas / $total) * 100, 1) : 0,
            'por_complexidade' => $por_complexidade
        ];
    }

    /**
     * Marca licitação como processada
     */
    public function marcar_como_processada($licitacao_id, $dados_analise) {
        $data = [
            'processado_ia' => true,
            'complexidade_estimada' => $dados_analise['complexidade'] ?? null,
            'palavras_chave' => json_encode($dados_analise['palavras_chave'] ?? []),
            'categorias_identificadas' => json_encode($dados_analise['categorias'] ?? []),
            'prioridade' => $dados_analise['prioridade'] ?? 5
        ];
        
        return $this->db->where('id', $licitacao_id)->update('licitacoes', $data);
    }

    /**
     * Busca análise de uma licitação
     */
    public function get_analise($licitacao_id) {
        return $this->db->select('processado_ia, complexidade_estimada, palavras_chave, 
                                 categorias_identificadas, prioridade, data_atualizacao')
            ->where('id', $licitacao_id)
            ->get('licitacoes')
            ->row();
    }

    /**
     * Reprocessar licitação
     */
    public function resetar_analise($licitacao_id) {
        $data = [
            'processado_ia' => false,
            'complexidade_estimada' => null,
            'palavras_chave' => null,
            'categorias_identificadas' => null,
            'prioridade' => 0
        ];
        
        return $this->db->where('id', $licitacao_id)->update('licitacoes', $data);
    }

    /**
     * Análise em lote
     */
    public function processar_lote($licitacao_ids, $dados_analises) {
        foreach ($licitacao_ids as $index => $licitacao_id) {
            if (isset($dados_analises[$index])) {
                $this->marcar_como_processada($licitacao_id, $dados_analises[$index]);
            }
        }
        return true;
    }
}
