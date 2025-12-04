<!-- Content Wrapper -->
<div class="p-4 md:p-6 lg:p-8" x-data="documentosPage()">
    
    <!-- Breadcrumb e Header -->
    <div class="mb-6">
        <nav class="text-sm mb-3">
            <ol class="flex items-center space-x-2 text-gray-400">
                <li><a href="<?= base_url('admin/dashboard') ?>" class="hover:text-white transition-colors">Dashboard</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li><a href="<?= base_url('admin/empresas') ?>" class="hover:text-white transition-colors">Empresas</a></li>
                <li><i class="fas fa-chevron-right text-xs"></i></li>
                <li class="text-purple-400">Documentos</li>
            </ol>
        </nav>
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <!-- Logo da empresa -->
                <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/25">
                    <?php if ($empresa->logo): ?>
                    <img src="<?= base_url('uploads/logos/' . $empresa->logo) ?>" class="w-full h-full object-cover rounded-2xl">
                    <?php else: ?>
                    <span class="text-2xl font-bold text-white"><?= strtoupper(substr($empresa->nome, 0, 2)) ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-white">Documentos</h1>
                    <p class="text-gray-400"><?= $empresa->nome ?></p>
                    <p class="text-gray-500 text-sm"><?= $empresa->cnpj ?></p>
                </div>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="<?= base_url('admin/empresas') ?>" 
                   class="inline-flex items-center gap-2 px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Voltar</span>
                </a>
                
                <a href="<?= base_url('admin/documento_upload/' . $empresa->id) ?>" 
                   class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 transition-all">
                    <i class="fas fa-upload"></i>
                    <span>Novo Documento</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-blue-600/20 to-blue-700/20 backdrop-blur border border-blue-500/30 rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-500/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-folder-open text-blue-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $stats['total'] ?></p>
                    <p class="text-blue-300 text-sm">Total</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-green-600/20 to-green-700/20 backdrop-blur border border-green-500/30 rounded-2xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-green-500/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $stats['ativos'] ?></p>
                    <p class="text-green-300 text-sm">Válidos</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-yellow-600/20 to-yellow-700/20 backdrop-blur border border-yellow-500/30 rounded-2xl p-4 <?= $stats['a_vencer'] > 0 ? 'animate-pulse' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-yellow-500/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-yellow-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $stats['a_vencer'] ?></p>
                    <p class="text-yellow-300 text-sm">A Vencer</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-red-600/20 to-red-700/20 backdrop-blur border border-red-500/30 rounded-2xl p-4 <?= $stats['vencidos'] > 0 ? 'ring-2 ring-red-500/50' : '' ?>">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-500/30 rounded-xl flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-400 text-xl"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white"><?= $stats['vencidos'] ?></p>
                    <p class="text-red-300 text-sm">Vencidos</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertas de vencimento -->
    <?php 
    $docs_a_vencer = array_filter($documentos, function($d) {
        if (empty($d->data_validade)) return false;
        $dias = floor((strtotime($d->data_validade) - time()) / 86400);
        return $dias >= 0 && $dias <= 30;
    });
    $docs_vencidos = array_filter($documentos, function($d) {
        if (empty($d->data_validade)) return false;
        return strtotime($d->data_validade) < time();
    });
    ?>
    
    <?php if (count($docs_vencidos) > 0): ?>
    <div class="mb-6 p-4 bg-red-500/10 border border-red-500/30 rounded-2xl">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-red-500/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-red-400"></i>
            </div>
            <div>
                <h4 class="text-red-400 font-semibold mb-1">Documentos Vencidos</h4>
                <p class="text-gray-300 text-sm">
                    Existem <strong><?= count($docs_vencidos) ?></strong> documento(s) vencido(s) que precisam ser renovados:
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php foreach (array_slice($docs_vencidos, 0, 5) as $doc): ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-red-500/20 text-red-300 rounded-lg text-xs">
                        <i class="fas fa-file-alt"></i>
                        <?= $doc->nome ?>
                    </span>
                    <?php endforeach; ?>
                    <?php if (count($docs_vencidos) > 5): ?>
                    <span class="text-red-400 text-xs">+<?= count($docs_vencidos) - 5 ?> mais</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (count($docs_a_vencer) > 0): ?>
    <div class="mb-6 p-4 bg-yellow-500/10 border border-yellow-500/30 rounded-2xl">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 bg-yellow-500/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-clock text-yellow-400"></i>
            </div>
            <div>
                <h4 class="text-yellow-400 font-semibold mb-1">Documentos a Vencer</h4>
                <p class="text-gray-300 text-sm">
                    Existem <strong><?= count($docs_a_vencer) ?></strong> documento(s) que vencem nos próximos 30 dias:
                </p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <?php foreach (array_slice($docs_a_vencer, 0, 5) as $doc): 
                        $dias = floor((strtotime($doc->data_validade) - time()) / 86400);
                    ?>
                    <span class="inline-flex items-center gap-1 px-2 py-1 bg-yellow-500/20 text-yellow-300 rounded-lg text-xs">
                        <i class="fas fa-file-alt"></i>
                        <?= $doc->nome ?> (<?= $dias ?> dias)
                    </span>
                    <?php endforeach; ?>
                    <?php if (count($docs_a_vencer) > 5): ?>
                    <span class="text-yellow-400 text-xs">+<?= count($docs_a_vencer) - 5 ?> mais</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-4 mb-6">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
            <!-- Filtro por categoria -->
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-1">Categoria</label>
                <select x-model="filtroCategoria" @change="aplicarFiltros()"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Todas as categorias</option>
                    <?php foreach ($tipos as $cat => $cat_tipos): ?>
                    <option value="<?= $cat ?>"><?= ucfirst(strtolower(str_replace('_', ' ', $cat))) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filtro por status -->
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-1">Status</label>
                <select x-model="filtroStatus" @change="aplicarFiltros()"
                        class="w-full px-3 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <option value="">Todos os status</option>
                    <option value="ATIVO">Válidos</option>
                    <option value="VENCIDO">Vencidos</option>
                    <option value="a_vencer">A vencer (30 dias)</option>
                </select>
            </div>
            
            <!-- Busca -->
            <div class="flex-1">
                <label class="block text-xs text-gray-400 mb-1">Buscar</label>
                <div class="relative">
                    <input type="text" x-model="busca" @input.debounce.300ms="aplicarFiltros()"
                           placeholder="Nome do documento..."
                           class="w-full pl-10 pr-4 py-2 bg-slate-900/50 border border-slate-600 rounded-lg text-white placeholder-gray-500 text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                </div>
            </div>
            
            <!-- Limpar filtros -->
            <div class="flex items-end">
                <button @click="limparFiltros()" 
                        class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm transition-colors">
                    <i class="fas fa-times mr-1"></i> Limpar
                </button>
            </div>
        </div>
    </div>

    <!-- Documentos por Categoria -->
    <?php
    // Agrupar por categoria
    $por_categoria = [];
    foreach ($documentos as $doc) {
        $cat = $doc->categoria ?: 'OUTROS';
        if (!isset($por_categoria[$cat])) {
            $por_categoria[$cat] = [];
        }
        $por_categoria[$cat][] = $doc;
    }
    
    // Ordem das categorias
    $ordem_categorias = ['CERTIDAO', 'RECEITA', 'SOCIETARIO', 'HABILITACAO', 'FINANCEIRO', 'OUTROS'];
    ?>
    
    <?php if (empty($documentos)): ?>
    <!-- Estado vazio -->
    <div class="text-center py-16">
        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-br from-purple-500/20 to-pink-500/20 rounded-3xl flex items-center justify-center">
            <i class="fas fa-folder-open text-4xl text-purple-400"></i>
        </div>
        <h3 class="text-xl font-semibold text-white mb-2">Nenhum documento cadastrado</h3>
        <p class="text-gray-400 mb-6">Comece adicionando os documentos da empresa</p>
        <a href="<?= base_url('admin/documento_upload/' . $empresa->id) ?>" 
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white font-semibold rounded-xl shadow-lg shadow-purple-500/25 transition-all">
            <i class="fas fa-upload"></i>
            Upload Primeiro Documento
        </a>
    </div>
    <?php else: ?>
    
    <!-- Grid de categorias -->
    <div class="space-y-6">
        <?php foreach ($ordem_categorias as $cat): ?>
        <?php if (!isset($por_categoria[$cat]) || empty($por_categoria[$cat])) continue; ?>
        
        <?php
        $icons = [
            'CERTIDAO' => 'fa-certificate',
            'RECEITA' => 'fa-file-invoice',
            'SOCIETARIO' => 'fa-building',
            'HABILITACAO' => 'fa-award',
            'FINANCEIRO' => 'fa-chart-line',
            'OUTROS' => 'fa-file-alt'
        ];
        $colors = [
            'CERTIDAO' => 'purple',
            'RECEITA' => 'blue',
            'SOCIETARIO' => 'green',
            'HABILITACAO' => 'yellow',
            'FINANCEIRO' => 'cyan',
            'OUTROS' => 'gray'
        ];
        $icon = $icons[$cat] ?? 'fa-file-alt';
        $color = $colors[$cat] ?? 'gray';
        $cat_nome = ucfirst(strtolower(str_replace('_', ' ', $cat)));
        ?>
        
        <div class="categoria-section" data-categoria="<?= $cat ?>" 
             x-show="!filtroCategoria || filtroCategoria === '<?= $cat ?>'"
             x-transition>
            
            <!-- Header da categoria -->
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-<?= $color ?>-500/30 rounded-xl flex items-center justify-center">
                    <i class="fas <?= $icon ?> text-<?= $color ?>-400"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-white"><?= $cat_nome ?></h3>
                    <p class="text-gray-400 text-sm"><?= count($por_categoria[$cat]) ?> documento(s)</p>
                </div>
            </div>
            
            <!-- Grid de documentos -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                <?php foreach ($por_categoria[$cat] as $doc): 
                    // Calcular status de validade
                    $status_validade = 'sem_validade';
                    $dias_vencimento = null;
                    if ($doc->data_validade) {
                        $dias_vencimento = floor((strtotime($doc->data_validade) - time()) / 86400);
                        if ($dias_vencimento < 0) {
                            $status_validade = 'vencido';
                        } elseif ($dias_vencimento <= 30) {
                            $status_validade = 'a_vencer';
                        } else {
                            $status_validade = 'valido';
                        }
                    }
                    
                    // Ícone do tipo de arquivo
                    $mime = $doc->mime_type ?? '';
                    $file_icon = 'fa-file-alt';
                    if (strpos($mime, 'pdf') !== false) $file_icon = 'fa-file-pdf';
                    elseif (strpos($mime, 'word') !== false || strpos($mime, 'document') !== false) $file_icon = 'fa-file-word';
                    elseif (strpos($mime, 'excel') !== false || strpos($mime, 'spreadsheet') !== false) $file_icon = 'fa-file-excel';
                    elseif (strpos($mime, 'image') !== false) $file_icon = 'fa-file-image';
                ?>
                
                <div class="documento-card bg-slate-800/50 backdrop-blur border border-slate-700/50 rounded-2xl p-4 hover:border-<?= $color ?>-500/50 transition-all group"
                     data-nome="<?= strtolower($doc->nome) ?>"
                     data-status="<?= $doc->status ?>"
                     data-validade="<?= $status_validade ?>"
                     x-show="filtrarDocumento($el)"
                     x-transition>
                    
                    <div class="flex items-start gap-4">
                        <!-- Ícone do arquivo -->
                        <div class="w-14 h-14 bg-gradient-to-br from-<?= $color ?>-500/20 to-<?= $color ?>-600/20 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <i class="fas <?= $file_icon ?> text-2xl text-<?= $color ?>-400"></i>
                        </div>
                        
                        <div class="flex-1 min-w-0">
                            <!-- Nome e tipo -->
                            <h4 class="text-white font-medium truncate" title="<?= $doc->nome ?>">
                                <?= $doc->nome ?>
                            </h4>
                            <p class="text-gray-400 text-sm truncate" title="<?= $this->Documento_model->get_tipo_nome($doc->tipo) ?>">
                                <?= $this->Documento_model->get_tipo_nome($doc->tipo) ?>
                            </p>
                            
                            <!-- Badges de status -->
                            <div class="flex items-center gap-2 mt-2 flex-wrap">
                                <?php if ($status_validade === 'vencido'): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Vencido há <?= abs($dias_vencimento) ?> dias
                                </span>
                                <?php elseif ($status_validade === 'a_vencer'): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-yellow-500/20 text-yellow-400 rounded-full text-xs">
                                    <i class="fas fa-clock"></i>
                                    Vence em <?= $dias_vencimento ?> dias
                                </span>
                                <?php elseif ($status_validade === 'valido'): ?>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs">
                                    <i class="fas fa-check"></i>
                                    Válido
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($doc->numero_documento): ?>
                                <span class="text-gray-500 text-xs">
                                    #<?= $doc->numero_documento ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Arquivo info -->
                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                <?php if ($doc->arquivo_original): ?>
                                <span class="truncate max-w-[150px]" title="<?= $doc->arquivo_original ?>">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    <?= $doc->arquivo_original ?>
                                </span>
                                <?php endif; ?>
                                
                                <?php if ($doc->tamanho_bytes): ?>
                                <span><?= number_format($doc->tamanho_bytes / 1024, 0) ?> KB</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ações -->
                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-slate-700/50">
                        <div class="text-xs text-gray-500">
                            <?php if ($doc->data_validade): ?>
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Validade: <?= date('d/m/Y', strtotime($doc->data_validade)) ?>
                            <?php else: ?>
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Upload: <?= date('d/m/Y', strtotime($doc->data_upload)) ?>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center gap-1">
                            <?php if ($doc->arquivo): ?>
                            <a href="<?= base_url('admin/documento_visualizar/' . $doc->id) ?>" target="_blank"
                               class="p-2 text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 rounded-lg transition-colors"
                               title="Visualizar">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="<?= base_url('admin/documento_download/' . $doc->id) ?>"
                               class="p-2 text-gray-400 hover:text-green-400 hover:bg-green-500/10 rounded-lg transition-colors"
                               title="Download">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php endif; ?>
                            <a href="<?= base_url('admin/documento_editar/' . $doc->id) ?>"
                               class="p-2 text-gray-400 hover:text-yellow-400 hover:bg-yellow-500/10 rounded-lg transition-colors"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button @click="confirmarExclusao(<?= $doc->id ?>, '<?= addslashes($doc->nome) ?>')"
                                    class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-colors"
                                    title="Excluir">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
    
    <!-- Modal de confirmação de exclusão -->
    <div x-show="showDeleteModal" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            
            <!-- Modal -->
            <div class="relative bg-slate-800 border border-slate-700 rounded-2xl max-w-md w-full mx-auto p-6 shadow-2xl"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <div class="w-16 h-16 mx-auto mb-4 bg-red-500/20 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-3xl text-red-400"></i>
                </div>
                
                <h3 class="text-xl font-semibold text-white mb-2">Confirmar Exclusão</h3>
                <p class="text-gray-400 mb-4">
                    Deseja realmente excluir o documento <strong class="text-white" x-text="deleteDocNome"></strong>?
                </p>
                <p class="text-sm text-red-400 mb-6">
                    <i class="fas fa-warning mr-1"></i>
                    Esta ação não pode ser desfeita!
                </p>
                
                <div class="flex items-center gap-3">
                    <button @click="showDeleteModal = false"
                            class="flex-1 py-2.5 px-4 bg-slate-700 hover:bg-slate-600 text-white rounded-xl transition-colors">
                        Cancelar
                    </button>
                    <form :action="'<?= base_url('admin/documento_excluir/') ?>' + deleteDocId" method="POST" class="flex-1">
                        <button type="submit"
                                class="w-full py-2.5 px-4 bg-red-600 hover:bg-red-500 text-white rounded-xl transition-colors">
                            <i class="fas fa-trash mr-1"></i>
                            Excluir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function documentosPage() {
    return {
        filtroCategoria: '<?= $filtros['categoria'] ?? '' ?>',
        filtroStatus: '<?= $filtros['status'] ?? '' ?>',
        busca: '',
        
        showDeleteModal: false,
        deleteDocId: null,
        deleteDocNome: '',
        
        aplicarFiltros() {
            // Os filtros são aplicados via x-show nos elementos
        },
        
        limparFiltros() {
            this.filtroCategoria = '';
            this.filtroStatus = '';
            this.busca = '';
        },
        
        filtrarDocumento(el) {
            const nome = el.dataset.nome || '';
            const status = el.dataset.status || '';
            const validade = el.dataset.validade || '';
            
            // Filtro por busca
            if (this.busca && !nome.includes(this.busca.toLowerCase())) {
                return false;
            }
            
            // Filtro por status
            if (this.filtroStatus) {
                if (this.filtroStatus === 'a_vencer' && validade !== 'a_vencer') {
                    return false;
                } else if (this.filtroStatus === 'VENCIDO' && validade !== 'vencido') {
                    return false;
                } else if (this.filtroStatus === 'ATIVO' && (validade === 'vencido')) {
                    return false;
                }
            }
            
            return true;
        },
        
        confirmarExclusao(id, nome) {
            this.deleteDocId = id;
            this.deleteDocNome = nome;
            this.showDeleteModal = true;
        }
    };
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
