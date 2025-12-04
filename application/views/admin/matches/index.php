<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matches - AllMight Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }
        .line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen">
    
<div x-data="{ showFilters: <?php echo (!empty($filters['search']) || !empty($filters['empresa_id']) || !empty($filters['status']) || !empty($filters['score_min'])) ? 'true' : 'false'; ?>, viewMode: localStorage.getItem('matchesViewMode') || 'table' }" x-cloak>

    <!-- Header com ações rápidas -->
    <header class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-bullseye mr-3 text-pink-400"></i>
                        Matches
                        <?php if (!empty($filters['search']) || !empty($filters['empresa_id']) || !empty($filters['status'])): ?>
                        <span class="ml-3 px-2 py-1 bg-pink-500/20 text-pink-400 text-sm rounded-lg">Filtrado</span>
                        <?php endif; ?>
                    </h1>
                    <p class="text-gray-400 text-sm mt-1">
                        <?php echo number_format($total); ?> matches encontrados
                        <?php if ($stats['novos'] > 0): ?>
                            • <span class="text-yellow-400"><?php echo $stats['novos']; ?> novos</span>
                        <?php endif; ?>
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <!-- Quick Filters -->
                    <div class="hidden md:flex items-center gap-1 bg-gray-800/50 rounded-xl p-1">
                        <a href="<?php echo base_url('admin/matches'); ?>" 
                           class="px-3 py-1.5 rounded-lg text-sm transition-colors <?php echo empty($filters['status']) && empty($filters['visualizado']) && empty($filters['score_min']) ? 'bg-pink-600 text-white' : 'text-gray-400 hover:text-white'; ?>">
                            Todos
                        </a>
                        <a href="<?php echo base_url('admin/matches?visualizado=0'); ?>" 
                           class="px-3 py-1.5 rounded-lg text-sm transition-colors <?php echo ($filters['visualizado'] ?? '') === '0' ? 'bg-yellow-600 text-white' : 'text-gray-400 hover:text-white'; ?>">
                            <i class="fas fa-star mr-1"></i>Novos
                        </a>
                        <a href="<?php echo base_url('admin/matches?status=INTERESSADO'); ?>" 
                           class="px-3 py-1.5 rounded-lg text-sm transition-colors <?php echo ($filters['status'] ?? '') === 'INTERESSADO' ? 'bg-green-600 text-white' : 'text-gray-400 hover:text-white'; ?>">
                            <i class="fas fa-heart mr-1"></i>Interessados
                        </a>
                        <a href="<?php echo base_url('admin/matches?score_min=80'); ?>" 
                           class="px-3 py-1.5 rounded-lg text-sm transition-colors <?php echo ($filters['score_min'] ?? '') == '80' ? 'bg-green-600 text-white' : 'text-gray-400 hover:text-white'; ?>">
                            <i class="fas fa-fire mr-1"></i>Top
                        </a>
                    </div>
                    
                    <!-- Toggle Filters -->
                    <button @click="showFilters = !showFilters" 
                            class="flex items-center gap-2 px-4 py-2 bg-gray-800 hover:bg-gray-700 text-white rounded-xl transition-colors border border-gray-700">
                        <i class="fas fa-sliders-h"></i>
                        <span class="hidden sm:inline">Filtros</span>
                        <?php 
                        $active_filters = count(array_filter($filters, function($v) { return $v !== '' && $v !== null; }));
                        if ($active_filters > 1): ?>
                        <span class="px-1.5 py-0.5 bg-pink-500 text-white text-xs rounded-full"><?php echo $active_filters - 1; ?></span>
                        <?php endif; ?>
                    </button>
                    
                    <!-- View Toggle -->
                    <div class="flex items-center bg-gray-800 rounded-xl p-1 border border-gray-700">
                        <button @click="viewMode = 'table'; localStorage.setItem('matchesViewMode', 'table')" 
                                :class="viewMode === 'table' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white'"
                                class="p-2 rounded-lg transition-colors">
                            <i class="fas fa-list"></i>
                        </button>
                        <button @click="viewMode = 'cards'; localStorage.setItem('matchesViewMode', 'cards')" 
                                :class="viewMode === 'cards' ? 'bg-gray-700 text-white' : 'text-gray-400 hover:text-white'"
                                class="p-2 rounded-lg transition-colors">
                            <i class="fas fa-th-large"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Cards de Estatísticas Compactos -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <a href="<?php echo base_url('admin/matches'); ?>" class="bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/30 rounded-xl p-4 hover:border-blue-400/50 transition-colors cursor-pointer block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-400 text-xs font-semibold uppercase tracking-wider">Total</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <i class="fas fa-link text-blue-400/50 text-2xl"></i>
                </div>
            </a>

            <a href="<?php echo base_url('admin/matches?visualizado=0'); ?>" class="bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 border border-yellow-500/30 rounded-xl p-4 hover:border-yellow-400/50 transition-colors cursor-pointer block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-400 text-xs font-semibold uppercase tracking-wider">Novos</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['novos']); ?></p>
                    </div>
                    <i class="fas fa-star text-yellow-400/50 text-2xl"></i>
                </div>
            </a>

            <a href="<?php echo base_url('admin/matches?status=INTERESSADO'); ?>" class="bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/30 rounded-xl p-4 hover:border-green-400/50 transition-colors cursor-pointer block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-400 text-xs font-semibold uppercase tracking-wider">Interessados</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['interessados']); ?></p>
                    </div>
                    <i class="fas fa-check-circle text-green-400/50 text-2xl"></i>
                </div>
            </a>


            <a href="<?php echo base_url('admin/matches?status=PROPOSTA_ENVIADA'); ?>" class="bg-gradient-to-br from-purple-500/10 to-purple-600/10 border border-purple-500/30 rounded-xl p-4 hover:border-purple-400/50 transition-colors cursor-pointer block">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-400 text-xs font-semibold uppercase tracking-wider">Propostas</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['propostas']); ?></p>
                    </div>
                    <i class="fas fa-file-invoice text-purple-400/50 text-2xl"></i>
                </div>
            </a>

            <div class="bg-gradient-to-br from-pink-500/10 to-pink-600/10 border border-pink-500/30 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-pink-400 text-xs font-semibold uppercase tracking-wider">Score Médio</p>
                        <p class="text-2xl font-bold text-white mt-1"><?php echo number_format($stats['score_medio'], 1); ?>%</p>
                    </div>
                    <i class="fas fa-chart-line text-pink-400/50 text-2xl"></i>
                </div>
            </div>
        </div>

        <!-- Painel de Filtros Expandível -->
        <div x-show="showFilters" x-transition class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <form method="get" action="<?php echo base_url('admin/matches'); ?>">
                <!-- Linha 1: Busca e Ordenação -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Buscar</label>
                        <div class="relative">
                            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                            <input type="text" name="search" 
                                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>"
                                   placeholder="Licitação, empresa, órgão..."
                                   class="w-full pl-10 pr-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-pink-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Ordenar por</label>
                        <select name="order_by" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="score_desc" <?php echo ($filters['order_by'] ?? '') === 'score_desc' ? 'selected' : ''; ?>>Maior Score</option>
                            <option value="score_asc" <?php echo ($filters['order_by'] ?? '') === 'score_asc' ? 'selected' : ''; ?>>Menor Score</option>
                            <option value="valor_desc" <?php echo ($filters['order_by'] ?? '') === 'valor_desc' ? 'selected' : ''; ?>>Maior Valor</option>
                            <option value="valor_asc" <?php echo ($filters['order_by'] ?? '') === 'valor_asc' ? 'selected' : ''; ?>>Menor Valor</option>
                            <option value="data_desc" <?php echo ($filters['order_by'] ?? '') === 'data_desc' ? 'selected' : ''; ?>>Mais Recente</option>
                            <option value="data_asc" <?php echo ($filters['order_by'] ?? '') === 'data_asc' ? 'selected' : ''; ?>>Mais Antigo</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Itens/página</label>
                        <select name="per_page" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="10" <?php echo $per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="20" <?php echo $per_page == 20 ? 'selected' : ''; ?>>20</option>
                            <option value="50" <?php echo $per_page == 50 ? 'selected' : ''; ?>>50</option>
                            <option value="100" <?php echo $per_page == 100 ? 'selected' : ''; ?>>100</option>
                        </select>
                    </div>
                </div>

                <!-- Linha 2: Filtros Principais -->
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Empresa</label>
                        <select name="empresa_id" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="">Todas</option>
                            <?php foreach ($empresas as $emp): ?>
                            <option value="<?php echo $emp->id; ?>" <?php echo ($filters['empresa_id'] ?? '') == $emp->id ? 'selected' : ''; ?>>
                                <?php echo truncate_text($emp->nome, 25); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="">Todos</option>
                            <option value="NOVO" <?php echo ($filters['status'] ?? '') === 'NOVO' ? 'selected' : ''; ?>>🆕 Novo</option>
                            <option value="ANALISADO" <?php echo ($filters['status'] ?? '') === 'ANALISADO' ? 'selected' : ''; ?>>👁️ Analisado</option>
                            <option value="INTERESSADO" <?php echo ($filters['status'] ?? '') === 'INTERESSADO' ? 'selected' : ''; ?>>💚 Interessado</option>
                            <option value="NAO_INTERESSADO" <?php echo ($filters['status'] ?? '') === 'NAO_INTERESSADO' ? 'selected' : ''; ?>>❌ Não Interessado</option>
                            <option value="PROPOSTA_ENVIADA" <?php echo ($filters['status'] ?? '') === 'PROPOSTA_ENVIADA' ? 'selected' : ''; ?>>📄 Proposta</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Score Mínimo</label>
                        <select name="score_min" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="">Qualquer</option>
                            <option value="90" <?php echo ($filters['score_min'] ?? '') == '90' ? 'selected' : ''; ?>>🔥 90%+</option>
                            <option value="80" <?php echo ($filters['score_min'] ?? '') == '80' ? 'selected' : ''; ?>>⭐ 80%+</option>
                            <option value="70" <?php echo ($filters['score_min'] ?? '') == '70' ? 'selected' : ''; ?>>✅ 70%+</option>
                            <option value="60" <?php echo ($filters['score_min'] ?? '') == '60' ? 'selected' : ''; ?>>👍 60%+</option>
                            <option value="50" <?php echo ($filters['score_min'] ?? '') == '50' ? 'selected' : ''; ?>>📊 50%+</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">UF</label>
                        <select name="uf" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="">Todas</option>
                            <?php if (!empty($ufs)): foreach ($ufs as $uf): ?>
                            <option value="<?php echo $uf->uf; ?>" <?php echo ($filters['uf'] ?? '') === $uf->uf ? 'selected' : ''; ?>><?php echo $uf->uf; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Modalidade</label>
                        <select name="modalidade" class="w-full px-4 py-2.5 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-pink-500">
                            <option value="">Todas</option>
                            <?php if (!empty($modalidades)): foreach ($modalidades as $mod): ?>
                            <option value="<?php echo $mod->modalidade; ?>" <?php echo ($filters['modalidade'] ?? '') === $mod->modalidade ? 'selected' : ''; ?>><?php echo $mod->modalidade; ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-700">
                    <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                        <input type="checkbox" name="visualizado" value="0" 
                               <?php echo ($filters['visualizado'] ?? '') === '0' ? 'checked' : ''; ?>
                               class="w-4 h-4 rounded bg-gray-700 border-gray-600 text-pink-500 focus:ring-pink-500">
                        Apenas não visualizados
                    </label>
                    <div class="flex gap-2">
                        <a href="<?php echo base_url('admin/matches'); ?>" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors">
                            <i class="fas fa-times mr-2"></i>Limpar
                        </a>
                        <button type="submit" class="px-6 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-xl transition-colors font-semibold">
                            <i class="fas fa-filter mr-2"></i>Aplicar
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Vista em Tabela -->
        <div x-show="viewMode === 'table'" class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-900/50 border-b border-gray-700">
                        <tr class="text-left text-gray-400 text-xs uppercase tracking-wider">
                            <?php 
                            // Helper para construir URL de ordenação
                            $current_order = $filters['order_by'] ?? 'score_desc';
                            $build_sort_url = function($field) use ($filters) {
                                $new_filters = $filters;
                                $new_filters['order_by'] = $field;
                                unset($new_filters['page']); // Reset page on sort
                                return '?' . http_build_query(array_filter($new_filters, function($v) { return $v !== '' && $v !== null; }));
                            };
                            ?>
                            <th class="px-4 py-3 font-semibold">Licitação</th>
                            <th class="px-4 py-3 font-semibold">
                                <a href="<?php echo $build_sort_url('empresa'); ?>" 
                                   class="flex items-center gap-1 hover:text-pink-400 transition-colors <?php echo $current_order === 'empresa' ? 'text-pink-400' : ''; ?>">
                                    Empresa
                                    <i class="fas fa-sort<?php echo $current_order === 'empresa' ? '-up text-pink-400' : ' text-gray-600'; ?>"></i>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold">
                                <a href="<?php echo $build_sort_url($current_order === 'valor_desc' ? 'valor_asc' : 'valor_desc'); ?>" 
                                   class="flex items-center gap-1 hover:text-pink-400 transition-colors <?php echo strpos($current_order, 'valor') !== false ? 'text-pink-400' : ''; ?>">
                                    Valor
                                    <?php if ($current_order === 'valor_desc'): ?>
                                        <i class="fas fa-sort-down text-pink-400"></i>
                                    <?php elseif ($current_order === 'valor_asc'): ?>
                                        <i class="fas fa-sort-up text-pink-400"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-gray-600"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold">
                                <a href="<?php echo $build_sort_url($current_order === 'score_desc' ? 'score_asc' : 'score_desc'); ?>" 
                                   class="flex items-center gap-1 hover:text-pink-400 transition-colors <?php echo strpos($current_order, 'score') !== false ? 'text-pink-400' : ''; ?>">
                                    Score
                                    <?php if ($current_order === 'score_desc'): ?>
                                        <i class="fas fa-sort-down text-pink-400"></i>
                                    <?php elseif ($current_order === 'score_asc'): ?>
                                        <i class="fas fa-sort-up text-pink-400"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-gray-600"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold">Status</th>
                            <th class="px-4 py-3 font-semibold">
                                <a href="<?php echo $build_sort_url($current_order === 'data_desc' ? 'data_asc' : 'data_desc'); ?>" 
                                   class="flex items-center gap-1 hover:text-pink-400 transition-colors <?php echo strpos($current_order, 'data') !== false ? 'text-pink-400' : ''; ?>">
                                    Data
                                    <?php if ($current_order === 'data_desc'): ?>
                                        <i class="fas fa-sort-down text-pink-400"></i>
                                    <?php elseif ($current_order === 'data_asc'): ?>
                                        <i class="fas fa-sort-up text-pink-400"></i>
                                    <?php else: ?>
                                        <i class="fas fa-sort text-gray-600"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th class="px-4 py-3 font-semibold text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                    <?php if (!empty($matches)): ?>
                        <?php foreach ($matches as $match): ?>
                            <tr class="hover:bg-gray-700/30 transition-colors <?php echo !$match->visualizado ? 'bg-yellow-500/5' : ''; ?>">
                                <td class="px-6 py-4">
                                    <p class="text-white font-semibold"><?php echo truncate_text($match->licitacao_titulo, 50); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <i class="fas fa-building mr-1"></i><?php echo truncate_text($match->orgao_nome, 35); ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i><?php echo $match->licitacao_municipio; ?> - <?php echo $match->licitacao_uf; ?>
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-white font-semibold"><?php echo truncate_text($match->empresa_nome, 30); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs"><?php echo $match->empresa_porte; ?></span>
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($match->valor_estimado) && $match->valor_estimado > 0): ?>
                                        <p class="text-green-400 font-bold">R$ <?php echo number_format($match->valor_estimado, 2, ',', '.'); ?></p>
                                        <?php if ($match->valor_estimado >= 1000000): ?>
                                            <p class="text-xs text-gray-500 mt-1"><?php echo number_format($match->valor_estimado / 1000000, 2, ',', '.'); ?> milhões</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $score = $match->score_total;
                                    $cor = $score >= 80 ? 'green' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
                                    ?>
                                    <div class="flex items-center">
                                        <div class="w-20 h-2 bg-gray-700 rounded-full overflow-hidden mr-3">
                                            <div class="h-full bg-<?php echo $cor; ?>-500" style="width: <?php echo $score; ?>%"></div>
                                        </div>
                                        <span class="text-<?php echo $cor; ?>-400 font-bold"><?php echo number_format($score, 1); ?>%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_colors = [
                                        'NOVO' => 'yellow',
                                        'ANALISADO' => 'blue',
                                        'INTERESSADO' => 'green',
                                        'NAO_INTERESSADO' => 'red',
                                        'PROPOSTA_ENVIADA' => 'purple'
                                    ];
                                    $cor = $status_colors[$match->status] ?? 'gray';
                                    $status_texto = str_replace('_', ' ', $match->status);
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-<?php echo $cor; ?>-500/20 text-<?php echo $cor; ?>-400 border border-<?php echo $cor; ?>-500/30">
                                        <?php echo ucfirst(strtolower($status_texto)); ?>
                                    </span>
                                    <?php if (!$match->visualizado): ?>
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">
                                        <i class="fas fa-star text-xs"></i>
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-300"><?php echo format_date($match->data_criacao); ?></p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?php echo base_url('admin/match/' . $match->id); ?>" 
                                       class="inline-flex items-center px-3 py-1 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-colors text-sm">
                                        <i class="fas fa-eye mr-2"></i>Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-gray-600 text-4xl mb-4"></i>
                                <p class="text-gray-400 mb-4">Nenhum match encontrado</p>
                                <p class="text-gray-500 text-sm">Use o botão "Gerar Matches" na página de detalhes das licitações</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
                </table>
            </div>
        </div>

        <!-- Vista em Cards -->
        <div x-show="viewMode === 'cards'" x-cloak class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <?php if (!empty($matches)): ?>
                <?php foreach ($matches as $match): ?>
                    <?php
                    $score = $match->score_total;
                    $cor_score = $score >= 80 ? 'green' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
                    $status_colors = [
                        'NOVO' => 'yellow',
                        'ANALISADO' => 'blue', 
                        'INTERESSADO' => 'green',
                        'NAO_INTERESSADO' => 'red',
                        'PROPOSTA_ENVIADA' => 'purple'
                    ];
                    $cor_status = $status_colors[$match->status] ?? 'gray';
                    $status_texto = str_replace('_', ' ', $match->status);
                    ?>
                    <a href="<?php echo base_url('admin/match/' . $match->id); ?>" 
                       class="block bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 hover:border-pink-500/50 transition-all duration-300 overflow-hidden group <?php echo !$match->visualizado ? 'ring-2 ring-yellow-500/30' : ''; ?>">
                        
                        <!-- Header do Card -->
                        <div class="px-4 py-3 bg-gray-900/50 border-b border-gray-700 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-<?php echo $cor_status; ?>-500/20 text-<?php echo $cor_status; ?>-400">
                                    <?php echo ucfirst(strtolower($status_texto)); ?>
                                </span>
                                <?php if (!$match->visualizado): ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-yellow-500/20 text-yellow-400">
                                        <i class="fas fa-star"></i>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-16 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                                    <div class="h-full bg-<?php echo $cor_score; ?>-500" style="width: <?php echo $score; ?>%"></div>
                                </div>
                                <span class="text-<?php echo $cor_score; ?>-400 font-bold text-sm"><?php echo number_format($score, 0); ?>%</span>
                            </div>
                        </div>

                        <!-- Conteúdo do Card -->
                        <div class="p-4 space-y-3">
                            <!-- Título -->
                            <h3 class="text-white font-semibold text-sm leading-tight group-hover:text-pink-400 transition-colors line-clamp-2">
                                <?php echo truncate_text($match->licitacao_titulo, 70); ?>
                            </h3>
                            
                            <!-- Órgão e Local -->
                            <div class="space-y-1">
                                <p class="text-xs text-gray-400 flex items-center gap-1">
                                    <i class="fas fa-building w-4 text-gray-500"></i>
                                    <?php echo truncate_text($match->orgao_nome, 40); ?>
                                </p>
                                <p class="text-xs text-gray-500 flex items-center gap-1">
                                    <i class="fas fa-map-marker-alt w-4 text-gray-600"></i>
                                    <?php echo $match->licitacao_municipio; ?> - <?php echo $match->licitacao_uf; ?>
                                </p>
                            </div>

                            <!-- Valor e Empresa -->
                            <div class="flex items-center justify-between pt-2 border-t border-gray-700/50">
                                <div>
                                    <?php if (!empty($match->valor_estimado) && $match->valor_estimado > 0): ?>
                                        <p class="text-green-400 font-bold text-sm">R$ <?php echo number_format($match->valor_estimado, 2, ',', '.'); ?></p>
                                        <?php if ($match->valor_estimado >= 1000000): ?>
                                            <p class="text-xs text-gray-500"><?php echo number_format($match->valor_estimado / 1000000, 1, ',', '.'); ?>M</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-xs">Valor não informado</span>
                                    <?php endif; ?>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400"><?php echo truncate_text($match->empresa_nome, 20); ?></p>
                                    <span class="inline-flex px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded text-xs"><?php echo $match->empresa_porte; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer do Card -->
                        <div class="px-4 py-2 bg-gray-900/30 border-t border-gray-700/50 flex items-center justify-between text-xs text-gray-500">
                            <span><i class="far fa-calendar mr-1"></i><?php echo format_date($match->data_criacao); ?></span>
                            <span class="text-pink-400 group-hover:text-pink-300 font-semibold">Ver detalhes <i class="fas fa-arrow-right ml-1"></i></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full">
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 p-12 text-center">
                        <i class="fas fa-inbox text-gray-600 text-4xl mb-4"></i>
                        <p class="text-gray-400 mb-2">Nenhum match encontrado</p>
                        <p class="text-gray-500 text-sm">Use o botão "Gerar Matches" na página de detalhes das licitações</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paginação -->
        <?php if ($total > $per_page): ?>
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-400">
                    Mostrando <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total); ?> de <?php echo $total; ?> matches
                </p>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg <?php echo $i === $page ? 'bg-pink-600 text-white' : 'bg-gray-800 border border-gray-700 text-white hover:bg-gray-700'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg bg-gray-800 border border-gray-700 text-white hover:bg-gray-700">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>

</body>
</html>
