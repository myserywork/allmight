<!-- Content Wrapper -->
<div class="p-4 md:p-6 lg:p-8">
    
    <!-- Breadcrumb e Header -->
    <div class="mb-6">
        <nav class="text-sm mb-3">
            <ol class="flex items-center space-x-2 text-gray-400">
                <li><a href="<?= base_url('admin/dashboard') ?>" class="hover:text-white transition-colors">Dashboard</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= base_url('admin/empresas') ?>" class="hover:text-white transition-colors">Empresas</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= base_url('admin/empresa_documentos/' . $empresa->id) ?>" class="hover:text-white transition-colors">Documentos</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-purple-400"><?= $documento ? 'Editar' : 'Novo' ?> Documento</li>
            </ol>
        </nav>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-white flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                        <i class="fas fa-<?= $documento ? 'edit' : 'upload' ?> text-white"></i>
                    </div>
                    <?= $documento ? 'Editar Documento' : 'Upload de Documento' ?>
                </h1>
                <p class="text-gray-400 mt-1"><?= $empresa->nome ?></p>
            </div>
            
            <a href="<?= base_url('admin/empresa_documentos/' . $empresa->id) ?>" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">
                <i class="fas fa-arrow-left"></i>
                Voltar
            </a>
        </div>
    </div>

    <!-- Form -->
    <form action="<?= base_url('admin/documento_salvar') ?>" method="POST" enctype="multipart/form-data"
          x-data="documentoForm()" @submit="handleSubmit">
        
        <?php if ($documento): ?>
        <input type="hidden" name="id" value="<?= $documento->id ?>">
        <?php endif; ?>
        <input type="hidden" name="empresa_id" value="<?= $empresa->id ?>">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Coluna Principal -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Tipo de Documento -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-folder-open text-purple-400"></i>
                        Tipo de Documento
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Categoria e Tipo -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Categoria *</label>
                                <select name="categoria_select" x-model="categoria" @change="filtrarTipos()"
                                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">Selecione a categoria...</option>
                                    <?php foreach ($tipos as $cat => $cat_tipos): ?>
                                    <option value="<?= $cat ?>"><?= ucfirst(strtolower(str_replace('_', ' ', $cat))) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Tipo do Documento *</label>
                                <select name="tipo" x-model="tipo" required
                                        class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">Selecione o tipo...</option>
                                    <template x-for="(nome, key) in tiposFiltrados" :key="key">
                                        <option :value="key" x-text="nome"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        
                        <!-- Nome e Número -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Nome/Identificação *</label>
                                <input type="text" name="nome" x-model="nome" required
                                       placeholder="Ex: CND Federal - Janeiro/2024"
                                       class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Número do Documento</label>
                                <input type="text" name="numero_documento" x-model="numero_documento"
                                       placeholder="Número de protocolo, certidão, etc."
                                       class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upload do Arquivo -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-file-upload text-blue-400"></i>
                        Arquivo
                        <?php if (!$documento): ?><span class="text-red-400">*</span><?php endif; ?>
                    </h3>
                    
                    <!-- Arquivo atual (se editando) -->
                    <?php if ($documento && $documento->arquivo): ?>
                    <div class="mb-4 p-4 bg-slate-900/50 rounded-xl border border-slate-600">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-500 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-file-<?= strpos($documento->mime_type, 'pdf') !== false ? 'pdf' : (strpos($documento->mime_type, 'image') !== false ? 'image' : 'alt') ?> text-white text-xl"></i>
                                </div>
                                <div>
                                    <p class="text-white font-medium"><?= $documento->arquivo_original ?></p>
                                    <p class="text-gray-400 text-sm">
                                        <?= number_format($documento->tamanho_bytes / 1024, 1) ?> KB
                                        <span class="mx-2">•</span>
                                        Enviado em <?= date('d/m/Y H:i', strtotime($documento->data_upload)) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="<?= base_url('admin/documento_visualizar/' . $documento->id) ?>" target="_blank"
                                   class="p-2 bg-blue-600 hover:bg-blue-500 rounded-lg transition-colors" title="Visualizar">
                                    <i class="fas fa-eye text-white"></i>
                                </a>
                                <a href="<?= base_url('admin/documento_download/' . $documento->id) ?>"
                                   class="p-2 bg-green-600 hover:bg-green-500 rounded-lg transition-colors" title="Download">
                                    <i class="fas fa-download text-white"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm mb-4">
                        <i class="fas fa-info-circle mr-1"></i>
                        Envie um novo arquivo para substituir o atual, ou deixe em branco para manter.
                    </p>
                    <?php endif; ?>
                    
                    <!-- Dropzone -->
                    <div class="relative"
                         x-data="{ isDragging: false }"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="isDragging = false; handleFileDrop($event)">
                        
                        <input type="file" name="arquivo" id="arquivo" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.xls,.xlsx"
                               class="hidden" @change="handleFileSelect($event)">
                        
                        <label for="arquivo"
                               :class="isDragging ? 'border-purple-500 bg-purple-500/10' : 'border-slate-600 hover:border-purple-500'"
                               class="flex flex-col items-center justify-center w-full h-48 border-2 border-dashed rounded-xl cursor-pointer transition-all">
                            
                            <template x-if="!arquivoSelecionado">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="w-16 h-16 mb-4 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-purple-400"></i>
                                    </div>
                                    <p class="mb-2 text-sm text-gray-300">
                                        <span class="font-semibold text-purple-400">Clique para enviar</span> ou arraste e solte
                                    </p>
                                    <p class="text-xs text-gray-500">PDF, DOC, DOCX, JPG, PNG, XLS, XLSX (máx. 10MB)</p>
                                </div>
                            </template>
                            
                            <template x-if="arquivoSelecionado">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <div class="w-16 h-16 mb-4 bg-gradient-to-br from-green-500/20 to-emerald-500/20 rounded-2xl flex items-center justify-center">
                                        <i class="fas fa-check-circle text-3xl text-green-400"></i>
                                    </div>
                                    <p class="mb-1 text-sm text-white font-medium" x-text="arquivoNome"></p>
                                    <p class="text-xs text-gray-400" x-text="arquivoTamanho"></p>
                                    <button type="button" @click.prevent="limparArquivo()" 
                                            class="mt-2 text-xs text-red-400 hover:text-red-300">
                                        <i class="fas fa-times mr-1"></i> Remover
                                    </button>
                                </div>
                            </template>
                        </label>
                    </div>
                </div>

                <!-- Descrição e Observações -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-align-left text-yellow-400"></i>
                        Informações Adicionais
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Descrição</label>
                            <textarea name="descricao" rows="3" x-model="descricao"
                                      placeholder="Descrição detalhada do documento..."
                                      class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Órgão Emissor</label>
                            <input type="text" name="orgao_emissor" x-model="orgao_emissor"
                                   placeholder="Ex: Receita Federal, Secretaria da Fazenda..."
                                   class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Observações</label>
                            <textarea name="observacoes" rows="2" x-model="observacoes"
                                      placeholder="Observações internas sobre o documento..."
                                      class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white placeholder-gray-500 focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Coluna Lateral -->
            <div class="space-y-6">
                
                <!-- Datas -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-green-400"></i>
                        Validade
                    </h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Data de Emissão</label>
                            <input type="date" name="data_emissao" x-model="data_emissao"
                                   class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Data de Validade</label>
                            <input type="date" name="data_validade" x-model="data_validade"
                                   class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-white focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                            <p class="text-xs text-gray-500 mt-1">Deixe em branco se não houver validade</p>
                        </div>
                        
                        <!-- Status de validade -->
                        <template x-if="data_validade">
                            <div class="p-3 rounded-xl" :class="statusValidadeClass">
                                <div class="flex items-center gap-2">
                                    <i class="fas" :class="statusValidadeIcon"></i>
                                    <span class="text-sm font-medium" x-text="statusValidadeText"></span>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Alerta de vencimento -->
                        <div class="flex items-center gap-3 p-3 bg-slate-900/50 rounded-xl">
                            <input type="checkbox" name="alerta_vencimento" value="1" x-model="alerta_vencimento"
                                   class="w-5 h-5 rounded bg-slate-700 border-slate-600 text-purple-500 focus:ring-purple-500 focus:ring-offset-0">
                            <div>
                                <label class="text-white text-sm font-medium cursor-pointer">Alerta de Vencimento</label>
                                <p class="text-gray-500 text-xs">Receber notificação antes do vencimento</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview do tipo -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6" x-show="tipo">
                    <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-blue-400"></i>
                        Tipo Selecionado
                    </h3>
                    
                    <div class="p-4 bg-gradient-to-br from-purple-500/10 to-pink-500/10 rounded-xl border border-purple-500/30">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                                <i class="fas" :class="categoriaIcon"></i>
                            </div>
                            <div>
                                <p class="text-white font-medium" x-text="tipoNome"></p>
                                <p class="text-purple-300 text-sm" x-text="categoria"></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-6">
                    <div class="space-y-3">
                        <button type="submit" :disabled="isSubmitting"
                                class="w-full py-3 px-4 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                            <template x-if="!isSubmitting">
                                <span>
                                    <i class="fas fa-<?= $documento ? 'save' : 'upload' ?> mr-1"></i>
                                    <?= $documento ? 'Salvar Alterações' : 'Enviar Documento' ?>
                                </span>
                            </template>
                            <template x-if="isSubmitting">
                                <span>
                                    <i class="fas fa-spinner fa-spin mr-1"></i>
                                    Processando...
                                </span>
                            </template>
                        </button>
                        
                        <a href="<?= base_url('admin/empresa_documentos/' . $empresa->id) ?>"
                           class="w-full py-3 px-4 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-xl transition-all flex items-center justify-center gap-2">
                            <i class="fas fa-times mr-1"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function documentoForm() {
    return {
        // Valores iniciais do documento (se editando)
        categoria: '<?= $documento ? $this->Documento_model->get_categoria_por_tipo($documento->tipo) : '' ?>',
        tipo: '<?= $documento->tipo ?? '' ?>',
        nome: '<?= addslashes($documento->nome ?? '') ?>',
        numero_documento: '<?= addslashes($documento->numero_documento ?? '') ?>',
        descricao: '<?= addslashes($documento->descricao ?? '') ?>',
        orgao_emissor: '<?= addslashes($documento->orgao_emissor ?? '') ?>',
        observacoes: '<?= addslashes($documento->observacoes ?? '') ?>',
        data_emissao: '<?= $documento->data_emissao ?? '' ?>',
        data_validade: '<?= $documento->data_validade ?? '' ?>',
        alerta_vencimento: <?= ($documento && $documento->alerta_vencimento) ? 'true' : 'true' ?>,
        
        // Arquivo
        arquivoSelecionado: false,
        arquivoNome: '',
        arquivoTamanho: '',
        
        // Estado
        isSubmitting: false,
        
        // Todos os tipos
        todosTipos: <?= json_encode($tipos) ?>,
        tiposFiltrados: {},
        
        init() {
            if (this.categoria) {
                this.filtrarTipos();
            } else {
                // Se não há categoria, mostra todos
                this.tiposFiltrados = {};
                for (const cat in this.todosTipos) {
                    for (const key in this.todosTipos[cat]) {
                        this.tiposFiltrados[key] = this.todosTipos[cat][key];
                    }
                }
            }
        },
        
        filtrarTipos() {
            if (this.categoria && this.todosTipos[this.categoria]) {
                this.tiposFiltrados = this.todosTipos[this.categoria];
            } else {
                this.tiposFiltrados = {};
                for (const cat in this.todosTipos) {
                    for (const key in this.todosTipos[cat]) {
                        this.tiposFiltrados[key] = this.todosTipos[cat][key];
                    }
                }
            }
            // Se o tipo atual não está na lista filtrada, limpa
            if (this.tipo && !this.tiposFiltrados[this.tipo]) {
                this.tipo = '';
            }
        },
        
        get tipoNome() {
            for (const cat in this.todosTipos) {
                if (this.todosTipos[cat][this.tipo]) {
                    return this.todosTipos[cat][this.tipo];
                }
            }
            return this.tipo;
        },
        
        get categoriaIcon() {
            const icons = {
                'CERTIDAO': 'fa-certificate',
                'RECEITA': 'fa-file-invoice',
                'SOCIETARIO': 'fa-building',
                'HABILITACAO': 'fa-award',
                'FINANCEIRO': 'fa-chart-line',
                'OUTROS': 'fa-file-alt'
            };
            return icons[this.categoria] || 'fa-file-alt';
        },
        
        get statusValidadeClass() {
            if (!this.data_validade) return '';
            const hoje = new Date();
            const validade = new Date(this.data_validade);
            const dias = Math.floor((validade - hoje) / (1000 * 60 * 60 * 24));
            
            if (dias < 0) return 'bg-red-500/20 text-red-400';
            if (dias <= 30) return 'bg-yellow-500/20 text-yellow-400';
            return 'bg-green-500/20 text-green-400';
        },
        
        get statusValidadeIcon() {
            if (!this.data_validade) return '';
            const hoje = new Date();
            const validade = new Date(this.data_validade);
            const dias = Math.floor((validade - hoje) / (1000 * 60 * 60 * 24));
            
            if (dias < 0) return 'fa-exclamation-triangle text-red-400';
            if (dias <= 30) return 'fa-clock text-yellow-400';
            return 'fa-check-circle text-green-400';
        },
        
        get statusValidadeText() {
            if (!this.data_validade) return '';
            const hoje = new Date();
            hoje.setHours(0,0,0,0);
            const validade = new Date(this.data_validade);
            validade.setHours(0,0,0,0);
            const dias = Math.floor((validade - hoje) / (1000 * 60 * 60 * 24));
            
            if (dias < 0) return `Vencido há ${Math.abs(dias)} dia${Math.abs(dias) !== 1 ? 's' : ''}`;
            if (dias === 0) return 'Vence hoje!';
            if (dias === 1) return 'Vence amanhã';
            if (dias <= 30) return `Vence em ${dias} dias`;
            return `Válido por ${dias} dias`;
        },
        
        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.setFile(file);
            }
        },
        
        handleFileDrop(event) {
            const file = event.dataTransfer.files[0];
            if (file) {
                // Atualiza o input file
                const input = document.getElementById('arquivo');
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                input.files = dataTransfer.files;
                
                this.setFile(file);
            }
        },
        
        setFile(file) {
            const maxSize = 10 * 1024 * 1024; // 10MB
            const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/gif', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
            const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif', 'xls', 'xlsx'];
            
            const ext = file.name.split('.').pop().toLowerCase();
            
            if (!allowedExtensions.includes(ext)) {
                alert('Tipo de arquivo não permitido! Use: PDF, DOC, DOCX, JPG, PNG, XLS, XLSX');
                this.limparArquivo();
                return;
            }
            
            if (file.size > maxSize) {
                alert('Arquivo muito grande! Máximo: 10MB');
                this.limparArquivo();
                return;
            }
            
            this.arquivoSelecionado = true;
            this.arquivoNome = file.name;
            this.arquivoTamanho = this.formatBytes(file.size);
        },
        
        limparArquivo() {
            document.getElementById('arquivo').value = '';
            this.arquivoSelecionado = false;
            this.arquivoNome = '';
            this.arquivoTamanho = '';
        },
        
        formatBytes(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        },
        
        handleSubmit(event) {
            if (!this.tipo || !this.nome) {
                event.preventDefault();
                alert('Preencha todos os campos obrigatórios!');
                return false;
            }
            
            <?php if (!$documento): ?>
            if (!this.arquivoSelecionado) {
                event.preventDefault();
                alert('Selecione um arquivo para upload!');
                return false;
            }
            <?php endif; ?>
            
            this.isSubmitting = true;
            return true;
        }
    };
}
</script>
