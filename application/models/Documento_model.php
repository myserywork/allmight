<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Documento_model extends CI_Model {

    private $table = 'empresa_documentos';
    private $upload_path = 'uploads/documentos/';
    
    // Tipos de documentos disponíveis organizados por categoria
    public $tipos_documentos = [
        'CERTIDAO' => [
            'CND_FEDERAL' => 'Certidão Negativa de Débitos Federais',
            'CND_ESTADUAL' => 'Certidão Negativa de Débitos Estaduais',
            'CND_MUNICIPAL' => 'Certidão Negativa de Débitos Municipais',
            'CND_TRABALHISTA' => 'Certidão Negativa de Débitos Trabalhistas (CNDT)',
            'CND_FGTS' => 'Certificado de Regularidade do FGTS (CRF)',
            'CERTIDAO_FALENCIA' => 'Certidão Negativa de Falência e Recuperação',
            'CERTIDAO_ACOES_CIVEIS' => 'Certidão de Ações Cíveis e Criminais',
            'CPD_EN' => 'Certidão Positiva com Efeitos de Negativa'
        ],
        'RECEITA' => [
            'CARTAO_CNPJ' => 'Cartão CNPJ',
            'INSCRICAO_ESTADUAL' => 'Inscrição Estadual',
            'INSCRICAO_MUNICIPAL' => 'Inscrição Municipal',
            'SIMPLES_NACIONAL' => 'Opção pelo Simples Nacional',
            'REGIME_TRIBUTARIO' => 'Comprovante de Regime Tributário'
        ],
        'SOCIETARIO' => [
            'CONTRATO_SOCIAL' => 'Contrato Social',
            'ALTERACAO_CONTRATUAL' => 'Alteração Contratual',
            'ESTATUTO_SOCIAL' => 'Estatuto Social',
            'ATA_ASSEMBLEIA' => 'Ata de Assembleia',
            'PROCURACAO' => 'Procuração',
            'REQUERIMENTO_EMPRESARIO' => 'Requerimento de Empresário'
        ],
        'HABILITACAO' => [
            'ATESTADO_CAPACIDADE' => 'Atestado de Capacidade Técnica',
            'REGISTRO_CONSELHO' => 'Registro em Conselho (CREA, CRM, CRC, etc)',
            'ALVARA_FUNCIONAMENTO' => 'Alvará de Funcionamento',
            'LICENCA_AMBIENTAL' => 'Licença Ambiental',
            'LICENCA_SANITARIA' => 'Licença Sanitária (ANVISA)',
            'CERTIFICACAO_ISO' => 'Certificação ISO',
            'CERTIFICACAO_QUALIDADE' => 'Outras Certificações de Qualidade'
        ],
        'FINANCEIRO' => [
            'BALANCO_PATRIMONIAL' => 'Balanço Patrimonial',
            'DRE' => 'Demonstração de Resultado do Exercício',
            'BALANCETE' => 'Balancete',
            'COMPROVANTE_BANCARIO' => 'Comprovante de Conta Bancária',
            'PATRIMONIO_LIQUIDO' => 'Comprovante de Patrimônio Líquido'
        ],
        'OUTROS' => [
            'COMPROVANTE_ENDERECO' => 'Comprovante de Endereço',
            'DOCUMENTO_SOCIO' => 'Documento Pessoal de Sócio',
            'DECLARACAO' => 'Declaração',
            'OUTRO' => 'Outros Documentos'
        ]
    ];

    public function __construct() {
        parent::__construct();
        $this->load->database();
        
        // Garantir que a pasta de uploads existe
        $full_path = FCPATH . $this->upload_path;
        if (!is_dir($full_path)) {
            mkdir($full_path, 0755, true);
        }
    }

    /**
     * Retorna todos os tipos de documentos
     */
    public function get_tipos() {
        return $this->tipos_documentos;
    }

    /**
     * Retorna nome legível do tipo
     */
    public function get_tipo_nome($tipo) {
        foreach ($this->tipos_documentos as $categoria => $tipos) {
            if (isset($tipos[$tipo])) {
                return $tipos[$tipo];
            }
        }
        return $tipo;
    }

    /**
     * Retorna categoria do tipo
     */
    public function get_categoria_por_tipo($tipo) {
        foreach ($this->tipos_documentos as $categoria => $tipos) {
            if (isset($tipos[$tipo])) {
                return $categoria;
            }
        }
        return 'OUTROS';
    }

    /**
     * Lista documentos de uma empresa
     */
    public function get_by_empresa($empresa_id, $filtros = []) {
        $this->db->where('empresa_id', $empresa_id);
        
        if (!empty($filtros['categoria'])) {
            $this->db->where('categoria', $filtros['categoria']);
        }
        
        if (!empty($filtros['tipo'])) {
            $this->db->where('tipo', $filtros['tipo']);
        }
        
        if (!empty($filtros['status'])) {
            $this->db->where('status', $filtros['status']);
        }
        
        if (isset($filtros['vencidos']) && $filtros['vencidos']) {
            $this->db->where('data_validade <', date('Y-m-d'));
        }
        
        if (isset($filtros['a_vencer']) && $filtros['a_vencer']) {
            $this->db->where('data_validade IS NOT NULL');
            $this->db->where('data_validade >=', date('Y-m-d'));
            $this->db->where('data_validade <=', date('Y-m-d', strtotime('+30 days')));
        }
        
        $this->db->order_by('categoria', 'ASC');
        $this->db->order_by('data_upload', 'DESC');
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Busca documento por ID
     */
    public function get_by_id($id) {
        return $this->db->get_where($this->table, ['id' => $id])->row();
    }

    /**
     * Conta documentos por empresa
     */
    public function count_by_empresa($empresa_id) {
        return $this->db->where('empresa_id', $empresa_id)->count_all_results($this->table);
    }

    /**
     * Estatísticas de documentos da empresa
     */
    public function get_stats($empresa_id) {
        $total = $this->db->where('empresa_id', $empresa_id)->count_all_results($this->table);
        
        $this->db->reset_query();
        $ativos = $this->db->where('empresa_id', $empresa_id)
            ->where('status', 'ATIVO')
            ->count_all_results($this->table);
        
        $this->db->reset_query();
        $vencidos = $this->db->where('empresa_id', $empresa_id)
            ->where('data_validade IS NOT NULL')
            ->where('data_validade <', date('Y-m-d'))
            ->count_all_results($this->table);
        
        $this->db->reset_query();
        $a_vencer = $this->db->where('empresa_id', $empresa_id)
            ->where('data_validade IS NOT NULL')
            ->where('data_validade >=', date('Y-m-d'))
            ->where('data_validade <=', date('Y-m-d', strtotime('+30 days')))
            ->count_all_results($this->table);
        
        // Por categoria
        $this->db->reset_query();
        $por_categoria = $this->db->select('categoria, COUNT(*) as total')
            ->where('empresa_id', $empresa_id)
            ->group_by('categoria')
            ->get($this->table)
            ->result();
        
        return [
            'total' => $total,
            'ativos' => $ativos,
            'vencidos' => $vencidos,
            'a_vencer' => $a_vencer,
            'por_categoria' => $por_categoria
        ];
    }

    /**
     * Lista documentos a vencer nos próximos X dias
     */
    public function get_a_vencer($empresa_id = null, $dias = 30) {
        if ($empresa_id) {
            $this->db->where('empresa_id', $empresa_id);
        }
        
        $this->db->where('data_validade IS NOT NULL');
        $this->db->where('data_validade >=', date('Y-m-d'));
        $this->db->where('data_validade <=', date('Y-m-d', strtotime("+{$dias} days")));
        $this->db->where('alerta_vencimento', true);
        $this->db->order_by('data_validade', 'ASC');
        
        // Join para pegar nome da empresa
        $this->db->select('empresa_documentos.*, empresas.nome as empresa_nome');
        $this->db->join('empresas', 'empresas.id = empresa_documentos.empresa_id');
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Lista documentos vencidos
     */
    public function get_vencidos($empresa_id = null) {
        if ($empresa_id) {
            $this->db->where('empresa_id', $empresa_id);
        }
        
        $this->db->where('data_validade IS NOT NULL');
        $this->db->where('data_validade <', date('Y-m-d'));
        $this->db->order_by('data_validade', 'DESC');
        
        // Join para pegar nome da empresa
        $this->db->select('empresa_documentos.*, empresas.nome as empresa_nome');
        $this->db->join('empresas', 'empresas.id = empresa_documentos.empresa_id');
        
        return $this->db->get($this->table)->result();
    }

    /**
     * Cria novo documento (com upload)
     */
    public function create($data, $arquivo = null) {
        // Se tiver arquivo, fazer upload
        if ($arquivo && !empty($arquivo['tmp_name'])) {
            $upload_result = $this->_upload_arquivo($arquivo, $data['empresa_id']);
            if (!$upload_result['success']) {
                return $upload_result;
            }
            $data['arquivo'] = $upload_result['arquivo'];
            $data['arquivo_original'] = $upload_result['arquivo_original'];
            $data['tamanho_bytes'] = $upload_result['tamanho'];
            $data['mime_type'] = $upload_result['mime_type'];
        }
        
        // Definir categoria automaticamente se não informada
        if (empty($data['categoria'])) {
            $data['categoria'] = $this->get_categoria_por_tipo($data['tipo']);
        }
        
        // Verificar status baseado na validade
        if (!empty($data['data_validade']) && strtotime($data['data_validade']) < time()) {
            $data['status'] = 'VENCIDO';
        }
        
        $data['data_upload'] = date('Y-m-d H:i:s');
        
        $this->db->insert($this->table, $data);
        $id = $this->db->insert_id();
        
        if ($id) {
            return ['success' => true, 'id' => $id, 'message' => 'Documento cadastrado com sucesso!'];
        }
        
        return ['success' => false, 'message' => 'Erro ao cadastrar documento'];
    }

    /**
     * Atualiza documento
     */
    public function update($id, $data, $arquivo = null) {
        // Se tiver novo arquivo, fazer upload
        if ($arquivo && !empty($arquivo['tmp_name'])) {
            // Buscar documento atual para deletar arquivo antigo
            $doc_atual = $this->get_by_id($id);
            
            $upload_result = $this->_upload_arquivo($arquivo, $data['empresa_id'] ?? $doc_atual->empresa_id);
            if (!$upload_result['success']) {
                return $upload_result;
            }
            
            // Deletar arquivo antigo
            if ($doc_atual && $doc_atual->arquivo) {
                $this->_delete_arquivo($doc_atual->arquivo);
            }
            
            $data['arquivo'] = $upload_result['arquivo'];
            $data['arquivo_original'] = $upload_result['arquivo_original'];
            $data['tamanho_bytes'] = $upload_result['tamanho'];
            $data['mime_type'] = $upload_result['mime_type'];
        }
        
        // Atualizar categoria se tipo mudar
        if (!empty($data['tipo'])) {
            $data['categoria'] = $this->get_categoria_por_tipo($data['tipo']);
        }
        
        // Verificar status baseado na validade
        if (!empty($data['data_validade']) && strtotime($data['data_validade']) < time()) {
            $data['status'] = 'VENCIDO';
        } elseif (!empty($data['data_validade']) && strtotime($data['data_validade']) >= time()) {
            $data['status'] = 'ATIVO';
        }
        
        $data['data_atualizacao'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $result = $this->db->update($this->table, $data);
        
        if ($result) {
            return ['success' => true, 'message' => 'Documento atualizado com sucesso!'];
        }
        
        return ['success' => false, 'message' => 'Erro ao atualizar documento'];
    }

    /**
     * Exclui documento
     */
    public function delete($id) {
        $doc = $this->get_by_id($id);
        
        if (!$doc) {
            return ['success' => false, 'message' => 'Documento não encontrado'];
        }
        
        // Deletar arquivo
        if ($doc->arquivo) {
            $this->_delete_arquivo($doc->arquivo);
        }
        
        $this->db->where('id', $id);
        $result = $this->db->delete($this->table);
        
        if ($result) {
            return ['success' => true, 'message' => 'Documento excluído com sucesso!'];
        }
        
        return ['success' => false, 'message' => 'Erro ao excluir documento'];
    }

    /**
     * Atualiza status dos documentos vencidos (para rodar periodicamente)
     */
    public function atualizar_status_vencidos() {
        $this->db->where('data_validade IS NOT NULL');
        $this->db->where('data_validade <', date('Y-m-d'));
        $this->db->where('status', 'ATIVO');
        $this->db->update($this->table, ['status' => 'VENCIDO']);
        
        return $this->db->affected_rows();
    }

    /**
     * Faz upload do arquivo
     */
    private function _upload_arquivo($arquivo, $empresa_id) {
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'xls', 'xlsx'];
        $max_size = 10 * 1024 * 1024; // 10MB
        
        // Validar tipo
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            return ['success' => false, 'message' => 'Tipo de arquivo não permitido. Use: ' . implode(', ', $allowed_types)];
        }
        
        // Validar tamanho
        if ($arquivo['size'] > $max_size) {
            return ['success' => false, 'message' => 'Arquivo muito grande. Máximo: 10MB'];
        }
        
        // Criar pasta da empresa se não existir
        $empresa_path = FCPATH . $this->upload_path . $empresa_id . '/';
        if (!is_dir($empresa_path)) {
            mkdir($empresa_path, 0755, true);
        }
        
        // Gerar nome único
        $novo_nome = date('Ymd_His') . '_' . uniqid() . '.' . $ext;
        $destino = $empresa_path . $novo_nome;
        
        if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
            return [
                'success' => true,
                'arquivo' => $empresa_id . '/' . $novo_nome,
                'arquivo_original' => $arquivo['name'],
                'tamanho' => $arquivo['size'],
                'mime_type' => $arquivo['type']
            ];
        }
        
        return ['success' => false, 'message' => 'Erro ao salvar arquivo'];
    }

    /**
     * Deleta arquivo do servidor
     */
    private function _delete_arquivo($arquivo) {
        $path = FCPATH . $this->upload_path . $arquivo;
        if (file_exists($path)) {
            unlink($path);
        }
    }

    /**
     * Retorna caminho completo do arquivo
     */
    public function get_arquivo_path($documento) {
        if (is_numeric($documento)) {
            $documento = $this->get_by_id($documento);
        }
        
        if ($documento && $documento->arquivo) {
            return FCPATH . $this->upload_path . $documento->arquivo;
        }
        
        return null;
    }

    /**
     * Retorna URL do arquivo
     */
    public function get_arquivo_url($documento) {
        if (is_numeric($documento)) {
            $documento = $this->get_by_id($documento);
        }
        
        if ($documento && $documento->arquivo) {
            return base_url($this->upload_path . $documento->arquivo);
        }
        
        return null;
    }

    /**
     * Verifica se documento está válido
     */
    public function is_valido($documento) {
        if (is_numeric($documento)) {
            $documento = $this->get_by_id($documento);
        }
        
        if (!$documento) {
            return false;
        }
        
        // Se não tem data de validade, considera válido
        if (empty($documento->data_validade)) {
            return $documento->status === 'ATIVO';
        }
        
        return strtotime($documento->data_validade) >= time() && $documento->status === 'ATIVO';
    }

    /**
     * Dias até o vencimento
     */
    public function dias_para_vencimento($documento) {
        if (is_numeric($documento)) {
            $documento = $this->get_by_id($documento);
        }
        
        if (!$documento || empty($documento->data_validade)) {
            return null;
        }
        
        $validade = strtotime($documento->data_validade);
        $hoje = strtotime(date('Y-m-d'));
        
        return floor(($validade - $hoje) / 86400);
    }
}
