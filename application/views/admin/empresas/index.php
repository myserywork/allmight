<!-- Stats Cards -->
<div class="grid gap-6 md:grid-cols-4 mb-6">
    <div class="glass rounded-xl p-4 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Total</p>
                <p class="text-2xl font-bold text-white"><?php echo $stats['total']; ?></p>
            </div>
            <i class="fas fa-building text-2xl text-blue-400"></i>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Ativas</p>
                <p class="text-2xl font-bold text-white"><?php echo $stats['ativas']; ?></p>
            </div>
            <i class="fas fa-check-circle text-2xl text-green-400"></i>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-400">Inativas</p>
                <p class="text-2xl font-bold text-white"><?php echo $stats['inativas']; ?></p>
            </div>
            <i class="fas fa-times-circle text-2xl text-red-400"></i>
        </div>
    </div>
    
    <div class="glass rounded-xl p-4">
        <a href="<?php echo base_url('admin/empresa_nova'); ?>" 
           class="flex items-center justify-center h-full rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-medium transition-colors">
            <i class="fas fa-plus mr-2"></i>
            Nova Empresa
        </a>
    </div>
</div>

<!-- Filters -->
<div class="glass rounded-xl p-6 mb-6">
    <form method="get" class="grid gap-4 md:grid-cols-4">
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Buscar</label>
            <input type="text" name="search" value="<?php echo $filters['search']; ?>" 
                   placeholder="Nome, CNPJ..." 
                   class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">UF</label>
            <select name="uf" class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                <option value="">Todos</option>
                <?php 
                $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                foreach ($ufs as $uf): 
                ?>
                    <option value="<?php echo $uf; ?>" <?php echo $filters['uf'] == $uf ? 'selected' : ''; ?>><?php echo $uf; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Porte</label>
            <select name="porte" class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                <option value="">Todos</option>
                <option value="MEI" <?php echo $filters['porte'] == 'MEI' ? 'selected' : ''; ?>>MEI</option>
                <option value="ME" <?php echo $filters['porte'] == 'ME' ? 'selected' : ''; ?>>ME</option>
                <option value="EPP" <?php echo $filters['porte'] == 'EPP' ? 'selected' : ''; ?>>EPP</option>
                <option value="MEDIA" <?php echo $filters['porte'] == 'MEDIA' ? 'selected' : ''; ?>>Média</option>
                <option value="GRANDE" <?php echo $filters['porte'] == 'GRANDE' ? 'selected' : ''; ?>>Grande</option>
            </select>
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="flex-1 rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700 transition-colors">
                <i class="fas fa-search mr-2"></i>Filtrar
            </button>
            <a href="<?php echo base_url('admin/empresas'); ?>" 
               class="rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white hover:bg-dark-700 transition-colors">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="glass rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-dark-800 border-b border-dark-700">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Empresa</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">CNPJ</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Localização</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Porte</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Contato</th>
                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-700">
                <?php if (!empty($empresas)): ?>
                    <?php foreach ($empresas as $empresa): ?>
                        <tr class="hover:bg-dark-800/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <!-- Logo -->
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gradient-to-br from-primary-500/30 to-purple-500/30 flex-shrink-0 flex items-center justify-center">
                                        <?php if ($empresa->logo): ?>
                                            <img src="<?php echo base_url('uploads/logos/' . $empresa->logo); ?>" 
                                                 alt="<?php echo $empresa->nome; ?>" 
                                                 class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <span class="text-sm font-bold text-white/70"><?php echo strtoupper(substr($empresa->nome, 0, 2)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <p class="font-medium text-white"><?php echo $empresa->nome; ?></p>
                                        <p class="text-sm text-gray-400"><?php echo $empresa->razao_social; ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-300 font-mono"><?php echo cnpj_mask($empresa->cnpj); ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-300">
                                    <p><?php echo $empresa->cidade; ?></p>
                                    <p class="text-gray-400"><?php echo $empresa->uf; ?></p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center rounded-full bg-blue-500/20 px-3 py-1 text-xs font-medium text-blue-400">
                                    <?php echo $empresa->porte; ?>
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-300">
                                    <?php if ($empresa->email): ?>
                                        <p><i class="fas fa-envelope text-gray-500 mr-1"></i><?php echo $empresa->email; ?></p>
                                    <?php endif; ?>
                                    <?php if ($empresa->telefone): ?>
                                        <p><i class="fas fa-phone text-gray-500 mr-1"></i><?php echo $empresa->telefone; ?></p>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if ($empresa->ativo): ?>
                                    <span class="inline-flex items-center rounded-full bg-green-500/20 px-3 py-1 text-xs font-medium text-green-400">
                                        <i class="fas fa-check-circle mr-1"></i>Ativa
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full bg-red-500/20 px-3 py-1 text-xs font-medium text-red-400">
                                        <i class="fas fa-times-circle mr-1"></i>Inativa
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-center space-x-2">
                                    <a href="<?php echo base_url('admin/empresa_documentos/' . $empresa->id); ?>" 
                                       class="rounded-lg bg-purple-600 px-3 py-1 text-xs text-white hover:bg-purple-700 transition-colors"
                                       title="Documentos">
                                        <i class="fas fa-folder-open"></i>
                                    </a>
                                    <a href="<?php echo base_url('admin/empresa_editar/' . $empresa->id); ?>" 
                                       class="rounded-lg bg-primary-600 px-3 py-1 text-xs text-white hover:bg-primary-700 transition-colors"
                                       title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="<?php echo base_url('admin/empresa_toggle_status/' . $empresa->id); ?>" 
                                       class="rounded-lg bg-yellow-600 px-3 py-1 text-xs text-white hover:bg-yellow-700 transition-colors"
                                       title="<?php echo $empresa->ativo ? 'Desativar' : 'Ativar'; ?>">
                                        <i class="fas fa-power-off"></i>
                                    </a>
                                    <button onclick="confirmDelete('<?php echo $empresa->id; ?>', '<?php echo $empresa->nome; ?>')" 
                                            class="rounded-lg bg-red-600 px-3 py-1 text-xs text-white hover:bg-red-700 transition-colors"
                                            title="Deletar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <i class="fas fa-inbox text-4xl text-gray-600 mb-3"></i>
                            <p class="text-gray-400">Nenhuma empresa encontrada</p>
                            <a href="<?php echo base_url('admin/empresa_nova'); ?>" 
                               class="mt-4 inline-block rounded-lg bg-primary-600 px-4 py-2 text-sm text-white hover:bg-primary-700">
                                <i class="fas fa-plus mr-2"></i>Cadastrar primeira empresa
                            </a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmDelete(id, nome) {
    if (confirm(`Tem certeza que deseja deletar a empresa "${nome}"?\n\nEsta ação não pode ser desfeita.`)) {
        window.location.href = '<?php echo base_url('admin/empresa_deletar/'); ?>' + id;
    }
}
</script>
