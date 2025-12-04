<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Licitacao_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lista todas as licitações com filtros
     */
    public function get_all($limit = null, $offset = 0, $filters = []) {
        $this->db->select('id, numero_edital, titulo, orgao_nome, uf, municipio, modalidade, situacao, status, 
                          data_publicacao, data_abertura_proposta, data_encerramento_proposta, 
                          COALESCE(NULLIF(valor_estimado, 0), (SELECT SUM(valor_total_estimado) FROM licitacao_itens WHERE licitacao_id = licitacoes.id)) as valor_estimado,
                          (SELECT COUNT(*) FROM licitacao_itens WHERE licitacao_id = licitacoes.id) as total_itens,
                          data_insercao', FALSE);
        
        // Filtros de texto
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('titulo', $filters['search']);
            $this->db->or_like('numero_edital', $filters['search']);
            $this->db->or_like('orgao_nome', $filters['search']);
            $this->db->or_like('numero_processo', $filters['search']);
            $this->db->or_like('municipio', $filters['search']);
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
        
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        // Filtros de valor
        if (!empty($filters['valor_min'])) {
            $this->db->where('valor_estimado >=', (float)$filters['valor_min']);
        }
        if (!empty($filters['valor_max'])) {
            $this->db->where('valor_estimado <=', (float)$filters['valor_max']);
        }
        
        // Filtros de data
        if (!empty($filters['data_inicio'])) {
            $this->db->where('data_publicacao >=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $this->db->where('data_publicacao <=', $filters['data_fim'] . ' 23:59:59');
        }
        
        // Filtro órgão
        if (!empty($filters['orgao'])) {
            $this->db->like('orgao_nome', $filters['orgao']);
        }
        
        // Ordenação dinâmica
        $order_by = $filters['order_by'] ?? 'data_desc';
        switch ($order_by) {
            case 'data_desc':
                $this->db->order_by('data_publicacao', 'DESC');
                break;
            case 'data_asc':
                $this->db->order_by('data_publicacao', 'ASC');
                break;
            case 'valor_desc':
                $this->db->order_by('valor_estimado', 'DESC');
                break;
            case 'valor_asc':
                $this->db->order_by('valor_estimado', 'ASC');
                break;
            case 'titulo_asc':
                $this->db->order_by('titulo', 'ASC');
                break;
            case 'titulo_desc':
                $this->db->order_by('titulo', 'DESC');
                break;
            case 'abertura_desc':
                $this->db->order_by('data_abertura_proposta', 'DESC');
                break;
            case 'abertura_asc':
                $this->db->order_by('data_abertura_proposta', 'ASC');
                break;
            default:
                $this->db->order_by('data_publicacao', 'DESC');
        }
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get('licitacoes')->result();
    }

    /**
     * Conta total de licitações
     */
    public function count_all($filters = []) {
        // Aplica os mesmos filtros
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('titulo', $filters['search']);
            $this->db->or_like('numero_edital', $filters['search']);
            $this->db->or_like('orgao_nome', $filters['search']);
            $this->db->or_like('numero_processo', $filters['search']);
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
        
        if (!empty($filters['status'])) {
            $this->db->where('status', $filters['status']);
        }
        
        // Filtros de valor
        if (!empty($filters['valor_min'])) {
            $this->db->where('valor_estimado >=', (float)$filters['valor_min']);
        }
        if (!empty($filters['valor_max'])) {
            $this->db->where('valor_estimado <=', (float)$filters['valor_max']);
        }
        
        // Filtros de data
        if (!empty($filters['data_inicio'])) {
            $this->db->where('data_publicacao >=', $filters['data_inicio']);
        }
        if (!empty($filters['data_fim'])) {
            $this->db->where('data_publicacao <=', $filters['data_fim'] . ' 23:59:59');
        }
        
        // Filtro órgão
        if (!empty($filters['orgao'])) {
            $this->db->like('orgao_nome', $filters['orgao']);
        }
        
        return $this->db->count_all_results('licitacoes');
    }

    /**
     * Busca UFs disponíveis
     */
    public function get_ufs() {
        return $this->db->distinct()
            ->select('uf')
            ->where('uf IS NOT NULL')
            ->where('uf !=', '')
            ->order_by('uf', 'ASC')
            ->get('licitacoes')
            ->result();
    }

    /**
     * Busca licitação por ID
     */
    public function get_by_id($id) {
        $licitacao = $this->db->where('id', $id)->get('licitacoes')->row();
        
        // Calcular valor total dos itens se não tiver valor_estimado
        if ($licitacao && (!$licitacao->valor_estimado || $licitacao->valor_estimado == 0)) {
            $valor_itens = $this->db->select_sum('valor_total_estimado')
                ->where('licitacao_id', $id)
                ->get('licitacao_itens')
                ->row()
                ->valor_total_estimado;
            
            // Usar o valor dos itens como valor_estimado
            $licitacao->valor_estimado = $valor_itens;
            $licitacao->valor_total_itens = $valor_itens;
        }
        
        return $licitacao;
    }

    /**
     * Busca itens de uma licitação
     */
    public function get_itens($licitacao_id) {
        return $this->db->where('licitacao_id', $licitacao_id)
            ->order_by('numero_item', 'ASC')
            ->get('licitacao_itens')
            ->result();
    }

    /**
     * Busca arquivos de uma licitação
     */
    public function get_arquivos($licitacao_id) {
        return $this->db->where('licitacao_id', $licitacao_id)
            ->order_by('data_insercao', 'DESC')
            ->get('licitacao_arquivos')
            ->result();
    }

    /**
     * Estatísticas de licitações
     */
    public function get_stats() {
        $total = $this->db->count_all('licitacoes');
        
        $abertas = $this->db->where('status', 'ABERTA')
            ->count_all_results('licitacoes');
        
        $em_andamento = $this->db->where('status', 'EM_ANDAMENTO')
            ->count_all_results('licitacoes');
        
        $encerradas = $this->db->where('status', 'ENCERRADA')
            ->count_all_results('licitacoes');
        
        $valor_total = $this->db->select_sum('valor_estimado')
            ->get('licitacoes')
            ->row()
            ->valor_estimado ?: 0;
        
        // Por UF (top 10)
        $por_uf = $this->db->select('uf, COUNT(*) as total')
            ->where('uf IS NOT NULL')
            ->group_by('uf')
            ->order_by('total', 'DESC')
            ->limit(10)
            ->get('licitacoes')
            ->result();
        
        // Por modalidade (top 10)
        $por_modalidade = $this->db->select('modalidade, COUNT(*) as total')
            ->where('modalidade IS NOT NULL')
            ->group_by('modalidade')
            ->order_by('total', 'DESC')
            ->limit(10)
            ->get('licitacoes')
            ->result();
        
        // Publicadas nos últimos 30 dias
        $ultimos_30_dias = $this->db->where('data_publicacao >=', date('Y-m-d H:i:s', strtotime('-30 days')))
            ->count_all_results('licitacoes');
        
        return [
            'total' => $total,
            'abertas' => $abertas,
            'em_andamento' => $em_andamento,
            'encerradas' => $encerradas,
            'valor_total' => $valor_total,
            'por_uf' => $por_uf,
            'por_modalidade' => $por_modalidade,
            'ultimos_30_dias' => $ultimos_30_dias
        ];
    }

    /**
     * Busca modalidades únicas
     */
    public function get_modalidades() {
        return $this->db->distinct()
            ->select('modalidade')
            ->where('modalidade IS NOT NULL')
            ->where('modalidade !=', '')
            ->order_by('modalidade', 'ASC')
            ->get('licitacoes')
            ->result();
    }

    /**
     * Busca situações únicas
     */
    public function get_situacoes() {
        return $this->db->distinct()
            ->select('situacao')
            ->where('situacao IS NOT NULL')
            ->where('situacao !=', '')
            ->order_by('situacao', 'ASC')
            ->get('licitacoes')
            ->result();
    }
}
