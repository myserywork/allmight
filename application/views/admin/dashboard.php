<!-- Stats Cards -->
<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
    <!-- Total Licitações -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Total de Licitações</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo number_format($stats['total_licitacoes']); ?></p>
                <p class="mt-2 text-xs text-green-400">
                    <i class="fas fa-arrow-up"></i> +12% este mês
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-500/20">
                <i class="fas fa-gavel text-2xl text-blue-400"></i>
            </div>
        </div>
    </div>

    <!-- Licitações Abertas -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Abertas</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo number_format($stats['licitacoes_abertas']); ?></p>
                <p class="mt-2 text-xs text-yellow-400">
                    <i class="fas fa-clock"></i> Requer atenção
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-yellow-500/20">
                <i class="fas fa-folder-open text-2xl text-yellow-400"></i>
            </div>
        </div>
    </div>

    <!-- Total Matches -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Matches</p>
                <p class="mt-2 text-3xl font-bold text-white"><?php echo number_format($stats['total_matches']); ?></p>
                <p class="mt-2 text-xs text-green-400">
                    <i class="fas fa-bullseye"></i> Alta aderência
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-500/20">
                <i class="fas fa-bullseye text-2xl text-green-400"></i>
            </div>
        </div>
    </div>

    <!-- Valor Total -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Valor Estimado Total</p>
                <p class="mt-2 text-3xl font-bold text-white">
                    R$ <?php echo number_format($stats['valor_total_estimado'] / 1000000, 1); ?>M
                </p>
                <p class="mt-2 text-xs text-primary-400">
                    <i class="fas fa-chart-line"></i> Volume alto
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-primary-500/20">
                <i class="fas fa-dollar-sign text-2xl text-primary-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content Grid -->
