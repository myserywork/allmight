<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Empresa_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Lista todas as empresas
     */
    public function get_all($limit = null, $offset = 0, $filters = []) {
        $this->db->select('id, nome, razao_social, cnpj, porte, uf, cidade, email, telefone, ativo, cnae_principal, cnae_secundarios, segmentos, certificacoes');
        
        // Filtros
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('nome', $filters['search']);
            $this->db->or_like('razao_social', $filters['search']);
            $this->db->or_like('cnpj', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('uf', $filters['uf']);
        }
        
        if (!empty($filters['porte'])) {
            $this->db->where('porte', $filters['porte']);
        }
        
        if (isset($filters['ativo'])) {
            $this->db->where('ativo', $filters['ativo']);
        }
        
        $this->db->order_by('nome', 'ASC');
        
        if ($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get('empresas')->result();
    }

    /**
     * Conta total de empresas
     */
    public function count_all($filters = []) {
        if (!empty($filters['search'])) {
            $this->db->group_start();
            $this->db->like('nome', $filters['search']);
            $this->db->or_like('razao_social', $filters['search']);
            $this->db->or_like('cnpj', $filters['search']);
            $this->db->group_end();
        }
        
        if (!empty($filters['uf'])) {
            $this->db->where('uf', $filters['uf']);
        }
        
        if (!empty($filters['porte'])) {
            $this->db->where('porte', $filters['porte']);
        }
        
        if (isset($filters['ativo'])) {
            $this->db->where('ativo', $filters['ativo']);
        }
        
        return $this->db->count_all_results('empresas');
    }

    /**
     * Busca empresa por ID
     */
    public function get_by_id($id) {
        return $this->db->where('id', $id)->get('empresas')->row();
    }

    /**
     * Busca empresa por CNPJ
     */
    public function get_by_cnpj($cnpj) {
        return $this->db->where('cnpj', $cnpj)->get('empresas')->row();
    }

    /**
     * Cria nova empresa
     */
    public function create($data) {
        // Gera UUID
        $data['id'] = $this->generate_uuid();
        
        // Prepara dados JSON
        if (isset($data['cnae_secundarios']) && is_array($data['cnae_secundarios'])) {
            $data['cnae_secundarios'] = json_encode($data['cnae_secundarios']);
        }
        
        if (isset($data['segmentos']) && is_array($data['segmentos'])) {
            $data['segmentos'] = json_encode($data['segmentos']);
        }
        
        if (isset($data['certificacoes']) && is_array($data['certificacoes'])) {
            $data['certificacoes'] = json_encode($data['certificacoes']);
        }
        
        $this->db->insert('empresas', $data);
        return $data['id'];
    }

    /**
     * Atualiza empresa
     */
    public function update($id, $data) {
        // Prepara dados JSON
        if (isset($data['cnae_secundarios']) && is_array($data['cnae_secundarios'])) {
            $data['cnae_secundarios'] = json_encode($data['cnae_secundarios']);
        }
        
        if (isset($data['segmentos']) && is_array($data['segmentos'])) {
            $data['segmentos'] = json_encode($data['segmentos']);
        }
        
        if (isset($data['certificacoes']) && is_array($data['certificacoes'])) {
            $data['certificacoes'] = json_encode($data['certificacoes']);
        }
        
        $this->db->where('id', $id);
        return $this->db->update('empresas', $data);
    }

    /**
     * Deleta empresa
     */
    public function delete($id) {
        return $this->db->delete('empresas', ['id' => $id]);
    }

    /**
     * Ativa/Desativa empresa
     */
    public function toggle_status($id) {
        $empresa = $this->get_by_id($id);
        if ($empresa) {
            $new_status = !$empresa->ativo;
            $this->db->where('id', $id);
            return $this->db->update('empresas', ['ativo' => $new_status]);
        }
        return false;
    }

    /**
     * Busca perfil da empresa
     */
    public function get_perfil($empresa_id) {
        return $this->db->where('empresa_id', $empresa_id)->get('perfis_empresa')->row();
    }

    /**
     * Cria/Atualiza perfil da empresa
     */
    public function save_perfil($empresa_id, $data) {
        $perfil = $this->get_perfil($empresa_id);
        
        // Prepara dados JSON
        if (isset($data['capacidades_tecnicas']) && is_array($data['capacidades_tecnicas'])) {
            $data['capacidades_tecnicas'] = json_encode($data['capacidades_tecnicas']);
        }
        
        if (isset($data['areas_especialidade']) && is_array($data['areas_especialidade'])) {
            $data['areas_especialidade'] = json_encode($data['areas_especialidade']);
        }
        
        if (isset($data['qualificacoes']) && is_array($data['qualificacoes'])) {
            $data['qualificacoes'] = json_encode($data['qualificacoes']);
        }
        
        if ($perfil) {
            // Update
            $this->db->where('empresa_id', $empresa_id);
            return $this->db->update('perfis_empresa', $data);
        } else {
            // Insert
            $data['id'] = $this->generate_uuid();
            $data['empresa_id'] = $empresa_id;
            $this->db->insert('perfis_empresa', $data);
            return $data['id'];
        }
    }

    /**
     * Busca CEP via API ViaCEP
     */
    public function buscar_cep($cep) {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        
        if (strlen($cep) != 8) {
            return null;
        }
        
        $url = "https://viacep.com.br/ws/{$cep}/json/";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response);
    }

    /**
     * Gera UUID v4
     */
    private function generate_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Cria perfil básico da empresa
     */
    public function create_perfil_basico($empresa_id, $empresa_data) {
        // Monta introdução básica
        $introducao = "A {$empresa_data['razao_social']} é uma empresa ";
        
        if (!empty($empresa_data['porte'])) {
            $porte_desc = [
                'MEI' => 'microempreendedor individual',
                'ME' => 'de pequeno porte (Microempresa)',
                'EPP' => 'de pequeno porte (Empresa de Pequeno Porte)',
                'MEDIO' => 'de médio porte',
                'GRANDE' => 'de grande porte'
            ];
            $introducao .= $porte_desc[$empresa_data['porte']] ?? '';
        }
        
        if (!empty($empresa_data['cidade']) && !empty($empresa_data['uf'])) {
            $introducao .= " localizada em {$empresa_data['cidade']}/{$empresa_data['uf']}.";
        }
        
        // Prepara áreas de especialidade baseado em segmentos
        $areas_especialidade = [];
        if (!empty($empresa_data['segmentos']) && is_array($empresa_data['segmentos'])) {
            $areas_especialidade = array_values(array_filter($empresa_data['segmentos']));
        }
        
        // Prepara capacidades técnicas baseado em certificações
        $capacidades_tecnicas = [];
        if (!empty($empresa_data['certificacoes']) && is_array($empresa_data['certificacoes'])) {
            $capacidades_tecnicas = array_map(function($cert) {
                return ['certificacao' => $cert, 'validado' => false];
            }, array_filter($empresa_data['certificacoes']));
        }
        
        $perfil_data = [
            'introducao' => $introducao,
            'explicacao' => 'Perfil básico criado automaticamente a partir dos dados cadastrais. Aguardando análise de IA para enriquecimento.',
            'areas_especialidade' => $areas_especialidade,
            'capacidades_tecnicas' => $capacidades_tecnicas,
            'versao_perfil' => 1,
            'gerado_por_ia' => 0
        ];
        
        return $this->save_perfil($empresa_id, $perfil_data);
    }

    /**
     * Estatísticas de empresas
     */
    public function get_stats() {
        return [
            'total' => $this->db->count_all('empresas'),
            'ativas' => $this->db->where('ativo', 1)->count_all_results('empresas'),
            'inativas' => $this->db->where('ativo', 0)->count_all_results('empresas'),
            'por_porte' => $this->db->select('porte, COUNT(*) as total')
                ->group_by('porte')
                ->get('empresas')
                ->result(),
            'por_uf' => $this->db->select('uf, COUNT(*) as total')
                ->group_by('uf')
                ->order_by('total', 'DESC')
                ->limit(10)
                ->get('empresas')
                ->result()
        ];
    }

    // =====================================================
    // SISTEMA DE KEYWORDS E MONITORAMENTO
    // =====================================================

    /**
     * Salvar keywords da empresa
     */
    public function save_keywords($empresa_id, $keywords, $gerada_por_ia = false) {
        $data = [
            'keywords' => json_encode($keywords, JSON_UNESCAPED_UNICODE),
            'keywords_geradas_ia' => $gerada_por_ia ? 1 : 0,
            'data_atualizacao' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->where('id', $empresa_id)->update('empresas', $data);
    }

    /**
     * Buscar keywords da empresa
     */
    public function get_keywords($empresa_id) {
        $empresa = $this->db->select('keywords, keywords_geradas_ia')
            ->where('id', $empresa_id)
            ->get('empresas')
            ->row();
        
        if ($empresa && $empresa->keywords) {
            return [
                'keywords' => json_decode($empresa->keywords, true) ?: [],
                'gerada_por_ia' => (bool) $empresa->keywords_geradas_ia
            ];
        }
        
        return ['keywords' => [], 'gerada_por_ia' => false];
    }

    /**
     * Buscar configurações de monitoramento da empresa
     */
    public function get_monitoramento_config($empresa_id) {
        $config = $this->db->where('empresa_id', $empresa_id)
            ->get('empresa_monitoramento')
            ->row();
        
        if ($config) {
            $config->ufs_interesse = json_decode($config->ufs_interesse, true) ?: [];
            $config->modalidades_interesse = json_decode($config->modalidades_interesse, true) ?: [];
            $config->portes_aceitos = json_decode($config->portes_aceitos, true) ?: [];
        }
        
        return $config;
    }

    /**
     * Salvar configurações de monitoramento
     */
    public function save_monitoramento_config($empresa_id, $monitoramento_ativo, $config) {
        // Primeiro atualiza o flag na tabela empresas
        $this->db->where('id', $empresa_id)
            ->update('empresas', ['monitoramento_ativo' => $monitoramento_ativo ? 1 : 0]);
        
        $data = [
            'empresa_id' => $empresa_id,
            'ufs_interesse' => json_encode($config['ufs_interesse'] ?? [], JSON_UNESCAPED_UNICODE),
            'modalidades_interesse' => json_encode($config['modalidades_interesse'] ?? [], JSON_UNESCAPED_UNICODE),
            'valor_minimo' => $config['valor_minimo'] ?? 0,
            'valor_maximo' => $config['valor_maximo'] ?? null,
            'portes_aceitos' => json_encode($config['portes_aceitos'] ?? [], JSON_UNESCAPED_UNICODE),
            'alerta_email' => isset($config['alerta_email']) ? (int)$config['alerta_email'] : 1,
            'alerta_sistema' => isset($config['alerta_sistema']) ? (int)$config['alerta_sistema'] : 1,
            'frequencia_verificacao' => $config['frequencia_verificacao'] ?? 'HORA',
            'score_minimo_alerta' => $config['score_minimo'] ?? 50,
            'ativo' => $monitoramento_ativo ? 1 : 0
        ];
        
        // Verificar se já existe
        $existing = $this->db->where('empresa_id', $empresa_id)->get('empresa_monitoramento')->row();
        
        if ($existing) {
            unset($data['empresa_id']);
            return $this->db->where('empresa_id', $empresa_id)->update('empresa_monitoramento', $data);
        } else {
            return $this->db->insert('empresa_monitoramento', $data);
        }
    }

    /**
     * Ativar/Desativar monitoramento
     */
    public function toggle_monitoramento($empresa_id, $ativo = true) {
        return $this->db->where('id', $empresa_id)
            ->update('empresas', ['monitoramento_ativo' => $ativo ? 1 : 0]);
    }

    /**
     * Buscar empresas com monitoramento ativo
     */
    public function get_empresas_monitoramento_ativo() {
        return $this->db->select('e.*, em.ufs_interesse, em.modalidades_interesse, em.valor_minimo, em.valor_maximo, em.score_minimo_alerta')
            ->from('empresas e')
            ->join('empresa_monitoramento em', 'em.empresa_id = e.id', 'left')
            ->where('e.ativo', 1)
            ->group_start()
                ->where('e.monitoramento_ativo', 1)
                ->or_where('e.monitoramento_ativo IS NULL')
            ->group_end()
            ->group_start()
                ->where('e.keywords IS NOT NULL')
                ->where("e.keywords !=", '[]')
                ->where("e.keywords !=", '')
            ->group_end()
            ->get()
            ->result();
    }

    /**
     * Buscar templates de keywords por segmento/CNAE
     */
    public function get_keywords_templates($segmento = null, $cnae = null) {
        $this->db->from('keywords_templates')->where('ativo', 1);
        
        if ($segmento) {
            $this->db->like('segmento', $segmento);
        }
        
        if ($cnae) {
            $this->db->or_where('cnae', $cnae);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Buscar todas as UFs disponíveis para filtro
     */
    public function get_all_ufs() {
        return $this->db->select('DISTINCT uf')
            ->where('uf IS NOT NULL')
            ->where('uf !=', '')
            ->order_by('uf')
            ->get('licitacoes')
            ->result();
    }
}
