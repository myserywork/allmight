<div x-data="{ 
    showFilters: <?php echo (!empty($filters['search']) || !empty($filters['modalidade']) || !empty($filters['valor_min'])) ? 'true' : 'false'; ?>, 
    viewMode: localStorage.getItem('licitacoesViewMode') || 'table',
    showStats: true
}" x-cloak class="min-h-screen">

<style>
    [x-cloak] { display: none !important; }
    .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
    .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .glass { background: rgba(31, 41, 55, 0.5); backdrop-filter: blur(12px); }
    .countdown-urgent { animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    @keyframes pulse { 0%, 100% { opacity: 1; } 50% { opacity: .7; } }
</style>

<!-- Header Fixo com Ações -->
<header class="sticky top-0 z-50 bg-gray-900/95 backdrop-blur-lg border-b border-gray-800 shadow-xl">
    <div class="container-fluid px-6 py-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Título e Info -->
            <div class="flex items-center gap-4">
                <div class="p-3 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg">
                    <i class="fas fa-file-contract text-2xl text-white"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center gap-3">
                        Licitações
                        <?php 
                        $active_count = count(array_filter($filters, function($v, $k) { 
                            return $v !== '' && $v !== null && $k !== 'order_by' && $k !== 'per_page'; 
                        }, ARRAY_FILTER_USE_BOTH));
                        if ($active_count > 0): ?>
                        <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-sm rounded-lg font-normal">
                            <i class="fas fa-filter mr-1"></i>Filtrado
                        </span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-400 text-sm mt-0.5">
                        <span class="font-semibold text-white"><?php echo number_format($total); ?></span> licitações encontradas
                        <?php if ($stats['abertas'] > 0): ?>
                        • <span class="text-green-400 font-semibold"><?php echo $stats['abertas']; ?> abertas</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <!-- Ações do Header -->
            <div class="flex flex-wrap items-center gap-2">
                <!-- Quick Filters -->
                <div class="hidden lg:flex items-center gap-1 bg-gray-800/70 rounded-xl p-1 border border-gray-700">
                    <a href="<?php echo base_url('admin/licitacoes'); ?>" 
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all <?php echo empty($filters['status']) ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        Todas
                    </a>
                    <a href="<?php echo base_url('admin/licitacoes?status=ABERTA'); ?>" 
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all <?php echo ($filters['status'] ?? '') === 'ABERTA' ? 'bg-green-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <i class="fas fa-door-open mr-1"></i>Abertas
                    </a>
                    <a href="<?php echo base_url('admin/licitacoes?order_by=valor_desc'); ?>" 
                       class="px-3 py-1.5 rounded-lg text-sm font-medium transition-all <?php echo ($filters['order_by'] ?? '') === 'valor_desc' ? 'bg-blue-600 text-white shadow-lg' : 'text-gray-400 hover:text-white hover:bg-gray-700'; ?>">
                        <i class="fas fa-dollar-sign mr-1"></i>Maior Valor
                    </a>
                </div>
                
                <!-- Toggle Estatísticas -->
                <button @click="showStats = !showStats" 
                        class="p-2.5 rounded-xl transition-all border"
                        :class="showStats ? 'bg-purple-600 border-purple-500 text-white' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white'">
                    <i class="fas fa-chart-pie"></i>
                </button>
                
                <!-- Toggle Filtros -->
                <button @click="showFilters = !showFilters" 
                        class="flex items-center gap-2 px-4 py-2.5 rounded-xl transition-all border"
                        :class="showFilters ? 'bg-purple-600 border-purple-500 text-white' : 'bg-gray-800 border-gray-700 text-gray-400 hover:text-white'">
                    <i class="fas fa-sliders-h"></i>
                    <span class="hidden sm:inline">Filtros</span>
                    <?php if ($active_count > 0): ?>
                    <span class="px-1.5 py-0.5 bg-white/20 text-white text-xs rounded-full font-bold"><?php echo $active_count; ?></span>
                    <?php endif; ?>
                </button>
                
                <!-- View Toggle -->
                <div class="flex items-center bg-gray-800 rounded-xl p-1 border border-gray-700">
                    <button @click="viewMode = 'table'; localStorage.setItem('licitacoesViewMode', 'table')" 
                            :class="viewMode === 'table' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white'"
                            class="p-2 rounded-lg transition-all">
                        <i class="fas fa-list"></i>
                    </button>
                    <button @click="viewMode = 'cards'; localStorage.setItem('licitacoesViewMode', 'cards')" 
                            :class="viewMode === 'cards' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white'"
                            class="p-2 rounded-lg transition-all">
                        <i class="fas fa-th-large"></i>
                    </button>
                    <button @click="viewMode = 'timeline'; localStorage.setItem('licitacoesViewMode', 'timeline')" 
                            :class="viewMode === 'timeline' ? 'bg-gray-700 text-white shadow' : 'text-gray-400 hover:text-white'"
                            class="p-2 rounded-lg transition-all">
                        <i class="fas fa-stream"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="container-fluid px-6 py-6 space-y-6">

    <!-- Dashboard de Estatísticas -->
    <div x-show="showStats" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0">
        <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-6">
            <!-- Total -->
            <a href="<?php echo base_url('admin/licitacoes'); ?>" 
               class="glass rounded-2xl p-5 border border-gray-700 hover:border-purple-500/50 transition-all group cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <div class="p-3 rounded-xl bg-purple-500/20 group-hover:bg-purple-500/30 transition-colors">
                        <i class="fas fa-file-contract text-xl text-purple-400"></i>
                    </div>
                </div>
            </a>
            
            <!-- Abertas -->
            <a href="<?php echo base_url('admin/licitacoes?status=ABERTA'); ?>" 
               class="glass rounded-2xl p-5 border border-gray-700 hover:border-green-500/50 transition-all group cursor-pointer <?php echo ($filters['status'] ?? '') === 'ABERTA' ? 'ring-2 ring-green-500' : ''; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Abertas</p>
                        <p class="text-2xl font-bold text-green-400 mt-1"><?php echo number_format($stats['abertas']); ?></p>
                    </div>
                    <div class="p-3 rounded-xl bg-green-500/20 group-hover:bg-green-500/30 transition-colors">
                        <i class="fas fa-door-open text-xl text-green-400"></i>
                    </div>
                </div>
            </a>
            
            
            <!-- Encerradas -->
            <a href="<?php echo base_url('admin/licitacoes?status=ENCERRADA'); ?>" 
               class="glass rounded-2xl p-5 border border-gray-700 hover:border-red-500/50 transition-all group cursor-pointer <?php echo ($filters['status'] ?? '') === 'ENCERRADA' ? 'ring-2 ring-red-500' : ''; ?>">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Encerradas</p>
                        <p class="text-2xl font-bold text-red-400 mt-1"><?php echo number_format($stats['encerradas']); ?></p>
                    </div>
                    <div class="p-3 rounded-xl bg-red-500/20 group-hover:bg-red-500/30 transition-colors">
                        <i class="fas fa-lock text-xl text-red-400"></i>
                    </div>
                </div>
            </a>
            
            <!-- Últimos 30 dias -->
            <a href="<?php echo base_url('admin/licitacoes?data_inicio=' . date('Y-m-d', strtotime('-30 days'))); ?>" 
               class="glass rounded-2xl p-5 border border-gray-700 hover:border-blue-500/50 transition-all group cursor-pointer">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Últimos 30d</p>
                        <p class="text-2xl font-bold text-blue-400 mt-1"><?php echo number_format($stats['ultimos_30_dias']); ?></p>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-500/20 group-hover:bg-blue-500/30 transition-colors">
                        <i class="fas fa-calendar-week text-xl text-blue-400"></i>
                    </div>
                </div>
            </a>
            
            <!-- Valor Total -->
            <div class="glass rounded-2xl p-5 border border-gray-700 col-span-2">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Valor Estimado Total [Matches]</p>
                        <p class="text-2xl font-bold text-emerald-400 truncate"><?php echo format_currency($stats['valor_total']); ?></p>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-500/20 flex-shrink-0">
                        <i class="fas fa-coins text-xl text-emerald-400"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Painel de Filtros Avançados -->
    <div x-show="showFilters" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="glass rounded-2xl border border-gray-700 overflow-hidden">
        
        <div class="px-6 py-4 bg-gray-800/50 border-b border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-white flex items-center gap-2">
                <i class="fas fa-filter text-purple-400"></i>
                Filtros Avançados
            </h3>
            <?php if ($active_count > 0): ?>
            <a href="<?php echo base_url('admin/licitacoes'); ?>" class="text-sm text-gray-400 hover:text-white flex items-center gap-1">
                <i class="fas fa-times"></i> Limpar filtros
            </a>
            <?php endif; ?>
        </div>
        
        <form method="get" action="<?php echo base_url('admin/licitacoes'); ?>" class="p-6">
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6">
                
                <!-- Busca -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Buscar</label>
                    <div class="relative">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>" 
                               placeholder="Título, edital, órgão, município..."
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 focus:ring-1 focus:ring-purple-500 transition-all">
                        <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    </div>
                </div>
                
                <!-- Ordenação -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Ordenar por</label>
                    <select name="order_by" class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                        <option value="data_desc" <?php echo ($filters['order_by'] ?? '') === 'data_desc' ? 'selected' : ''; ?>>📅 Mais Recentes</option>
                        <option value="data_asc" <?php echo ($filters['order_by'] ?? '') === 'data_asc' ? 'selected' : ''; ?>>📅 Mais Antigas</option>
                        <option value="valor_desc" <?php echo ($filters['order_by'] ?? '') === 'valor_desc' ? 'selected' : ''; ?>>💰 Maior Valor</option>
                        <option value="valor_asc" <?php echo ($filters['order_by'] ?? '') === 'valor_asc' ? 'selected' : ''; ?>>💰 Menor Valor</option>
                        <option value="abertura_desc" <?php echo ($filters['order_by'] ?? '') === 'abertura_desc' ? 'selected' : ''; ?>>🚪 Abertura (Próximas)</option>
                        <option value="titulo_asc" <?php echo ($filters['order_by'] ?? '') === 'titulo_asc' ? 'selected' : ''; ?>>🔤 Título A-Z</option>
                    </select>
                </div>
                
                <!-- Por Página -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Por Página</label>
                    <select name="per_page" class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                        <option value="10" <?php echo ($filters['per_page'] ?? 20) == 10 ? 'selected' : ''; ?>>10 itens</option>
                        <option value="20" <?php echo ($filters['per_page'] ?? 20) == 20 ? 'selected' : ''; ?>>20 itens</option>
                        <option value="50" <?php echo ($filters['per_page'] ?? 20) == 50 ? 'selected' : ''; ?>>50 itens</option>
                        <option value="100" <?php echo ($filters['per_page'] ?? 20) == 100 ? 'selected' : ''; ?>>100 itens</option>
                    </select>
                </div>
                
                <!-- UF -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Estado (UF)</label>
                    <select name="uf" class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                        <option value="">Todos os Estados</option>
                        <?php if (!empty($ufs)): foreach ($ufs as $uf_item): ?>
                        <option value="<?php echo $uf_item->uf; ?>" <?php echo ($filters['uf'] ?? '') === $uf_item->uf ? 'selected' : ''; ?>>
                            <?php echo $uf_item->uf; ?>
                        </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                
                <!-- Modalidade -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Modalidade</label>
                    <select name="modalidade" class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                        <option value="">Todas</option>
                        <?php if (!empty($modalidades)): foreach ($modalidades as $mod): ?>
                        <option value="<?php echo $mod->modalidade; ?>" <?php echo ($filters['modalidade'] ?? '') === $mod->modalidade ? 'selected' : ''; ?>>
                            <?php echo truncate_text($mod->modalidade, 25); ?>
                        </option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                
                <!-- Status -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Status</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                        <option value="">Todos</option>
                        <option value="ABERTA" <?php echo ($filters['status'] ?? '') === 'ABERTA' ? 'selected' : ''; ?>>🟢 Aberta</option>
                        <option value="EM_ANDAMENTO" <?php echo ($filters['status'] ?? '') === 'EM_ANDAMENTO' ? 'selected' : ''; ?>>🟡 Em Andamento</option>
                        <option value="ENCERRADA" <?php echo ($filters['status'] ?? '') === 'ENCERRADA' ? 'selected' : ''; ?>>🔴 Encerrada</option>
                        <option value="CANCELADA" <?php echo ($filters['status'] ?? '') === 'CANCELADA' ? 'selected' : ''; ?>>⚫ Cancelada</option>
                    </select>
                </div>
                
                <!-- Valor Mínimo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Valor Mínimo</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">R$</span>
                        <input type="number" name="valor_min" value="<?php echo $filters['valor_min'] ?? ''; ?>" 
                               placeholder="0"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition-all">
                    </div>
                </div>
                
                <!-- Valor Máximo -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Valor Máximo</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">R$</span>
                        <input type="number" name="valor_max" value="<?php echo $filters['valor_max'] ?? ''; ?>" 
                               placeholder="Sem limite"
                               class="w-full pl-10 pr-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-purple-500 transition-all">
                    </div>
                </div>
                
                <!-- Data Início -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Publicação De</label>
                    <input type="date" name="data_inicio" value="<?php echo $filters['data_inicio'] ?? ''; ?>" 
                           class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                </div>
                
                <!-- Data Fim -->
                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Publicação Até</label>
                    <input type="date" name="data_fim" value="<?php echo $filters['data_fim'] ?? ''; ?>" 
                           class="w-full px-4 py-2.5 bg-gray-800/70 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-purple-500 transition-all">
                </div>
            </div>
            
            <!-- Botões de Ação -->
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-700">
                <a href="<?php echo base_url('admin/licitacoes'); ?>" 
                   class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors">
                    <i class="fas fa-times mr-2"></i>Limpar
                </a>
                <button type="submit" 
                        class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all font-semibold shadow-lg">
                    <i class="fas fa-search mr-2"></i>Buscar Licitações
                </button>
            </div>
        </form>
    </div>

    <!-- VISTA EM TABELA -->
    <div x-show="viewMode === 'table'" class="glass rounded-2xl border border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800/70 border-b border-gray-700">
                    <?php 
                    $current_order = $filters['order_by'] ?? 'data_desc';
                    $build_sort_url = function($field) use ($filters) {
                        $new_filters = $filters;
                        $new_filters['order_by'] = $field;
                        unset($new_filters['page']);
                        return '?' . http_build_query(array_filter($new_filters, function($v) { return $v !== '' && $v !== null; }));
                    };
                    ?>
                    <tr class="text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">
                        <th class="px-5 py-4">
                            <a href="<?php echo $build_sort_url($current_order === 'titulo_asc' ? 'titulo_desc' : 'titulo_asc'); ?>" 
                               class="flex items-center gap-1 hover:text-purple-400 transition-colors">
                                Licitação
                                <?php if (strpos($current_order, 'titulo') !== false): ?>
                                <i class="fas fa-sort-<?php echo $current_order === 'titulo_asc' ? 'up' : 'down'; ?> text-purple-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-5 py-4">Órgão / Local</th>
                        <th class="px-5 py-4">Modalidade</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">
                            <a href="<?php echo $build_sort_url($current_order === 'valor_desc' ? 'valor_asc' : 'valor_desc'); ?>" 
                               class="flex items-center gap-1 hover:text-purple-400 transition-colors">
                                Valor
                                <?php if (strpos($current_order, 'valor') !== false): ?>
                                <i class="fas fa-sort-<?php echo $current_order === 'valor_desc' ? 'down' : 'up'; ?> text-purple-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-5 py-4">
                            <a href="<?php echo $build_sort_url($current_order === 'data_desc' ? 'data_asc' : 'data_desc'); ?>" 
                               class="flex items-center gap-1 hover:text-purple-400 transition-colors">
                                Publicação
                                <?php if (strpos($current_order, 'data') !== false): ?>
                                <i class="fas fa-sort-<?php echo $current_order === 'data_desc' ? 'down' : 'up'; ?> text-purple-400"></i>
                                <?php endif; ?>
                            </a>
                        </th>
                        <th class="px-5 py-4 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    <?php if (empty($licitacoes)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="p-4 rounded-full bg-gray-800 mb-4">
                                        <i class="fas fa-search text-4xl text-gray-600"></i>
                                    </div>
                                    <p class="text-gray-400 text-lg mb-2">Nenhuma licitação encontrada</p>
                                    <p class="text-gray-500 text-sm">Tente ajustar os filtros de busca</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($licitacoes as $lic): ?>
                            <?php
                            // Calcular urgência baseado na data de abertura
                            $urgente = false;
                            $dias_para_abertura = null;
                            if (!empty($lic->data_abertura_proposta) && $lic->status === 'ABERTA') {
                                $data_abertura = strtotime($lic->data_abertura_proposta);
                                $dias_para_abertura = floor(($data_abertura - time()) / 86400);
                                $urgente = $dias_para_abertura >= 0 && $dias_para_abertura <= 5;
                            }
                            ?>
                            <tr class="hover:bg-gray-800/50 transition-colors group <?php echo $urgente ? 'bg-orange-500/5' : ''; ?>">
                                <td class="px-5 py-4">
                                    <div class="flex items-start gap-3">
                                        <?php if ($urgente): ?>
                                        <span class="countdown-urgent flex-shrink-0 mt-1 px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs font-bold rounded-full">
                                            <?php echo $dias_para_abertura; ?>d
                                        </span>
                                        <?php endif; ?>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-white group-hover:text-purple-400 transition-colors line-clamp-2">
                                                <?php echo truncate_text($lic->titulo, 80); ?>
                                            </p>
                                            <div class="flex items-center gap-2 mt-1 text-xs text-gray-500">
                                                <?php if ($lic->numero_edital): ?>
                                                <span class="font-mono bg-gray-800 px-2 py-0.5 rounded"><?php echo $lic->numero_edital; ?></span>
                                                <?php endif; ?>
                                                <?php if (!empty($lic->total_itens)): ?>
                                                <span class="text-blue-400"><i class="fas fa-box mr-1"></i><?php echo $lic->total_itens; ?> itens</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-gray-300 line-clamp-1"><?php echo truncate_text($lic->orgao_nome, 35); ?></p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?php echo $lic->municipio ?? ''; ?><?php echo !empty($lic->municipio) && !empty($lic->uf) ? ' - ' : ''; ?><?php echo $lic->uf ?? ''; ?>
                                    </p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-semibold rounded-lg bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                        <?php echo truncate_text($lic->modalidade ?? 'N/A', 20); ?>
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <?php echo situacao_badge($lic->status); ?>
                                </td>
                                <td class="px-5 py-4">
                                    <?php if (!empty($lic->valor_estimado) && $lic->valor_estimado > 0): ?>
                                        <p class="text-sm font-bold text-emerald-400"><?php echo format_currency($lic->valor_estimado); ?></p>
                                        <?php if ($lic->valor_estimado >= 1000000): ?>
                                        <p class="text-xs text-gray-500 mt-0.5"><?php echo number_format($lic->valor_estimado / 1000000, 2, ',', '.'); ?>M</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="space-y-1">
                                        <p class="text-sm text-gray-300"><?php echo !empty($lic->data_publicacao) ? format_date($lic->data_publicacao) : '-'; ?></p>
                                        <?php if (!empty($lic->data_abertura_proposta)): ?>
                                        <p class="text-xs <?php echo $urgente ? 'text-orange-400 font-semibold' : 'text-gray-500'; ?>">
                                            <i class="far fa-clock mr-1"></i>Abre: <?php echo format_date($lic->data_abertura_proposta); ?>
                                        </p>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="<?php echo base_url('admin/licitacao/' . $lic->id); ?>" 
                                       class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white rounded-xl transition-all text-sm font-medium shadow-lg shadow-purple-500/20">
                                        <i class="fas fa-eye"></i>
                                        <span class="hidden xl:inline">Detalhes</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- VISTA EM CARDS -->
    <div x-show="viewMode === 'cards'" x-cloak class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php if (empty($licitacoes)): ?>
            <div class="col-span-full">
                <div class="glass rounded-2xl border border-gray-700 p-16 text-center">
                    <div class="flex flex-col items-center">
                        <div class="p-4 rounded-full bg-gray-800 mb-4">
                            <i class="fas fa-search text-4xl text-gray-600"></i>
                        </div>
                        <p class="text-gray-400 text-lg mb-2">Nenhuma licitação encontrada</p>
                        <p class="text-gray-500 text-sm">Tente ajustar os filtros de busca</p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($licitacoes as $lic): ?>
                <?php
                $urgente = false;
                $dias_para_abertura = null;
                if (!empty($lic->data_abertura_proposta) && $lic->status === 'ABERTA') {
                    $data_abertura = strtotime($lic->data_abertura_proposta);
                    $dias_para_abertura = floor(($data_abertura - time()) / 86400);
                    $urgente = $dias_para_abertura >= 0 && $dias_para_abertura <= 5;
                }
                ?>
                <a href="<?php echo base_url('admin/licitacao/' . $lic->id); ?>" 
                   class="glass rounded-2xl border border-gray-700 hover:border-purple-500/50 transition-all group overflow-hidden <?php echo $urgente ? 'ring-2 ring-orange-500/50' : ''; ?>">
                    
                    <!-- Header do Card -->
                    <div class="px-5 py-3 bg-gray-800/50 border-b border-gray-700 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <?php echo situacao_badge($lic->status); ?>
                            <?php if ($urgente): ?>
                            <span class="countdown-urgent px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs font-bold rounded-full">
                                <i class="fas fa-fire mr-1"></i><?php echo $dias_para_abertura; ?> dias
                            </span>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($lic->uf)): ?>
                        <span class="px-2 py-0.5 bg-gray-700 text-gray-300 text-xs font-bold rounded"><?php echo $lic->uf; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Conteúdo -->
                    <div class="p-5 space-y-4">
                        <!-- Título -->
                        <h3 class="text-white font-semibold group-hover:text-purple-400 transition-colors line-clamp-2 leading-tight">
                            <?php echo truncate_text($lic->titulo, 100); ?>
                        </h3>
                        
                        <!-- Info -->
                        <div class="space-y-2">
                            <p class="text-sm text-gray-400 flex items-start gap-2">
                                <i class="fas fa-building mt-0.5 text-gray-500 w-4"></i>
                                <span class="line-clamp-1"><?php echo truncate_text($lic->orgao_nome, 50); ?></span>
                            </p>
                            <?php if (!empty($lic->numero_edital)): ?>
                            <p class="text-xs text-gray-500 flex items-center gap-2">
                                <i class="fas fa-file-alt w-4 text-gray-600"></i>
                                <span class="font-mono"><?php echo $lic->numero_edital; ?></span>
                            </p>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Valor e Modalidade -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-700/50">
                            <div>
                                <?php if (!empty($lic->valor_estimado) && $lic->valor_estimado > 0): ?>
                                    <p class="text-emerald-400 font-bold"><?php echo format_currency($lic->valor_estimado); ?></p>
                                <?php else: ?>
                                    <p class="text-gray-500 text-sm">Valor não informado</p>
                                <?php endif; ?>
                            </div>
                            <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded-lg">
                                <?php echo truncate_text($lic->modalidade ?? 'N/A', 15); ?>
                            </span>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="px-5 py-3 bg-gray-800/30 border-t border-gray-700/50 flex items-center justify-between text-xs text-gray-500">
                        <span><i class="far fa-calendar mr-1"></i><?php echo !empty($lic->data_publicacao) ? format_date($lic->data_publicacao) : '-'; ?></span>
                        <span class="text-purple-400 group-hover:text-purple-300 font-semibold">
                            Ver detalhes <i class="fas fa-arrow-right ml-1"></i>
                        </span>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- VISTA TIMELINE -->
    <div x-show="viewMode === 'timeline'" x-cloak>
        <?php if (empty($licitacoes)): ?>
            <div class="glass rounded-2xl border border-gray-700 p-16 text-center">
                <div class="flex flex-col items-center">
                    <div class="p-4 rounded-full bg-gray-800 mb-4">
                        <i class="fas fa-search text-4xl text-gray-600"></i>
                    </div>
                    <p class="text-gray-400 text-lg mb-2">Nenhuma licitação encontrada</p>
                    <p class="text-gray-500 text-sm">Tente ajustar os filtros de busca</p>
                </div>
            </div>
        <?php else: ?>
            <div class="relative">
                <!-- Linha vertical da timeline -->
                <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gradient-to-b from-purple-500 via-pink-500 to-purple-500"></div>
                
                <div class="space-y-4">
                    <?php foreach ($licitacoes as $index => $lic): ?>
                        <?php
                        $urgente = false;
                        $dias_para_abertura = null;
                        if (!empty($lic->data_abertura_proposta) && $lic->status === 'ABERTA') {
                            $data_abertura = strtotime($lic->data_abertura_proposta);
                            $dias_para_abertura = floor(($data_abertura - time()) / 86400);
                            $urgente = $dias_para_abertura >= 0 && $dias_para_abertura <= 5;
                        }
                        $status_colors = [
                            'ABERTA' => 'green',
                            'EM_ANDAMENTO' => 'yellow',
                            'ENCERRADA' => 'red',
                            'CANCELADA' => 'gray'
                        ];
                        $cor = $status_colors[$lic->status] ?? 'purple';
                        ?>
                        <div class="relative flex items-start gap-6 pl-4">
                            <!-- Ponto da timeline -->
                            <div class="absolute left-6 w-4 h-4 rounded-full bg-<?php echo $cor; ?>-500 border-4 border-gray-900 z-10 <?php echo $urgente ? 'countdown-urgent' : ''; ?>"></div>
                            
                            <!-- Card -->
                            <a href="<?php echo base_url('admin/licitacao/' . $lic->id); ?>" 
                               class="ml-12 flex-1 glass rounded-2xl border border-gray-700 hover:border-<?php echo $cor; ?>-500/50 transition-all group overflow-hidden">
                                <div class="p-5">
                                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-3 mb-2">
                                                <?php echo situacao_badge($lic->status); ?>
                                                <span class="text-xs text-gray-500">
                                                    <?php echo !empty($lic->data_publicacao) ? format_date($lic->data_publicacao) : '-'; ?>
                                                </span>
                                                <?php if ($urgente): ?>
                                                <span class="countdown-urgent px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs font-bold rounded-full">
                                                    <i class="fas fa-fire mr-1"></i>Urgente
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <h3 class="text-white font-semibold group-hover:text-purple-400 transition-colors line-clamp-2 mb-2">
                                                <?php echo truncate_text($lic->titulo, 120); ?>
                                            </h3>
                                            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-400">
                                                <span class="flex items-center gap-1">
                                                    <i class="fas fa-building text-gray-500"></i>
                                                    <?php echo truncate_text($lic->orgao_nome, 40); ?>
                                                </span>
                                                <?php if (!empty($lic->uf)): ?>
                                                <span class="flex items-center gap-1">
                                                    <i class="fas fa-map-marker-alt text-gray-500"></i>
                                                    <?php echo !empty($lic->municipio) ? $lic->municipio . ' - ' : ''; ?><?php echo $lic->uf; ?>
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <?php if (!empty($lic->valor_estimado) && $lic->valor_estimado > 0): ?>
                                                <p class="text-lg font-bold text-emerald-400"><?php echo format_currency($lic->valor_estimado); ?></p>
                                            <?php endif; ?>
                                            <span class="px-3 py-1 bg-blue-500/20 text-blue-400 text-xs rounded-lg">
                                                <?php echo truncate_text($lic->modalidade ?? 'N/A', 20); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginação Melhorada -->
    <?php if ($total > $per_page): ?>
        <div class="glass rounded-2xl border border-gray-700 px-6 py-4">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-sm text-gray-400">
                    Mostrando <span class="font-semibold text-white"><?php echo (($page - 1) * $per_page) + 1; ?></span> - 
                    <span class="font-semibold text-white"><?php echo min($page * $per_page, $total); ?></span> de 
                    <span class="font-semibold text-white"><?php echo number_format($total); ?></span> licitações
                </p>
                
                <div class="flex items-center gap-1">
                    <?php 
                    $query_string = http_build_query(array_filter($filters, function($v) { return $v !== '' && $v !== null; }));
                    ?>
                    
                    <!-- Primeira Página -->
                    <?php if ($page > 2): ?>
                    <a href="?page=1<?php echo $query_string ? '&' . $query_string : ''; ?>" 
                       class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors" title="Primeira página">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Anterior -->
                    <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>" 
                       class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Números das páginas -->
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?page=<?php echo $i; ?><?php echo $query_string ? '&' . $query_string : ''; ?>" 
                       class="px-4 py-2 rounded-lg transition-colors <?php echo $i == $page ? 'bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold shadow-lg' : 'bg-gray-800 border border-gray-700 text-white hover:bg-gray-700'; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php endfor; ?>
                    
                    <!-- Próxima -->
                    <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?><?php echo $query_string ? '&' . $query_string : ''; ?>" 
                       class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                    
                    <!-- Última Página -->
                    <?php if ($page < $total_pages - 1): ?>
                    <a href="?page=<?php echo $total_pages; ?><?php echo $query_string ? '&' . $query_string : ''; ?>" 
                       class="px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700 transition-colors" title="Última página">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>
</div>
