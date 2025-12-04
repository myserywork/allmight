<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Propostas - AllMight Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen">
    
    <!-- Header -->
    <header class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div>
                <h1 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-file-invoice mr-3 text-cyan-400"></i>
                    Propostas Comerciais
                </h1>
                <p class="text-gray-400 text-sm mt-1">Gerenciar propostas para licitações</p>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Cards de Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-6 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500/10 to-blue-600/10 border border-blue-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-400 text-sm font-semibold">Total</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['total']); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <i class="fas fa-file-alt text-blue-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-yellow-500/10 to-yellow-600/10 border border-yellow-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-yellow-400 text-sm font-semibold">Rascunhos</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['rascunhos']); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-yellow-500/20 flex items-center justify-center">
                        <i class="fas fa-edit text-yellow-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-500/10 to-purple-600/10 border border-purple-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-400 text-sm font-semibold">Aprovadas</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['aprovadas']); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-purple-500/20 flex items-center justify-center">
                        <i class="fas fa-check-circle text-purple-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-400 text-sm font-semibold">Enviadas</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['enviadas']); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-green-500/20 flex items-center justify-center">
                        <i class="fas fa-paper-plane text-green-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-pink-500/10 to-pink-600/10 border border-pink-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-pink-400 text-sm font-semibold">Vencedoras</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['vencedoras']); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-pink-500/20 flex items-center justify-center">
                        <i class="fas fa-trophy text-pink-400 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-cyan-500/10 to-cyan-600/10 border border-cyan-500/30 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-cyan-400 text-sm font-semibold">Geradas IA</p>
                        <p class="text-3xl font-bold text-white mt-2"><?php echo number_format($stats['geradas_ia']); ?></p>
                        <p class="text-xs text-cyan-400 mt-1"><?php echo number_format($stats['percentual_ia'], 1); ?>%</p>
                    </div>
                    <div class="w-14 h-14 rounded-full bg-cyan-500/20 flex items-center justify-center">
                        <i class="fas fa-robot text-cyan-400 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <form method="get" action="<?php echo base_url('admin/propostas'); ?>" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <input type="text" name="search" 
                           value="<?php echo $filters['search'] ?? ''; ?>"
                           placeholder="Buscar por proposta, licitação ou empresa..."
                           class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-cyan-500">
                </div>
                <div>
                    <select name="empresa_id" class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-cyan-500">
                        <option value="">Todas as Empresas</option>
                        <?php foreach ($empresas as $emp): ?>
                        <option value="<?php echo $emp->id; ?>" <?php echo ($filters['empresa_id'] ?? '') == $emp->id ? 'selected' : ''; ?>>
                            <?php echo truncate_text($emp->nome, 30); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <select name="status" class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-xl text-white focus:outline-none focus:border-cyan-500">
                        <option value="">Status: Todos</option>
                        <option value="RASCUNHO" <?php echo ($filters['status'] ?? '') === 'RASCUNHO' ? 'selected' : ''; ?>>Rascunho</option>
                        <option value="EM_ELABORACAO" <?php echo ($filters['status'] ?? '') === 'EM_ELABORACAO' ? 'selected' : ''; ?>>Em Elaboração</option>
                        <option value="APROVADA" <?php echo ($filters['status'] ?? '') === 'APROVADA' ? 'selected' : ''; ?>>Aprovada</option>
                        <option value="ENVIADA" <?php echo ($filters['status'] ?? '') === 'ENVIADA' ? 'selected' : ''; ?>>Enviada</option>
                        <option value="VENCEDORA" <?php echo ($filters['status'] ?? '') === 'VENCEDORA' ? 'selected' : ''; ?>>Vencedora</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-xl transition-colors">
                        <i class="fas fa-filter mr-2"></i>Filtrar
                    </button>
                    <a href="<?php echo base_url('admin/propostas'); ?>" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition-colors">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabela de Propostas -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-900/50 border-b border-gray-700">
                    <tr class="text-left text-gray-400 text-sm">
                        <th class="px-6 py-4 font-semibold">Proposta</th>
                        <th class="px-6 py-4 font-semibold">Empresa</th>
                        <th class="px-6 py-4 font-semibold">Licitação</th>
                        <th class="px-6 py-4 font-semibold">Valor Licitação</th>
                        <th class="px-6 py-4 font-semibold">Valor Proposta</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700/50">
                    <?php if (!empty($propostas)): ?>
                        <?php foreach ($propostas as $prop): ?>
                            <tr class="hover:bg-gray-700/30 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-white font-semibold"><?php echo truncate_text($prop->titulo ?? 'Sem título', 40); ?></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <?php if ($prop->numero_proposta): ?>
                                        <span class="text-xs text-gray-400">
                                            <i class="fas fa-hashtag mr-1"></i><?php echo $prop->numero_proposta; ?>
                                        </span>
                                        <?php endif; ?>
                                        <?php if ($prop->gerado_por_ia): ?>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/20 text-cyan-400">
                                            <i class="fas fa-robot mr-1"></i>IA
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-white text-sm"><?php echo truncate_text($prop->empresa_nome, 25); ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo format_cnpj($prop->empresa_cnpj); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-white text-sm"><?php echo truncate_text($prop->licitacao_titulo, 30); ?></p>
                                    <p class="text-xs text-gray-400 mt-1"><?php echo $prop->orgao_nome; ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if (!empty($prop->licitacao_valor_estimado) && $prop->licitacao_valor_estimado > 0): ?>
                                        <p class="text-blue-400 font-bold text-sm">R$ <?php echo number_format($prop->licitacao_valor_estimado, 2, ',', '.'); ?></p>
                                    <?php else: ?>
                                        <span class="text-gray-500 text-sm">Não informado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-green-400 font-bold text-sm">R$ <?php echo number_format($prop->valor_final ?? $prop->valor_total ?? 0, 2, ',', '.'); ?></p>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status_colors = [
                                        'RASCUNHO' => 'yellow',
                                        'EM_ELABORACAO' => 'blue',
                                        'AGUARDANDO_APROVACAO' => 'purple',
                                        'APROVADA' => 'green',
                                        'ENVIADA' => 'cyan',
                                        'VENCEDORA' => 'pink',
                                        'PERDEDORA' => 'red',
                                        'DESCLASSIFICADA' => 'red',
                                        'CANCELADA' => 'gray'
                                    ];
                                    $cor = $status_colors[$prop->status] ?? 'gray';
                                    ?>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-<?php echo $cor; ?>-500/20 text-<?php echo $cor; ?>-400 border border-<?php echo $cor; ?>-500/30">
                                        <?php echo str_replace('_', ' ', $prop->status); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo base_url('admin/proposta/editar/' . $prop->id); ?>" 
                                           class="inline-flex items-center px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/proposta/preview/' . $prop->id); ?>" 
                                           class="inline-flex items-center px-3 py-1 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm"
                                           title="Preview"
                                           target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo base_url('admin/proposta/exportar/pdf/' . $prop->id); ?>" 
                                           class="inline-flex items-center px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm"
                                           title="Exportar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <i class="fas fa-inbox text-gray-600 text-4xl mb-4"></i>
                                <p class="text-gray-400 mb-4">Nenhuma proposta encontrada</p>
                                <p class="text-gray-500 text-sm">Comece criando uma proposta a partir de um match</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if ($total > $per_page): ?>
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-400">
                    Mostrando <?php echo (($page - 1) * $per_page) + 1; ?> - <?php echo min($page * $per_page, $total); ?> de <?php echo $total; ?> propostas
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
                           class="px-3 py-1 rounded-lg <?php echo $i === $page ? 'bg-cyan-600 text-white' : 'bg-gray-800 border border-gray-700 text-white hover:bg-gray-700'; ?>">
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

</body>
</html>
