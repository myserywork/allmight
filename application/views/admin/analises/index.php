<!-- Painel de Análises IA -->
<div class="min-h-screen" x-data="analisesIAApp()">
    
    <!-- Header Futurista -->
    <div class="relative overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0 bg-gradient-to-r from-purple-600/10 via-blue-600/10 to-cyan-600/10"></div>
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute top-20 left-20 w-72 h-72 bg-purple-500/20 rounded-full filter blur-3xl animate-pulse"></div>
            <div class="absolute bottom-10 right-20 w-96 h-96 bg-blue-500/20 rounded-full filter blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
        </div>
        
        <div class="relative z-10 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div class="relative">
                        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-2xl flex items-center justify-center shadow-lg shadow-purple-500/30">
                            <i class="fas fa-brain text-white text-2xl"></i>
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-slate-900 animate-pulse"></div>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold bg-gradient-to-r from-white via-purple-200 to-blue-200 bg-clip-text text-transparent">
                            Central de Inteligência
                        </h1>
                        <p class="text-slate-400 flex items-center mt-1">
                            <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                            Sistema operacional • Powered by Google Gemini
                        </p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4">
                    <!-- Status da IA -->
                    <div class="bg-slate-800/50 backdrop-blur-xl rounded-2xl px-5 py-3 border border-slate-700/50">
                        <div class="flex items-center space-x-3">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            <span class="text-green-400 text-sm font-medium">IA Online</span>
                        </div>
                    </div>
                    
                    <!-- Botão Processar Lote -->
                    <button @click="processarLote()" 
                            :disabled="processando"
                            class="group relative overflow-hidden bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold py-3 px-6 rounded-2xl transition-all duration-300 transform hover:scale-105 hover:shadow-xl hover:shadow-purple-500/30 disabled:opacity-50 disabled:transform-none">
                        <div class="absolute inset-0 bg-gradient-to-r from-purple-400 to-blue-400 opacity-0 group-hover:opacity-20 transition-opacity"></div>
                        <span class="relative flex items-center">
                            <i :class="processando ? 'fa-spinner fa-spin' : 'fa-bolt'" class="fas mr-2"></i>
                            <span x-text="processando ? 'Processando...' : 'Processar Pendentes'"></span>
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="px-8 py-6">
        <!-- Cards de Estatísticas Futuristas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Card Total -->
            <div class="group relative bg-slate-800/30 backdrop-blur-xl rounded-3xl p-6 border border-slate-700/50 hover:border-blue-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-blue-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-500/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-database text-blue-400 text-xl"></i>
                        </div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Total</span>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($stats['total']); ?></p>
                    <p class="text-sm text-slate-400">Licitações no sistema</p>
                </div>
            </div>

            <!-- Card Processadas -->
            <div class="group relative bg-slate-800/30 backdrop-blur-xl rounded-3xl p-6 border border-slate-700/50 hover:border-green-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-green-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-green-600/10 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-500/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-check-circle text-green-400 text-xl"></i>
                        </div>
                        <span class="text-xs text-green-400 font-bold"><?php echo $stats['percentual_processado']; ?>%</span>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($stats['processadas']); ?></p>
                    <p class="text-sm text-slate-400">Analisadas pela IA</p>
                    <!-- Progress Bar -->
                    <div class="mt-3 h-1.5 bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-green-500 to-emerald-400 rounded-full transition-all duration-1000" style="width: <?php echo $stats['percentual_processado']; ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Card Pendentes -->
            <div class="group relative bg-slate-800/30 backdrop-blur-xl rounded-3xl p-6 border border-slate-700/50 hover:border-yellow-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-yellow-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-yellow-600/10 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-yellow-500/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-yellow-400 text-xl"></i>
                        </div>
                        <span class="text-xs text-slate-500 uppercase tracking-wider">Aguardando</span>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($stats['pendentes']); ?></p>
                    <p class="text-sm text-slate-400">Pendentes de análise</p>
                </div>
            </div>

            <!-- Card Matches -->
            <div class="group relative bg-slate-800/30 backdrop-blur-xl rounded-3xl p-6 border border-slate-700/50 hover:border-purple-500/50 transition-all duration-500 hover:shadow-2xl hover:shadow-purple-500/10">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-600/10 to-transparent rounded-3xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-purple-500/20 rounded-2xl flex items-center justify-center">
                            <i class="fas fa-link text-purple-400 text-xl"></i>
                        </div>
                        <i class="fas fa-fire text-orange-400 text-sm animate-pulse"></i>
                    </div>
                    <p class="text-4xl font-bold text-white mb-1"><?php echo number_format($stats['com_matches']); ?></p>
                    <p class="text-sm text-slate-400">Com empresas compatíveis</p>
                </div>
            </div>
        </div>

        <!-- Layout Principal: Lista + Chat -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Painel de Licitações (2 colunas) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Filtros Elegantes -->
                <div class="bg-slate-800/30 backdrop-blur-xl rounded-3xl border border-slate-700/50 p-6">
                    <form method="get" action="<?php echo base_url('admin/analises'); ?>" class="space-y-4">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-white font-semibold flex items-center">
                                <i class="fas fa-filter text-purple-400 mr-2"></i>
                                Filtros de Pesquisa
                            </h3>
                            <a href="<?php echo base_url('admin/analises'); ?>" class="text-slate-400 hover:text-white text-sm transition-colors">
                                <i class="fas fa-times mr-1"></i>Limpar
                            </a>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="md:col-span-2">
                                <div class="relative">
                                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                                    <input type="text" name="search" 
                                           value="<?php echo $filters['search'] ?? ''; ?>"
                                           placeholder="Buscar por título, edital ou órgão..."
                                           class="w-full pl-12 pr-4 py-3 bg-slate-900/50 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all">
                                </div>
                            </div>
                            
                            <div>
                                <select name="processado" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-2xl text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all appearance-none cursor-pointer">
                                    <option value="">Status: Todos</option>
                                    <option value="sim" <?php echo ($filters['processado'] ?? '') === 'sim' ? 'selected' : ''; ?>>✅ Processadas</option>
                                    <option value="nao" <?php echo ($filters['processado'] ?? '') === 'nao' ? 'selected' : ''; ?>>⏳ Pendentes</option>
                                </select>
                            </div>
                            
                            <div>
                                <select name="complexidade" class="w-full px-4 py-3 bg-slate-900/50 border border-slate-700 rounded-2xl text-white focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 transition-all appearance-none cursor-pointer">
                                    <option value="">Complexidade</option>
                                    <option value="BAIXA" <?php echo ($filters['complexidade'] ?? '') === 'BAIXA' ? 'selected' : ''; ?>>🟢 Baixa</option>
                                    <option value="MEDIA" <?php echo ($filters['complexidade'] ?? '') === 'MEDIA' ? 'selected' : ''; ?>>🟡 Média</option>
                                    <option value="ALTA" <?php echo ($filters['complexidade'] ?? '') === 'ALTA' ? 'selected' : ''; ?>>🟠 Alta</option>
                                    <option value="MUITO_ALTA" <?php echo ($filters['complexidade'] ?? '') === 'MUITO_ALTA' ? 'selected' : ''; ?>>🔴 Muito Alta</option>
                                </select>
                            </div>
                        </div>
                        
                        <button type="submit" class="w-full md:w-auto px-6 py-3 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white font-semibold rounded-2xl transition-all transform hover:scale-[1.02]">
                            <i class="fas fa-search mr-2"></i>Pesquisar
                        </button>
                    </form>
                </div>

                <!-- Lista de Licitações -->
                <div class="bg-slate-800/30 backdrop-blur-xl rounded-3xl border border-slate-700/50 overflow-hidden">
                    <div class="p-4 border-b border-slate-700/50 flex items-center justify-between">
                        <h3 class="text-white font-semibold">
                            <i class="fas fa-list text-blue-400 mr-2"></i>
                            Licitações
                            <span class="ml-2 text-sm text-slate-500">(<?php echo number_format($total); ?> encontradas)</span>
                        </h3>
                        <div class="text-sm text-slate-400">
                            Página <?php echo $page; ?> de <?php echo $total_pages; ?>
                        </div>
                    </div>
                    
                    <div class="divide-y divide-slate-700/30 max-h-[600px] overflow-y-auto">
                        <?php if (!empty($licitacoes)): ?>
                            <?php foreach ($licitacoes as $lic): ?>
                                <div class="p-5 hover:bg-slate-700/20 transition-all cursor-pointer group"
                                     :class="licitacaoSelecionada?.id == '<?php echo $lic->id; ?>' ? 'bg-purple-600/10 border-l-4 border-purple-500' : ''"
                                     @click="selecionarLicitacao(<?php echo htmlspecialchars(json_encode($lic), ENT_QUOTES, 'UTF-8'); ?>)">
                                    
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 mb-2 flex-wrap">
                                                <!-- Status Badge -->
                                                <?php if ($lic->processado_ia): ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30">
                                                        <i class="fas fa-check mr-1"></i>Analisada
                                                    </span>
                                                <?php else: ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400 border border-yellow-500/30">
                                                        <i class="fas fa-clock mr-1"></i>Pendente
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <!-- Complexidade -->
                                                <?php if ($lic->complexidade_estimada): ?>
                                                    <?php
                                                    $cores_complex = [
                                                        'BAIXA' => ['bg' => 'bg-blue-500/20', 'text' => 'text-blue-400', 'border' => 'border-blue-500/30'],
                                                        'MEDIA' => ['bg' => 'bg-yellow-500/20', 'text' => 'text-yellow-400', 'border' => 'border-yellow-500/30'],
                                                        'ALTA' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-400', 'border' => 'border-orange-500/30'],
                                                        'MUITO_ALTA' => ['bg' => 'bg-red-500/20', 'text' => 'text-red-400', 'border' => 'border-red-500/30']
                                                    ];
                                                    $cor = $cores_complex[$lic->complexidade_estimada] ?? $cores_complex['BAIXA'];
                                                    ?>
                                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold <?php echo $cor['bg'] . ' ' . $cor['text'] . ' border ' . $cor['border']; ?>">
                                                        <?php echo ucfirst(strtolower(str_replace('_', ' ', $lic->complexidade_estimada))); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <!-- Prioridade -->
                                                <?php if ($lic->prioridade > 0): ?>
                                                    <span class="text-yellow-400 text-xs">
                                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                                            <i class="fas fa-star <?php echo $i <= ($lic->prioridade / 2) ? '' : 'opacity-30'; ?>"></i>
                                                        <?php endfor; ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <h4 class="text-white font-semibold group-hover:text-purple-300 transition-colors line-clamp-2 mb-2">
                                                <?php echo htmlspecialchars($lic->titulo); ?>
                                            </h4>
                                            
                                            <div class="flex items-center flex-wrap gap-4 text-sm text-slate-400">
                                                <span class="flex items-center">
                                                    <i class="fas fa-building mr-1.5 text-slate-500"></i>
                                                    <?php echo htmlspecialchars(truncate_text($lic->orgao_nome ?? 'N/I', 30)); ?>
                                                </span>
                                                <span class="flex items-center">
                                                    <i class="fas fa-map-marker-alt mr-1.5 text-slate-500"></i>
                                                    <?php echo htmlspecialchars($lic->municipio ?? 'N/I'); ?> - <?php echo htmlspecialchars($lic->uf ?? ''); ?>
                                                </span>
                                                <?php if ($lic->total_itens > 0): ?>
                                                    <span class="flex items-center">
                                                        <i class="fas fa-boxes mr-1.5 text-slate-500"></i>
                                                        <?php echo $lic->total_itens; ?> itens
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="flex flex-col items-end gap-2">
                                            <?php if ($lic->valor_total_calculado > 0): ?>
                                                <span class="text-lg font-bold text-green-400">
                                                    <?php echo format_currency($lic->valor_total_calculado); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <a href="<?php echo base_url('admin/licitacao/' . $lic->id); ?>" 
                                                   class="w-8 h-8 flex items-center justify-center bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition-colors"
                                                   title="Ver detalhes"
                                                   @click.stop>
                                                    <i class="fas fa-eye text-sm"></i>
                                                </a>
                                                <button @click.stop="analisarLicitacao('<?php echo $lic->id; ?>')"
                                                        :disabled="processandoId === '<?php echo $lic->id; ?>'"
                                                        class="w-8 h-8 flex items-center justify-center bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition-colors disabled:opacity-50"
                                                        title="<?php echo $lic->processado_ia ? 'Reprocessar IA' : 'Analisar com IA'; ?>">
                                                    <i :class="processandoId === '<?php echo $lic->id; ?>' ? 'fa-spinner fa-spin' : 'fa-brain'" class="fas text-sm"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-12 text-center">
                                <div class="w-20 h-20 bg-slate-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-inbox text-slate-500 text-3xl"></i>
                                </div>
                                <p class="text-slate-400 text-lg">Nenhuma licitação encontrada</p>
                                <p class="text-slate-500 text-sm mt-2">Tente ajustar os filtros de pesquisa</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($total > $per_page): ?>
                        <div class="p-4 border-t border-slate-700/50 flex items-center justify-between">
                            <p class="text-sm text-slate-400">
                                Mostrando <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total); ?> de <?php echo $total; ?>
                            </p>
                            <div class="flex space-x-2">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                                       class="px-4 py-2 rounded-xl bg-slate-700/50 border border-slate-600 text-white hover:bg-slate-600 transition-colors">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                                       class="px-4 py-2 rounded-xl <?php echo $i === $page ? 'bg-gradient-to-r from-purple-600 to-blue-600 text-white' : 'bg-slate-700/50 border border-slate-600 text-white hover:bg-slate-600'; ?> transition-colors">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                                       class="px-4 py-2 rounded-xl bg-slate-700/50 border border-slate-600 text-white hover:bg-slate-600 transition-colors">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Painel de Chat IA (1 coluna) -->
            <div class="lg:col-span-1">
                <div class="sticky top-6 bg-slate-800/30 backdrop-blur-xl rounded-3xl border border-slate-700/50 overflow-hidden flex flex-col" style="height: calc(100vh - 200px); min-height: 500px;">
                    
                    <!-- Header do Chat -->
                    <div class="p-4 border-b border-slate-700/50 bg-gradient-to-r from-purple-600/10 to-blue-600/10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center">
                                    <i class="fas fa-robot text-white"></i>
                                </div>
                                <div>
                                    <h3 class="text-white font-semibold">Assistente IA</h3>
                                    <p class="text-xs text-slate-400">
                                        <span x-show="!licitacaoSelecionada">Selecione uma licitação</span>
                                        <span x-show="licitacaoSelecionada" class="text-purple-400" x-text="'Analisando licitação'"></span>
                                    </p>
                                </div>
                            </div>
                            <button @click="limparChat()" class="text-slate-400 hover:text-white transition-colors" title="Limpar conversa">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Área de Contexto (quando licitação selecionada) -->
                    <div x-show="licitacaoSelecionada" class="p-3 bg-purple-900/20 border-b border-slate-700/50">
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 bg-purple-500/20 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-file-alt text-purple-400 text-sm"></i>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-purple-300 text-sm font-medium line-clamp-2" x-text="licitacaoSelecionada?.titulo"></p>
                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500">
                                    <span x-text="(licitacaoSelecionada?.orgao_nome || '').substring(0, 20) + '...'"></span>
                                    <span>•</span>
                                    <span x-text="licitacaoSelecionada?.uf"></span>
                                </div>
                            </div>
                            <button @click="licitacaoSelecionada = null; mensagens = []" class="text-slate-500 hover:text-white text-xs">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mensagens do Chat -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4" id="chatMessages">
                        <!-- Mensagem de boas-vindas -->
                        <template x-if="mensagens.length === 0">
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gradient-to-br from-purple-500/20 to-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-comments text-purple-400 text-2xl"></i>
                                </div>
                                <h4 class="text-white font-semibold mb-2">Olá! Sou seu assistente de IA para licitações</h4>
                                <p class="text-slate-400 text-sm mb-4">Selecione uma licitação ao lado para começar:</p>
                                
                                <!-- Quick Stats Info -->
                                <div class="bg-slate-800/50 rounded-xl p-3 mb-4 text-left">
                                    <div class="flex items-center gap-2 text-xs text-purple-400 mb-2">
                                        <i class="fas fa-info-circle"></i>
                                        <span class="font-semibold">Contexto Disponível</span>
                                    </div>
                                    <p class="text-xs text-slate-400">
                                        Tenho acesso a <strong class="text-blue-400"><?php echo $stats['total_empresas'] ?? 0; ?> empresas</strong> cadastradas, 
                                        seus documentos e a data atual (<?php echo date('d/m/Y'); ?>).
                                    </p>
                                </div>
                                
                                <p class="text-slate-500 text-xs mb-3">Sugestões de perguntas:</p>
                                <div class="space-y-2 text-left max-w-xs mx-auto">
                                    <button @click="enviarSugestao('Faça uma análise completa desta licitação e identifique oportunidades e riscos.')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        💡 Análise completa da licitação
                                    </button>
                                    <button @click="enviarSugestao('Quais das minhas empresas cadastradas são mais compatíveis com esta licitação? Analise cada uma.')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        🏢 Qual empresa devo usar?
                                    </button>
                                    <button @click="enviarSugestao('Verifique se alguma empresa tem documentos vencidos ou próximos do vencimento que impeçam participar.')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        � Verificar documentação das empresas
                                    </button>
                                    <button @click="enviarSugestao('Quais são os documentos de habilitação típicos que devo preparar?')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        📄 Documentos de habilitação
                                    </button>
                                    <button @click="enviarSugestao('Quanto tempo ainda tenho até o prazo? O que devo priorizar?')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        ⏰ Prazo e prioridades
                                    </button>
                                    <button @click="enviarSugestao('Esta licitação é exclusiva para ME/EPP? Qual das minhas empresas se enquadra?')" class="w-full text-left px-4 py-2 bg-slate-700/30 hover:bg-slate-700/50 rounded-xl text-sm text-slate-300 transition-colors">
                                        � ME/EPP - Exclusividade
                                    </button>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Mensagens -->
                        <template x-for="(msg, index) in mensagens" :key="index">
                            <div :class="msg.tipo === 'user' ? 'flex justify-end' : 'flex justify-start'">
                                <div :class="msg.tipo === 'user' ? 'bg-gradient-to-r from-purple-600 to-blue-600 text-white' : 'bg-slate-700/50 text-slate-200'" 
                                     class="max-w-[85%] rounded-2xl px-4 py-3">
                                    <div x-show="msg.tipo === 'ia'" class="flex items-center gap-2 mb-2 text-xs text-slate-400">
                                        <i class="fas fa-robot"></i>
                                        <span>Assistente IA</span>
                                    </div>
                                    <p class="text-sm whitespace-pre-wrap" x-html="formatarMensagem(msg.texto)"></p>
                                    <span class="text-xs opacity-60 mt-1 block" x-text="msg.hora"></span>
                                </div>
                            </div>
                        </template>
                        
                        <!-- Indicador de digitação -->
                        <div x-show="digitando" class="flex justify-start">
                            <div class="bg-slate-700/50 rounded-2xl px-4 py-3">
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.1s;"></div>
                                    <div class="w-2 h-2 bg-purple-400 rounded-full animate-bounce" style="animation-delay: 0.2s;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Input do Chat -->
                    <div class="p-4 border-t border-slate-700/50 bg-slate-900/30">
                        <form @submit.prevent="enviarMensagem()" class="flex items-end gap-3">
                            <div class="flex-1 relative">
                                <textarea 
                                    x-model="mensagemInput"
                                    @keydown.enter.prevent="if(!$event.shiftKey) enviarMensagem()"
                                    placeholder="Digite sua pergunta sobre a licitação..."
                                    rows="1"
                                    class="w-full px-4 py-3 bg-slate-800/50 border border-slate-700 rounded-2xl text-white placeholder-slate-500 focus:outline-none focus:border-purple-500 focus:ring-2 focus:ring-purple-500/20 resize-none transition-all"
                                    :disabled="!licitacaoSelecionada || digitando"
                                    style="min-height: 48px; max-height: 120px;"
                                ></textarea>
                            </div>
                            <button type="submit" 
                                    :disabled="!mensagemInput.trim() || !licitacaoSelecionada || digitando"
                                    class="w-12 h-12 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-2xl flex items-center justify-center transition-all transform hover:scale-105 disabled:opacity-50 disabled:transform-none disabled:cursor-not-allowed">
                                <i :class="digitando ? 'fa-spinner fa-spin' : 'fa-paper-plane'" class="fas"></i>
                            </button>
                        </form>
                        <p class="text-xs text-slate-500 mt-2 text-center">
                            Shift + Enter para nova linha • Enter para enviar
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Análise Detalhada -->
    <div x-show="modalAnaliseAberto" 
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20">
            <!-- Overlay -->
            <div class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm" @click="modalAnaliseAberto = false"></div>

            <!-- Modal -->
            <div class="relative w-full max-w-3xl bg-slate-800 rounded-3xl shadow-2xl shadow-purple-500/20 overflow-hidden"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100">
                
                <!-- Header do Modal -->
                <div class="bg-gradient-to-r from-purple-600/20 to-blue-600/20 px-6 py-4 border-b border-slate-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-brain text-purple-400 mr-3"></i>
                            Análise de IA Detalhada
                        </h3>
                        <button @click="modalAnaliseAberto = false" class="w-10 h-10 rounded-full bg-slate-700/50 hover:bg-slate-600 text-slate-400 hover:text-white flex items-center justify-center transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Conteúdo do Modal -->
                <div class="p-6 max-h-[70vh] overflow-y-auto">
                    <template x-if="analiseAtual">
                        <div class="space-y-6">
                            <!-- Métricas -->
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-slate-700/30 rounded-2xl p-4">
                                    <span class="text-slate-400 text-sm">Complexidade</span>
                                    <p class="text-2xl font-bold text-white mt-2" x-text="analiseAtual.complexidade_estimada || 'N/A'"></p>
                                </div>
                                <div class="bg-slate-700/30 rounded-2xl p-4">
                                    <span class="text-slate-400 text-sm">Prioridade</span>
                                    <div class="flex items-center mt-2">
                                        <template x-for="i in 5">
                                            <i class="fas fa-star text-lg" :class="i <= (analiseAtual.prioridade / 2) ? 'text-yellow-400' : 'text-slate-600'"></i>
                                        </template>
                                        <span class="ml-2 text-white font-bold" x-text="analiseAtual.prioridade || '0'"></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Palavras-chave -->
                            <div class="bg-slate-700/30 rounded-2xl p-4" x-show="analiseAtual.palavras_chave_array?.length">
                                <span class="text-slate-400 text-sm block mb-3">Palavras-chave identificadas</span>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="palavra in analiseAtual.palavras_chave_array" :key="palavra">
                                        <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm border border-blue-500/30" x-text="palavra"></span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Categorias -->
                            <div class="bg-slate-700/30 rounded-2xl p-4" x-show="analiseAtual.categorias_array?.length">
                                <span class="text-slate-400 text-sm block mb-3">Categorias</span>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="cat in analiseAtual.categorias_array" :key="cat">
                                        <span class="px-3 py-1 bg-purple-500/20 text-purple-400 rounded-full text-sm border border-purple-500/30" x-text="cat"></span>
                                    </template>
                                </div>
                            </div>
                            
                            <!-- Data da análise -->
                            <div class="text-center text-slate-500 text-sm pt-4 border-t border-slate-700">
                                <i class="fas fa-clock mr-2"></i>
                                Analisado em: <span x-text="analiseAtual.data_atualizacao"></span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function analisesIAApp() {
    return {
        // Estados
        processando: false,
        processandoId: null,
        licitacaoSelecionada: null,
        mensagens: [],
        mensagemInput: '',
        digitando: false,
        modalAnaliseAberto: false,
        analiseAtual: null,
        
        // Métodos
        selecionarLicitacao(licitacao) {
            this.licitacaoSelecionada = licitacao;
            this.mensagens = [];
            
            // Adicionar mensagem inicial da IA
            const valorFormatado = licitacao.valor_total_calculado 
                ? 'R$ ' + Number(licitacao.valor_total_calculado).toLocaleString('pt-BR', {minimumFractionDigits: 2}) 
                : 'Não informado';
            
            this.adicionarMensagemIA(`Olá! Estou analisando a licitação **"${(licitacao.titulo || '').substring(0, 50)}..."**

📍 **Órgão:** ${licitacao.orgao_nome || 'Não informado'}
📍 **Local:** ${licitacao.municipio || 'N/I'} - ${licitacao.uf || ''}
💰 **Valor estimado:** ${valorFormatado}
📦 **Itens:** ${licitacao.total_itens || 0}

Como posso ajudá-lo? Você pode me perguntar sobre:
• Resumo e análise da licitação
• Documentos necessários
• Riscos e oportunidades
• Empresas compatíveis`);
        },
        
        async enviarMensagem() {
            if (!this.mensagemInput.trim() || !this.licitacaoSelecionada || this.digitando) return;
            
            const pergunta = this.mensagemInput.trim();
            this.mensagemInput = '';
            
            // Adicionar mensagem do usuário
            this.mensagens.push({
                tipo: 'user',
                texto: pergunta,
                hora: new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})
            });
            
            // Scroll para baixo
            this.$nextTick(() => {
                document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
            });
            
            // Mostrar indicador de digitação
            this.digitando = true;
            
            try {
                const response = await fetch('<?php echo base_url("admin/chat_ia"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        licitacao_id: this.licitacaoSelecionada.id,
                        pergunta: pergunta,
                        contexto: this.mensagens.slice(-10) // Últimas 10 mensagens como contexto
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.adicionarMensagemIA(result.resposta);
                } else {
                    this.adicionarMensagemIA('Desculpe, ocorreu um erro ao processar sua pergunta. Por favor, tente novamente.');
                }
            } catch (error) {
                console.error('Erro:', error);
                this.adicionarMensagemIA('Desculpe, ocorreu um erro de conexão. Por favor, verifique sua internet e tente novamente.');
            } finally {
                this.digitando = false;
            }
        },
        
        enviarSugestao(sugestao) {
            if (!this.licitacaoSelecionada) {
                this.mostrarNotificacao('Por favor, selecione uma licitação primeiro.', 'error');
                return;
            }
            this.mensagemInput = sugestao;
            this.enviarMensagem();
        },
        
        adicionarMensagemIA(texto) {
            this.mensagens.push({
                tipo: 'ia',
                texto: texto,
                hora: new Date().toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'})
            });
            
            this.$nextTick(() => {
                document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
            });
        },
        
        formatarMensagem(texto) {
            if (!texto) return '';
            // Formatar markdown básico
            return texto
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/`(.*?)`/g, '<code class="bg-slate-600 px-1 rounded">$1</code>')
                .replace(/\n/g, '<br>');
        },
        
        limparChat() {
            if (this.mensagens.length > 0 && confirm('Deseja limpar todo o histórico da conversa?')) {
                this.mensagens = [];
            }
        },
        
        async analisarLicitacao(id) {
            this.processandoId = id;
            
            try {
                const response = await fetch('<?php echo base_url("admin/analisar_licitacao/"); ?>' + id, {
                    method: 'POST'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.mostrarNotificacao('✅ ' + result.message, 'success');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    this.mostrarNotificacao('❌ ' + result.message, 'error');
                }
            } catch (error) {
                this.mostrarNotificacao('❌ Erro ao processar: ' + error.message, 'error');
            } finally {
                this.processandoId = null;
            }
        },
        
        async verAnalise(id) {
            try {
                const response = await fetch('<?php echo base_url("admin/api/analise_detalhes/"); ?>' + id);
                const result = await response.json();
                
                if (result.success) {
                    // Processar arrays JSON
                    if (result.data.palavras_chave) {
                        try {
                            result.data.palavras_chave_array = JSON.parse(result.data.palavras_chave);
                        } catch(e) {
                            result.data.palavras_chave_array = [];
                        }
                    }
                    
                    if (result.data.categorias_identificadas) {
                        try {
                            result.data.categorias_array = JSON.parse(result.data.categorias_identificadas);
                        } catch(e) {
                            result.data.categorias_array = [];
                        }
                    }
                    
                    this.analiseAtual = result.data;
                    this.modalAnaliseAberto = true;
                } else {
                    this.mostrarNotificacao('❌ Erro ao carregar análise', 'error');
                }
            } catch (error) {
                this.mostrarNotificacao('❌ Erro: ' + error.message, 'error');
            }
        },
        
        async processarLote() {
            if (!confirm('Deseja processar todas as licitações pendentes? Isso pode levar alguns minutos.')) {
                return;
            }

            this.processando = true;
            
            try {
                const response = await fetch('<?php echo base_url("admin/processar_lote_ia"); ?>', {
                    method: 'POST'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    this.mostrarNotificacao('✅ ' + result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.mostrarNotificacao('❌ ' + result.message, 'error');
                }
            } catch (error) {
                this.mostrarNotificacao('❌ Erro: ' + error.message, 'error');
            } finally {
                this.processando = false;
            }
        },
        
        mostrarNotificacao(mensagem, tipo = 'info') {
            // Criar elemento de notificação
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-[100] px-6 py-4 rounded-2xl shadow-2xl transform translate-x-full transition-all duration-500 ${
                tipo === 'success' ? 'bg-green-500 text-white' : 
                tipo === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `<span class="font-medium">${mensagem}</span>`;
            document.body.appendChild(notification);
            
            // Animar entrada
            setTimeout(() => notification.classList.remove('translate-x-full'), 10);
            
            // Remover após 3 segundos
            setTimeout(() => {
                notification.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => notification.remove(), 500);
            }, 3000);
        }
    }
}
</script>

<style>
[x-cloak] { display: none !important; }

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Animação suave para scroll */
#chatMessages {
    scroll-behavior: smooth;
}
</style>
