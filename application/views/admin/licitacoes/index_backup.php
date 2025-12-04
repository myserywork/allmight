<div class="container-fluid px-6 py-6">
    <!-- Stats Cards -->
    <div class="grid gap-6 mb-6 md:grid-cols-4">
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Total de Licitações</p>
                    <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['total']); ?></p>
                </div>
                <div class="p-3 rounded-full bg-primary-500/20">
                    <i class="fas fa-file-contract text-2xl text-primary-400"></i>
                </div>
            </div>
        </div>
        
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Abertas</p>
                    <p class="text-3xl font-bold text-green-400 mt-2"><?php echo number_format($stats['abertas']); ?></p>
                </div>
                <div class="p-3 rounded-full bg-green-500/20">
                    <i class="fas fa-door-open text-2xl text-green-400"></i>
                </div>
            </div>
        </div>
        
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Em Andamento</p>
                    <p class="text-3xl font-bold text-yellow-400 mt-2"><?php echo number_format($stats['em_andamento']); ?></p>
                </div>
                <div class="p-3 rounded-full bg-yellow-500/20">
                    <i class="fas fa-hourglass-half text-2xl text-yellow-400"></i>
                </div>
            </div>
        </div>
        
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-400">Valor Total</p>
                    <p class="text-2xl font-bold text-white mt-2"><?php echo format_currency($stats['valor_total']); ?></p>
                </div>
                <div class="p-3 rounded-full bg-blue-500/20">
                    <i class="fas fa-money-bill-wave text-2xl text-blue-400"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="glass rounded-xl p-6 mb-6">
        <form method="get" action="<?php echo base_url('admin/licitacoes'); ?>" class="grid gap-4 md:grid-cols-5">
            <div class="md:col-span-2">
                <input type="text" name="search" value="<?php echo $filters['search']; ?>" 
                       placeholder="Buscar por título, nº edital, órgão..."
                       class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
            </div>
            
            <div>
                <select name="uf" class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                    <option value="">Todos os Estados</option>
                    <?php 
                    $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                    foreach ($ufs as $uf): 
                    ?>
                        <option value="<?php echo $uf; ?>" <?php echo $filters['uf'] == $uf ? 'selected' : ''; ?>><?php echo $uf; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <select name="status" class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                    <option value="">Todos os Status</option>
                    <option value="ABERTA" <?php echo $filters['status'] == 'ABERTA' ? 'selected' : ''; ?>>Aberta</option>
                    <option value="EM_ANDAMENTO" <?php echo $filters['status'] == 'EM_ANDAMENTO' ? 'selected' : ''; ?>>Em Andamento</option>
                    <option value="ENCERRADA" <?php echo $filters['status'] == 'ENCERRADA' ? 'selected' : ''; ?>>Encerrada</option>
                    <option value="CANCELADA" <?php echo $filters['status'] == 'CANCELADA' ? 'selected' : ''; ?>>Cancelada</option>
                </select>
            </div>
            
            <div class="flex space-x-2">
                <button type="submit" class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700 transition-colors">
                    <i class="fas fa-search mr-2"></i>Filtrar
                </button>
                <a href="<?php echo base_url('admin/licitacoes'); ?>" 
                   class="rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white hover:bg-dark-700 transition-colors">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Tabela de Licitações -->
    <div class="glass rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-dark-800 border-b border-dark-700">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Licitação</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Órgão</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Modalidade</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Valor</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Data Abertura</th>
                        <th class="px-6 py-4 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    <?php if (empty($licitacoes)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-5xl text-gray-600 mb-4"></i>
                                <p class="text-gray-400">Nenhuma licitação encontrada</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($licitacoes as $lic): ?>
                            <tr class="hover:bg-dark-800/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-white"><?php echo truncate_text($lic->titulo, 60); ?></p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php if ($lic->numero_edital): ?>
                                                <span class="font-mono"><?php echo $lic->numero_edital; ?></span>
                                            <?php endif; ?>
                                            <?php if ($lic->uf): ?>
                                                <span class="ml-2"><?php echo $lic->uf; ?></span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-300"><?php echo truncate_text($lic->orgao_nome, 40); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-500/20 text-blue-400">
                                        <?php echo $lic->modalidade; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php echo situacao_badge($lic->status); ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-white"><?php echo $lic->valor_estimado ? format_currency($lic->valor_estimado) : '-'; ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-sm text-gray-300"><?php echo $lic->data_abertura_proposta ? format_date($lic->data_abertura_proposta) : '-'; ?></p>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?php echo base_url('admin/licitacao/' . $lic->id); ?>" 
                                       class="inline-flex items-center px-3 py-1 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors text-sm">
                                        <i class="fas fa-eye mr-2"></i>Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Paginação -->
        <?php if ($total > $per_page): ?>
            <div class="px-6 py-4 border-t border-dark-700 flex items-center justify-between">
                <p class="text-sm text-gray-400">
                    Mostrando <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total); ?> de <?php echo $total; ?> licitações
                </p>
                <div class="flex space-x-2">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg bg-dark-800 border border-dark-700 text-white hover:bg-dark-700">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                        <a href="?page=<?php echo $i; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg <?php echo $i == $page ? 'bg-primary-600 text-white' : 'bg-dark-800 border border-dark-700 text-white hover:bg-dark-700'; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?><?php echo http_build_query(array_filter($filters)) ? '&' . http_build_query(array_filter($filters)) : ''; ?>" 
                           class="px-3 py-1 rounded-lg bg-dark-800 border border-dark-700 text-white hover:bg-dark-700">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