<div class="grid gap-6 lg:grid-cols-3">
    <!-- Left Column - 2/3 -->
    <div class="space-y-6 lg:col-span-2">
        <!-- Licitações Recentes -->
        <div class="glass rounded-xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-clock mr-2 text-primary-400"></i>
                    Licitações Recentes
                </h3>
                <a href="<?php echo base_url('admin/licitacoes'); ?>" class="text-sm text-primary-400 hover:text-primary-300">
                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                </a>
            </div>

            <div class="space-y-4">
                <?php if (!empty($recent_licitacoes)): ?>
                    <?php foreach ($recent_licitacoes as $lic): ?>
                        <div class="rounded-lg border border-dark-700 bg-dark-800/50 p-4 hover:border-primary-500 transition-colors">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <span class="rounded-full bg-primary-500/20 px-2 py-1 text-xs font-medium text-primary-400">
                                            <?php echo $lic->modalidade; ?>
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            <?php echo $lic->numero_edital; ?>
                                        </span>
                                    </div>
                                    <h4 class="font-medium text-white mb-1">
                                        <?php echo character_limiter($lic->titulo, 80); ?>
                                    </h4>
                                    <p class="text-sm text-gray-400">
                                        <i class="fas fa-building mr-1"></i>
                                        <?php echo $lic->orgao_nome; ?>
                                    </p>
                                </div>
                                <div class="ml-4 text-right">
                                    <p class="text-sm font-bold text-white">
                                        R$ <?php echo number_format($lic->valor_estimado, 2, ',', '.'); ?>
                                    </p>
                                    <p class="mt-1 text-xs text-gray-400">
                                        <?php echo date('d/m/Y', strtotime($lic->data_publicacao)); ?>
                                    </p>
                                    <span class="mt-2 inline-block rounded-full bg-green-500/20 px-2 py-1 text-xs font-medium text-green-400">
                                        <?php echo $lic->situacao; ?>
                                    </span>
                                </div>
                            </div>
                            <div class="mt-3 flex space-x-2">
                                <a href="<?php echo base_url('admin/licitacao_detalhes/' . $lic->id); ?>" 
                                   class="text-xs text-primary-400 hover:text-primary-300">
                                    <i class="fas fa-eye mr-1"></i>Ver detalhes
                                </a>
                                <span class="text-gray-600">•</span>
                                <a href="#" class="text-xs text-primary-400 hover:text-primary-300">
                                    <i class="fas fa-robot mr-1"></i>Analisar com IA
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-inbox text-4xl mb-2"></i>
                        <p>Nenhuma licitação encontrada</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Gráfico de Licitações por Mês -->
        <div class="glass rounded-xl p-6">
            <h3 class="mb-6 text-xl font-bold text-white">
                <i class="fas fa-chart-line mr-2 text-primary-400"></i>
                Licitações por Mês
            </h3>
            <canvas id="licitacoesPorMesChart" height="100"></canvas>
        </div>
    </div>

    <!-- Right Column - 1/3 -->
    <div class="space-y-6">
        <!-- Top Matches -->
        <div class="glass rounded-xl p-6">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-bold text-white">
                    <i class="fas fa-star mr-2 text-yellow-400"></i>
                    Top Matches
                </h3>
                <a href="<?php echo base_url('admin/matches'); ?>" class="text-sm text-primary-400 hover:text-primary-300">
                    Ver todos
                </a>
            </div>

            <div class="space-y-4">
                <?php if (!empty($top_matches)): ?>
                    <?php foreach ($top_matches as $match): ?>
                        <div class="rounded-lg border border-dark-700 bg-dark-800/50 p-4">
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-400">
                                    Score: <?php echo number_format($match->score_total, 0); ?>%
                                </span>
                                <div class="flex items-center space-x-1">
                                    <?php 
                                    $stars = round($match->score_total / 20); // score_total é de 0-100, convertendo para 0-5 estrelas
                                    for ($i = 0; $i < 5; $i++): 
                                    ?>
                                        <i class="fas fa-star text-xs <?php echo $i < $stars ? 'text-yellow-400' : 'text-gray-600'; ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <h4 class="text-sm font-medium text-white mb-2">
                                <?php echo character_limiter($match->titulo, 50); ?>
                            </h4>
                            <p class="text-xs text-primary-400 mb-1">
                                <i class="fas fa-building mr-1"></i>
                                <?php echo $match->empresa_nome; ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?php echo $match->numero_edital; ?>
                            </p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">Nenhum match encontrado</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="glass rounded-xl p-6">
            <h3 class="mb-6 text-xl font-bold text-white">
                <i class="fas fa-bolt mr-2 text-primary-400"></i>
                Ações Rápidas
            </h3>
            <div class="space-y-3">
                <button class="w-full rounded-lg bg-primary-600 px-4 py-3 text-left text-sm font-medium text-white hover:bg-primary-700 transition-colors">
                    <i class="fas fa-plus mr-2"></i>
                    Nova Empresa
                </button>
                <button class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-3 text-left text-sm font-medium text-white hover:bg-dark-700 transition-colors">
                    <i class="fas fa-robot mr-2"></i>
                    Análise com IA
                </button>
                <button class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-3 text-left text-sm font-medium text-white hover:bg-dark-700 transition-colors">
                    <i class="fas fa-file-export mr-2"></i>
                    Exportar Relatório
                </button>
                <button class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-3 text-left text-sm font-medium text-white hover:bg-dark-700 transition-colors">
                    <i class="fas fa-sync mr-2"></i>
                    Atualizar Dados
                </button>
            </div>
        </div>

        <!-- Distribuição por UF -->
        <div class="glass rounded-xl p-6">
            <h3 class="mb-6 text-xl font-bold text-white">
                <i class="fas fa-map-marked-alt mr-2 text-primary-400"></i>
                Top UFs
            </h3>
            <canvas id="licitacoesPorUfChart" height="200"></canvas>
        </div>
    </div>
</div>

<!-- Chart.js Scripts -->
<script>
    // Licitações por Mês
    const ctxMes = document.getElementById('licitacoesPorMesChart').getContext('2d');
    new Chart(ctxMes, {
        type: 'line',
        data: {
            labels: [
                <?php foreach ($chart_data['por_mes'] as $item): ?>
                    '<?php echo date('M/Y', strtotime($item->mes . '-01')); ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                label: 'Licitações',
                data: [
                    <?php foreach ($chart_data['por_mes'] as $item): ?>
                        <?php echo $item->total; ?>,
                    <?php endforeach; ?>
                ],
                borderColor: '#0ea5e9',
                backgroundColor: 'rgba(14, 165, 233, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(71, 85, 105, 0.3)'
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: '#94a3b8'
                    }
                }
            }
        }
    });

    // Licitações por UF
    const ctxUf = document.getElementById('licitacoesPorUfChart').getContext('2d');
    new Chart(ctxUf, {
        type: 'doughnut',
        data: {
            labels: [
                <?php foreach ($chart_data['por_uf'] as $item): ?>
                    '<?php echo $item->uf; ?>',
                <?php endforeach; ?>
            ],
            datasets: [{
                data: [
                    <?php foreach ($chart_data['por_uf'] as $item): ?>
                        <?php echo $item->total; ?>,
                    <?php endforeach; ?>
                ],
                backgroundColor: [
                    '#0ea5e9', '#06b6d4', '#14b8a6', '#10b981', '#84cc16',
                    '#eab308', '#f59e0b', '#f97316', '#ef4444', '#ec4899'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: '#94a3b8',
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            }
        }
    });
</script>
