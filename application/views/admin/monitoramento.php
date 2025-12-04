<!-- Stats Cards -->
<div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 mb-8">
    <!-- Total Alertas -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Total de Alertas</p>
                <p class="mt-2 text-3xl font-bold text-white"><?= number_format($stats->total ?? 0) ?></p>
                <p class="mt-2 text-xs text-blue-400">
                    <i class="fas fa-bell"></i> Monitoramento ativo
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-500/20">
                <i class="fas fa-bell text-2xl text-blue-400"></i>
            </div>
        </div>
    </div>

    <!-- Novos -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Novos</p>
                <p class="mt-2 text-3xl font-bold text-green-400"><?= number_format($stats->novos ?? 0) ?></p>
                <p class="mt-2 text-xs text-green-400">
                    <i class="fas fa-exclamation-circle"></i> Aguardando análise
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-500/20">
                <i class="fas fa-exclamation-circle text-2xl text-green-400"></i>
            </div>
        </div>
    </div>

    <!-- Visualizados -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Visualizados</p>
                <p class="mt-2 text-3xl font-bold text-cyan-400"><?= number_format($stats->visualizados ?? 0) ?></p>
                <p class="mt-2 text-xs text-cyan-400">
                    <i class="fas fa-eye"></i> Já analisados
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-cyan-500/20">
                <i class="fas fa-eye text-2xl text-cyan-400"></i>
            </div>
        </div>
    </div>

    <!-- Score Médio -->
    <div class="glass rounded-xl p-6 card-hover">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-400">Score Médio</p>
                <p class="mt-2 text-3xl font-bold text-yellow-400"><?= number_format($stats->score_medio ?? 0, 1) ?>%</p>
                <p class="mt-2 text-xs text-yellow-400">
                    <i class="fas fa-chart-line"></i> Compatibilidade
                </p>
            </div>
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-yellow-500/20">
                <i class="fas fa-chart-line text-2xl text-yellow-400"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="grid gap-6 lg:grid-cols-3" x-data="monitoramentoApp()">
    
    <!-- Alertas Urgentes -->
    <div class="lg:col-span-1">
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                    Alertas Urgentes
                </h3>
                <span class="text-xs text-gray-400">Próximos 7 dias</span>
            </div>
            
            <?php if (!empty($alertas_urgentes)): ?>
            <div class="space-y-3">
                <?php foreach ($alertas_urgentes as $urgente): ?>
                <div class="bg-dark-800/50 rounded-lg p-3 border border-red-500/20 hover:border-red-500/50 transition-all">
                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-white truncate" title="<?= htmlspecialchars($urgente->objeto ?? $urgente->licitacao_titulo ?? '') ?>">
                                <?= htmlspecialchars(mb_substr($urgente->objeto ?? $urgente->licitacao_titulo ?? 'Sem título', 0, 50)) ?>...
                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                <?= htmlspecialchars($urgente->empresa_nome ?? '') ?>
                            </p>
                        </div>
                        <div class="ml-2 text-right">
                            <span class="text-red-400 text-xs font-medium">
                                <?= date('d/m H:i', strtotime($urgente->data_abertura_proposta ?? '')) ?>
                            </span>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full">
                            Score: <?= $urgente->score_total ?? 0 ?>%
                        </span>
                        <a href="<?= base_url('admin/analise/' . $urgente->licitacao_id) ?>" 
                           class="text-xs text-primary-400 hover:text-primary-300">
                            Ver análise →
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-8">
                <i class="fas fa-check-circle text-4xl text-green-500/50 mb-3"></i>
                <p class="text-gray-400 text-sm">Nenhum alerta urgente</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Ações Rápidas -->
        <div class="glass rounded-xl p-6 mt-6">
            <h3 class="text-lg font-bold text-white mb-4">
                <i class="fas fa-bolt text-yellow-400 mr-2"></i>
                Ações Rápidas
            </h3>
            <div class="space-y-3">
                <button @click="executarMatching()" 
                        :disabled="executando"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 disabled:opacity-50 text-white rounded-lg transition-all neon-glow">
                    <i class="fas fa-sync-alt" :class="{'animate-spin': executando}"></i>
                    <span x-text="executando ? 'Processando...' : 'Executar Matching'"></span>
                </button>
                <button @click="limparEReprocessar()" 
                        :disabled="limpando"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 disabled:opacity-50 text-white rounded-lg transition-all">
                    <i class="fas fa-trash-alt" :class="{'animate-pulse': limpando}"></i>
                    <span x-text="limpando ? 'Limpando...' : 'Limpar e Reprocessar'"></span>
                </button>
                <a href="<?= base_url('admin/empresas') ?>" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-all border border-dark-600">
                    <i class="fas fa-cog"></i>
                    Configurar Keywords
                </a>
            </div>
        </div>
    </div>

    <!-- Lista de Alertas -->
    <div class="lg:col-span-2">
        <div class="glass rounded-xl p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-white">
                    <i class="fas fa-list text-primary-400 mr-2"></i>
                    Alertas de Licitações
                </h3>
                
                <!-- Filtros -->
                <div class="flex items-center gap-2">
                    <select id="filtro_empresa" onchange="filtrarAlertas()" 
                            class="bg-dark-700 border border-dark-600 text-white text-sm rounded-lg px-3 py-2 focus:border-primary-500 focus:outline-none">
                        <option value="">Todas empresas</option>
                        <?php foreach ($empresas as $emp): ?>
                        <option value="<?= $emp->id ?>" <?= ($this->input->get('empresa_id') == $emp->id) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($emp->nome) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtro_status" onchange="filtrarAlertas()"
                            class="bg-dark-700 border border-dark-600 text-white text-sm rounded-lg px-3 py-2 focus:border-primary-500 focus:outline-none">
                        <option value="">Todos status</option>
                        <option value="NOVO" <?= ($this->input->get('status') == 'NOVO') ? 'selected' : '' ?>>Novos</option>
                        <option value="VISUALIZADO" <?= ($this->input->get('status') == 'VISUALIZADO') ? 'selected' : '' ?>>Visualizados</option>
                        <option value="DESCARTADO" <?= ($this->input->get('status') == 'DESCARTADO') ? 'selected' : '' ?>>Descartados</option>
                    </select>
                </div>
            </div>

            <?php if (empty($alertas)): ?>
            <div class="text-center py-12">
                <div class="w-20 h-20 mx-auto mb-4 rounded-full bg-dark-700/50 flex items-center justify-center">
                    <i class="fas fa-bell-slash text-3xl text-gray-600"></i>
                </div>
                <h4 class="text-lg font-medium text-gray-400 mb-2">Nenhum alerta encontrado</h4>
                <p class="text-gray-500 text-sm mb-6">
                    Configure as keywords das empresas e execute o matching para gerar alertas.
                </p>
                <button @click="executarMatching()" 
                        :disabled="executando"
                        class="inline-flex items-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all neon-glow">
                    <i class="fas fa-sync-alt" :class="{'animate-spin': executando}"></i>
                    <span x-text="executando ? 'Processando...' : 'Executar Matching Agora'"></span>
                </button>
            </div>
            <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($alertas as $alerta): ?>
                <?php
                $score = $alerta->score_total ?? 0;
                $scoreColor = $score >= 80 ? 'green' : ($score >= 50 ? 'yellow' : 'red');
                $keywords_match = json_decode($alerta->keywords_match ?? '[]', true) ?: [];
                ?>
                <div class="bg-dark-800/50 rounded-xl p-4 border border-dark-700 hover:border-primary-500/30 transition-all group <?= $alerta->status == 'NOVO' ? 'border-l-4 border-l-green-500' : '' ?>">
                    <div class="flex items-start gap-4">
                        <!-- Score Circle -->
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 rounded-full bg-<?= $scoreColor ?>-500/20 flex items-center justify-center border-2 border-<?= $scoreColor ?>-500/50">
                                <span class="text-lg font-bold text-<?= $scoreColor ?>-400"><?= $score ?>%</span>
                            </div>
                        </div>
                        
                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div>
                                    <h4 class="text-white font-medium group-hover:text-primary-400 transition-colors">
                                        <?= htmlspecialchars(mb_substr($alerta->objeto ?? $alerta->licitacao_titulo ?? 'Sem título', 0, 80)) ?>...
                                    </h4>
                                    <p class="text-sm text-gray-400 mt-1">
                                        <span class="text-primary-400"><?= htmlspecialchars($alerta->empresa_nome ?? '') ?></span>
                                        • <?= htmlspecialchars($alerta->modalidade ?? '') ?>
                                        • <?= htmlspecialchars($alerta->orgao ?? '') ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        <?= htmlspecialchars($alerta->municipio ?? '') ?>/<?= htmlspecialchars($alerta->uf ?? '') ?>
                                    </p>
                                </div>
                                
                                <!-- Data/Prazo -->
                                <div class="text-right ml-4">
                                    <?php
                                    $data_abertura = $alerta->data_abertura_proposta ?? null;
                                    $dias_restantes = $alerta->dias_para_abertura ?? null;
                                    ?>
                                    <?php if ($data_abertura): ?>
                                    <p class="text-sm font-medium <?= ($dias_restantes !== null && $dias_restantes <= 3 && $dias_restantes >= 0) ? 'text-red-400' : 'text-white' ?>">
                                        <?= date('d/m/Y', strtotime($data_abertura)) ?>
                                    </p>
                                    <p class="text-xs <?= ($dias_restantes !== null && $dias_restantes <= 3 && $dias_restantes >= 0) ? 'text-red-400' : 'text-gray-500' ?>">
                                        <?php if ($dias_restantes !== null): ?>
                                            <?php if ($dias_restantes < 0): ?>
                                                Encerrada
                                            <?php elseif ($dias_restantes == 0): ?>
                                                Hoje!
                                            <?php elseif ($dias_restantes == 1): ?>
                                                Amanhã
                                            <?php else: ?>
                                                <?= $dias_restantes ?> dias
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Keywords Match -->
                            <?php if (!empty($keywords_match)): ?>
                            <div class="mt-3 flex flex-wrap gap-1">
                                <?php foreach (array_slice($keywords_match, 0, 6) as $kw): ?>
                                <span class="px-2 py-0.5 bg-primary-500/20 text-primary-300 text-xs rounded-full">
                                    <?= htmlspecialchars($kw) ?>
                                </span>
                                <?php endforeach; ?>
                                <?php if (count($keywords_match) > 6): ?>
                                <span class="px-2 py-0.5 bg-dark-600 text-gray-400 text-xs rounded-full">
                                    +<?= count($keywords_match) - 6 ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Actions -->
                            <div class="mt-3 flex items-center gap-3">
                                <?php
                                $statusColors = [
                                    'NOVO' => 'green',
                                    'VISUALIZADO' => 'blue',
                                    'DESCARTADO' => 'gray',
                                    'INTERESSADO' => 'purple',
                                    'EM_ANALISE' => 'yellow'
                                ];
                                $statusLabels = [
                                    'NOVO' => 'Novo',
                                    'VISUALIZADO' => 'Visualizado',
                                    'DESCARTADO' => 'Descartado',
                                    'INTERESSADO' => 'Interessado',
                                    'EM_ANALISE' => 'Em Análise'
                                ];
                                $statusColor = $statusColors[$alerta->status] ?? 'gray';
                                $statusLabel = $statusLabels[$alerta->status] ?? $alerta->status;
                                ?>
                                <span class="px-2 py-1 bg-<?= $statusColor ?>-500/20 text-<?= $statusColor ?>-400 text-xs rounded-full">
                                    <?= $statusLabel ?>
                                </span>
                                
                                <a href="<?= base_url('admin/licitacao/' . $alerta->licitacao_id) ?>" 
                                   onclick="marcarVisualizado(<?= $alerta->id ?>)"
                                   class="text-xs text-primary-400 hover:text-primary-300 transition-colors">
                                    <i class="fas fa-eye mr-1"></i> Ver Detalhes
                                </a>
                                
                                <button onclick="verScoreDetalhado(<?= htmlspecialchars(json_encode([
                                    'licitacao_id' => $alerta->licitacao_id,
                                    'titulo' => $alerta->objeto ?? $alerta->licitacao_titulo ?? '',
                                    'descricao' => $alerta->descricao ?? '',
                                    'orgao' => $alerta->orgao ?? '',
                                    'modalidade' => $alerta->modalidade ?? '',
                                    'municipio' => $alerta->municipio ?? '',
                                    'uf' => $alerta->uf ?? '',
                                    'valor_estimado' => $alerta->valor_estimado ?? 0,
                                    'data_abertura' => $alerta->data_abertura_proposta ?? '',
                                    'empresa_nome' => $alerta->empresa_nome ?? '',
                                    'link_edital' => $alerta->link_edital ?? '',
                                    'link_portal' => $alerta->link_portal ?? '',
                                    'score_total' => $alerta->score_total ?? 0,
                                    'score_keywords' => $alerta->score_keywords ?? 0,
                                    'score_localizacao' => $alerta->score_localizacao ?? 0,
                                    'score_porte' => $alerta->score_porte ?? 0,
                                    'score_valor' => $alerta->score_valor ?? 0,
                                    'keywords_match' => $keywords_match,
                                    'itens_match' => json_decode($alerta->itens_match ?? '[]', true) ?: []
                                ])) ?>)"
                                        class="text-xs text-cyan-400 hover:text-cyan-300 transition-colors">
                                    <i class="fas fa-chart-pie mr-1"></i> Ver Score
                                </button>
                                
                                <?php if ($alerta->status != 'DESCARTADO'): ?>
                                <button onclick="descartarAlerta(<?= $alerta->id ?>, this)"
                                        class="text-xs text-red-400 hover:text-red-300 transition-colors">
                                    <i class="fas fa-times mr-1"></i> Descartar
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Info Footer -->
<div class="mt-6 text-center">
    <p class="text-sm text-gray-500">
        <i class="fas fa-info-circle mr-1"></i>
        O matching é executado automaticamente ou pode ser disparado manualmente.
        Configure as keywords de cada empresa na área de cadastro.
    </p>
</div>

<!-- Explicação do Score -->
<div class="mt-6 glass rounded-xl p-6">
    <h3 class="text-lg font-bold text-white mb-4">
        <i class="fas fa-info-circle text-primary-400 mr-2"></i>
        Como funciona o Score de Compatibilidade
    </h3>
    <div class="grid md:grid-cols-4 gap-4">
        <div class="bg-dark-800/50 rounded-lg p-4 border border-primary-500/20">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-primary-500/20 flex items-center justify-center">
                    <i class="fas fa-key text-primary-400 text-sm"></i>
                </div>
                <span class="text-white font-medium">Keywords</span>
            </div>
            <p class="text-2xl font-bold text-primary-400">até 50 pts</p>
            <p class="text-xs text-gray-400 mt-1">Quantas palavras-chave da empresa aparecem na licitação</p>
        </div>
        <div class="bg-dark-800/50 rounded-lg p-4 border border-green-500/20">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center">
                    <i class="fas fa-map-marker-alt text-green-400 text-sm"></i>
                </div>
                <span class="text-white font-medium">Localização</span>
            </div>
            <p class="text-2xl font-bold text-green-400">até 20 pts</p>
            <p class="text-xs text-gray-400 mt-1">Mesmo estado = 20pts, mesma região = 10pts</p>
        </div>
        <div class="bg-dark-800/50 rounded-lg p-4 border border-yellow-500/20">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-yellow-500/20 flex items-center justify-center">
                    <i class="fas fa-building text-yellow-400 text-sm"></i>
                </div>
                <span class="text-white font-medium">Porte</span>
            </div>
            <p class="text-2xl font-bold text-yellow-400">até 15 pts</p>
            <p class="text-xs text-gray-400 mt-1">ME/EPP em licitações exclusivas = 15pts</p>
        </div>
        <div class="bg-dark-800/50 rounded-lg p-4 border border-cyan-500/20">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-full bg-cyan-500/20 flex items-center justify-center">
                    <i class="fas fa-dollar-sign text-cyan-400 text-sm"></i>
                </div>
                <span class="text-white font-medium">Valor</span>
            </div>
            <p class="text-2xl font-bold text-cyan-400">até 15 pts</p>
            <p class="text-xs text-gray-400 mt-1">Dentro da faixa configurada = 15pts</p>
        </div>
    </div>
    <div class="mt-4 p-3 bg-dark-800/30 rounded-lg border border-dark-600">
        <p class="text-sm text-gray-400">
            <i class="fas fa-lightbulb text-yellow-400 mr-2"></i>
            <strong class="text-white">Dica:</strong> Para aumentar os scores, configure UFs de interesse e adicione keywords mais específicas no cadastro da empresa.
            Score mínimo atual: <span class="text-primary-400 font-bold">30%</span>
        </p>
    </div>
</div>

<!-- Modal de Score Detalhado -->
<div id="modalScore" class="fixed inset-0 bg-black/70 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4 overflow-y-auto">
    <div class="glass rounded-2xl p-6 max-w-2xl w-full animate-fade-in my-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">
                <i class="fas fa-chart-pie text-primary-400 mr-2"></i>
                Análise de Compatibilidade
            </h3>
            <button onclick="fecharModalScore()" class="text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Info da Licitação -->
        <div class="bg-dark-800/50 rounded-xl p-4 mb-4 border border-dark-600">
            <h4 id="modalTitulo" class="text-white font-medium mb-2"></h4>
            
            <!-- Descrição -->
            <div id="modalDescricaoContainer" class="mb-3 p-3 bg-dark-700/50 rounded-lg border border-dark-600">
                <p class="text-xs text-gray-500 mb-1">
                    <i class="fas fa-file-alt mr-1"></i> Descrição do Objeto
                </p>
                <p id="modalDescricao" class="text-sm text-gray-300 line-clamp-4"></p>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-sm">
                <div class="flex items-center gap-2 text-gray-400">
                    <i class="fas fa-building text-primary-400"></i>
                    <span id="modalOrgao" class="truncate"></span>
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <i class="fas fa-gavel text-yellow-400"></i>
                    <span id="modalModalidade"></span>
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <i class="fas fa-map-marker-alt text-green-400"></i>
                    <span id="modalLocal"></span>
                </div>
                <div class="flex items-center gap-2 text-gray-400">
                    <i class="fas fa-calendar text-red-400"></i>
                    <span id="modalData"></span>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-dark-600 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <i class="fas fa-dollar-sign text-green-400"></i>
                    <span class="text-gray-400">Valor Estimado:</span>
                    <span id="modalValor" class="text-green-400 font-bold"></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-briefcase text-primary-400"></i>
                    <span id="modalEmpresa" class="text-primary-400 font-medium"></span>
                </div>
            </div>
        </div>
        
        <!-- Score e Breakdown lado a lado -->
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <!-- Score Total -->
            <div class="bg-dark-800/50 rounded-xl p-4 border border-dark-600 flex flex-col items-center justify-center">
                <div id="scoreCircle" class="w-28 h-28 rounded-full flex items-center justify-center border-4 mb-2">
                    <span id="scoreTotal" class="text-4xl font-bold"></span>
                </div>
                <p class="text-gray-400">Score de Compatibilidade</p>
                <p id="scoreRecomendacao" class="text-sm font-medium mt-1"></p>
            </div>
            
            <!-- Breakdown -->
            <div class="bg-dark-800/50 rounded-xl p-4 border border-dark-600">
                <h5 class="text-white font-medium mb-3 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-primary-400"></i>
                    Composição do Score
                </h5>
                <div class="space-y-3" id="scoreBreakdown">
                    <!-- Preenchido via JS -->
                </div>
            </div>
        </div>
        
        <!-- Keywords encontradas -->
        <div class="bg-dark-800/50 rounded-xl p-4 border border-primary-500/30 mb-4">
            <h5 class="text-white font-medium mb-3 flex items-center gap-2">
                <i class="fas fa-key text-primary-400"></i>
                Keywords Encontradas
                <span id="keywordsCount" class="px-2 py-0.5 bg-primary-500/20 text-primary-300 text-xs rounded-full"></span>
            </h5>
            <div id="keywordsMatch" class="flex flex-wrap gap-2">
                <!-- Preenchido via JS -->
            </div>
        </div>
        
        <!-- Itens com Match -->
        <div id="itensMatchContainer" class="bg-dark-800/50 rounded-xl p-4 border border-green-500/30 mb-4 hidden">
            <h5 class="text-white font-medium mb-3 flex items-center gap-2">
                <i class="fas fa-list-check text-green-400"></i>
                Itens com Keywords
                <span id="itensCount" class="px-2 py-0.5 bg-green-500/20 text-green-300 text-xs rounded-full"></span>
            </h5>
            <div id="itensMatchList" class="space-y-2 max-h-40 overflow-y-auto">
                <!-- Preenchido via JS -->
            </div>
        </div>
        
        <!-- Arquivos/Documentos -->
        <div id="arquivosContainer" class="bg-dark-800/50 rounded-xl p-4 border border-yellow-500/30 mb-4 hidden">
            <h5 class="text-white font-medium mb-3 flex items-center gap-2">
                <i class="fas fa-file-download text-yellow-400"></i>
                Documentos Disponíveis
                <span id="arquivosCount" class="px-2 py-0.5 bg-yellow-500/20 text-yellow-300 text-xs rounded-full"></span>
            </h5>
            <div id="arquivosList" class="space-y-2 max-h-48 overflow-y-auto">
                <!-- Preenchido via JS -->
            </div>
        </div>
        
        <!-- Ações -->
        <div class="space-y-3">
            <!-- Link para Edital/PNCP -->
            <div id="linkEditalContainer" class="hidden">
                <a id="btnEditalPNCP" href="#" target="_blank" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-yellow-600 to-orange-600 hover:from-yellow-700 hover:to-orange-700 text-white rounded-lg transition-all">
                    <i class="fas fa-external-link-alt"></i>
                    Acessar Edital no PNCP
                    <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
            
            <div class="flex gap-3">
                <a id="btnVerLicitacao" href="#" class="flex-1 flex items-center justify-center gap-2 px-4 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-all neon-glow">
                    <i class="fas fa-eye"></i>
                    Ver Detalhes Completos
                </a>
                <button onclick="fecharModalScore()" class="px-4 py-3 bg-dark-700 hover:bg-dark-600 text-white rounded-lg transition-all border border-dark-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function monitoramentoApp() {
    return {
        executando: false,
        limpando: false,
        
        async executarMatching() {
            if (this.executando) return;
            
            this.executando = true;
            
            try {
                const response = await fetch('<?= base_url('admin/executar_matching') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                console.log('Resultado matching:', data);
                
                if (data.success) {
                    showToast(data.message + (data.empresas_processadas ? ` (${data.empresas_processadas} empresas)` : ''), 'success');
                    // Recarregar página após 1.5s para mostrar novos alertas
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Erro: ' + (data.message || 'Erro desconhecido'), 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao executar matching', 'error');
            } finally {
                this.executando = false;
            }
        },
        
        async limparEReprocessar() {
            if (this.limpando) return;
            
            if (!confirm('Tem certeza que deseja APAGAR TODOS os alertas e reprocessar?\n\nIsso irá:\n• Deletar todos os alertas existentes\n• Executar novo matching para todas as empresas')) {
                return;
            }
            
            this.limpando = true;
            showToast('Limpando alertas...', 'info');
            
            try {
                // Primeiro limpar
                const limparResponse = await fetch('<?= base_url('admin/limpar_alertas') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const limparData = await limparResponse.json();
                
                if (!limparData.success) {
                    showToast('Erro ao limpar: ' + limparData.message, 'error');
                    return;
                }
                
                showToast(`${limparData.deletados} alertas removidos. Reprocessando...`, 'info');
                
                // Depois reprocessar
                const matchResponse = await fetch('<?= base_url('admin/executar_matching') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const matchData = await matchResponse.json();
                
                if (matchData.success) {
                    showToast(`Concluído! ${matchData.novos_alertas} novos alertas gerados.`, 'success');
                    setTimeout(() => window.location.reload(), 1500);
                } else {
                    showToast('Erro no matching: ' + matchData.message, 'error');
                }
            } catch (error) {
                console.error('Erro:', error);
                showToast('Erro ao processar', 'error');
            } finally {
                this.limpando = false;
            }
        }
    }
}

async function marcarVisualizado(alertaId) {
    try {
        await fetch('<?= base_url('admin/alerta_visualizar/') ?>' + alertaId, {
            method: 'POST'
        });
    } catch (error) {
        console.error('Erro ao marcar como visualizado:', error);
    }
}

async function descartarAlerta(alertaId, button) {
    if (!confirm('Deseja descartar este alerta?')) {
        return;
    }
    
    try {
        const response = await fetch('<?= base_url('admin/alerta_descartar/') ?>' + alertaId, {
            method: 'POST'
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Esconder o card
            const card = button.closest('.bg-dark-800\\/50');
            if (card) {
                card.style.opacity = '0.3';
                card.style.pointerEvents = 'none';
            }
            showToast('Alerta descartado', 'success');
        }
    } catch (error) {
        console.error('Erro ao descartar:', error);
        showToast('Erro ao descartar alerta', 'error');
    }
}

function filtrarAlertas() {
    const empresa = document.getElementById('filtro_empresa').value;
    const status = document.getElementById('filtro_status').value;
    
    let url = '<?= base_url('admin/monitoramento') ?>?';
    if (empresa) url += 'empresa_id=' + empresa + '&';
    if (status) url += 'status=' + status;
    
    window.location.href = url;
}

// Modal de Score Detalhado
function verScoreDetalhado(data) {
    const modal = document.getElementById('modalScore');
    
    // Preencher info da licitação
    document.getElementById('modalTitulo').textContent = data.titulo || 'Sem título';
    document.getElementById('modalOrgao').textContent = data.orgao || 'N/A';
    document.getElementById('modalModalidade').textContent = data.modalidade || 'N/A';
    document.getElementById('modalLocal').textContent = `${data.municipio || ''}/${data.uf || ''}`;
    document.getElementById('modalEmpresa').textContent = data.empresa_nome || 'N/A';
    
    // Preencher descrição
    const descricaoContainer = document.getElementById('modalDescricaoContainer');
    const descricaoEl = document.getElementById('modalDescricao');
    if (data.descricao && data.descricao.trim()) {
        descricaoEl.textContent = data.descricao;
        descricaoContainer.classList.remove('hidden');
    } else {
        descricaoContainer.classList.add('hidden');
    }
    
    // Formatar data
    if (data.data_abertura) {
        const dt = new Date(data.data_abertura);
        document.getElementById('modalData').textContent = dt.toLocaleDateString('pt-BR') + ' ' + dt.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
    } else {
        document.getElementById('modalData').textContent = 'N/A';
    }
    
    // Formatar valor
    const valor = parseFloat(data.valor_estimado) || 0;
    document.getElementById('modalValor').textContent = valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    
    // Link para licitação
    document.getElementById('btnVerLicitacao').href = '<?= base_url('admin/licitacao/') ?>' + data.licitacao_id;
    
    // Score
    const score = data.score_total;
    let colorClass = 'border-red-500 text-red-400';
    let recomendacao = { text: 'Baixa compatibilidade', color: 'text-red-400' };
    
    if (score >= 70) {
        colorClass = 'border-green-500 text-green-400';
        recomendacao = { text: 'Alta compatibilidade - Recomendado!', color: 'text-green-400' };
    } else if (score >= 50) {
        colorClass = 'border-yellow-500 text-yellow-400';
        recomendacao = { text: 'Média compatibilidade', color: 'text-yellow-400' };
    } else if (score >= 35) {
        colorClass = 'border-orange-500 text-orange-400';
        recomendacao = { text: 'Compatibilidade moderada', color: 'text-orange-400' };
    }
    
    const scoreCircle = document.getElementById('scoreCircle');
    const scoreTotal = document.getElementById('scoreTotal');
    scoreCircle.className = `w-28 h-28 rounded-full flex items-center justify-center border-4 mb-2 ${colorClass}`;
    scoreTotal.textContent = score + '%';
    scoreTotal.className = `text-4xl font-bold ${colorClass.split(' ')[1]}`;
    
    document.getElementById('scoreRecomendacao').textContent = recomendacao.text;
    document.getElementById('scoreRecomendacao').className = `text-sm font-medium mt-1 ${recomendacao.color}`;
    
    // Breakdown
    const items = [
        { label: 'Keywords', value: data.score_keywords, max: 50, color: 'primary', icon: 'key', desc: 'Palavras-chave encontradas' },
        { label: 'Localização', value: data.score_localizacao, max: 20, color: 'green', icon: 'map-marker-alt', desc: 'Proximidade geográfica' },
        { label: 'Porte', value: data.score_porte, max: 15, color: 'yellow', icon: 'building', desc: 'Adequação ao porte' },
        { label: 'Valor', value: data.score_valor, max: 15, color: 'cyan', icon: 'dollar-sign', desc: 'Faixa de valor' }
    ];
    
    const breakdown = document.getElementById('scoreBreakdown');
    breakdown.innerHTML = items.map(item => {
        const percent = (item.value / item.max) * 100;
        return `
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-${item.color}-500/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-${item.icon} text-${item.color}-400 text-sm"></i>
                </div>
                <div class="flex-1">
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-gray-400">${item.label}</span>
                        <span class="text-${item.color}-400 font-medium">${item.value}/${item.max}</span>
                    </div>
                    <div class="h-2 bg-dark-700 rounded-full overflow-hidden">
                        <div class="h-full bg-${item.color}-500 rounded-full transition-all" style="width: ${percent}%"></div>
                    </div>
                </div>
            </div>
        `;
    }).join('');
    
    // Keywords
    const keywordsDiv = document.getElementById('keywordsMatch');
    const keywordsCount = document.getElementById('keywordsCount');
    
    if (data.keywords_match && data.keywords_match.length > 0) {
        keywordsCount.textContent = data.keywords_match.length + ' encontradas';
        keywordsDiv.innerHTML = data.keywords_match.map(kw => 
            `<span class="px-3 py-1 bg-primary-500/20 text-primary-300 text-sm rounded-full border border-primary-500/30">
                <i class="fas fa-check-circle mr-1 text-xs"></i>${kw}
            </span>`
        ).join('');
    } else {
        keywordsCount.textContent = '0';
        keywordsDiv.innerHTML = '<span class="text-gray-500 text-sm">Nenhuma keyword encontrada no texto da licitação</span>';
    }
    
    // Itens com match (buscar via AJAX se tiver itens_match)
    const itensContainer = document.getElementById('itensMatchContainer');
    const itensList = document.getElementById('itensMatchList');
    const itensCount = document.getElementById('itensCount');
    
    if (data.itens_match && data.itens_match.length > 0) {
        itensContainer.classList.remove('hidden');
        itensCount.textContent = data.itens_match.length + ' itens';
        
        // Buscar detalhes dos itens
        fetch('<?= base_url('admin/get_itens_licitacao/') ?>' + data.licitacao_id)
            .then(r => r.json())
            .then(result => {
                if (result.success && result.itens) {
                    const itensHtml = result.itens
                        .filter(item => data.itens_match.includes(item.id) || data.itens_match.includes(parseInt(item.id)))
                        .slice(0, 5)
                        .map(item => `
                            <div class="bg-dark-700/50 rounded-lg p-2 border border-dark-600">
                                <div class="flex items-start gap-2">
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded flex-shrink-0">
                                        Item ${item.numero_item || '?'}
                                    </span>
                                    <p class="text-gray-300 text-sm line-clamp-2">${item.descricao || 'Sem descrição'}</p>
                                </div>
                            </div>
                        `).join('');
                    
                    itensList.innerHTML = itensHtml || '<span class="text-gray-500 text-sm">Carregando itens...</span>';
                    
                    if (result.itens.filter(item => data.itens_match.includes(item.id) || data.itens_match.includes(parseInt(item.id))).length > 5) {
                        itensList.innerHTML += '<p class="text-gray-500 text-xs text-center mt-2">...e mais itens</p>';
                    }
                }
            })
            .catch(() => {
                itensList.innerHTML = '<span class="text-gray-500 text-sm">Erro ao carregar itens</span>';
            });
    } else {
        itensContainer.classList.add('hidden');
    }
    
    // Link do Edital/PNCP
    const linkEditalContainer = document.getElementById('linkEditalContainer');
    const btnEditalPNCP = document.getElementById('btnEditalPNCP');
    
    if (data.link_edital || data.link_portal) {
        const linkUrl = data.link_edital || data.link_portal;
        btnEditalPNCP.href = linkUrl;
        linkEditalContainer.classList.remove('hidden');
    } else {
        linkEditalContainer.classList.add('hidden');
    }
    
    // Arquivos (buscar via AJAX)
    const arquivosContainer = document.getElementById('arquivosContainer');
    const arquivosList = document.getElementById('arquivosList');
    const arquivosCount = document.getElementById('arquivosCount');
    
    arquivosContainer.classList.add('hidden');
    
    fetch('<?= base_url('admin/get_arquivos_licitacao/') ?>' + data.licitacao_id)
        .then(r => r.json())
        .then(result => {
            if (result.success && result.arquivos && result.arquivos.length > 0) {
                arquivosContainer.classList.remove('hidden');
                arquivosCount.textContent = result.arquivos.length + ' arquivos';
                
                arquivosList.innerHTML = result.arquivos.map(arq => {
                    const ext = (arq.titulo || '').split('.').pop().toLowerCase();
                    const icon = getFileIcon(ext);
                    const url = arq.url_download || arq.uri_original || '#';
                    const nome = arq.titulo || arq.tipo_documento_descricao || 'Documento';
                    const tamanho = arq.arquivo_tamanho ? formatFileSize(arq.arquivo_tamanho) : '';
                    
                    return `
                        <a href="${url}" target="_blank" 
                           class="flex items-center gap-3 p-2 bg-dark-700/50 rounded-lg border border-dark-600 hover:border-yellow-500/50 hover:bg-dark-600/50 transition-all group">
                            <div class="w-10 h-10 rounded-lg bg-yellow-500/20 flex items-center justify-center flex-shrink-0">
                                <i class="${icon} text-yellow-400"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-white truncate group-hover:text-yellow-400 transition-colors">${nome}</p>
                                ${tamanho ? `<p class="text-xs text-gray-500">${tamanho}</p>` : ''}
                            </div>
                            <i class="fas fa-download text-gray-500 group-hover:text-yellow-400 transition-colors"></i>
                        </a>
                    `;
                }).join('');
            }
        })
        .catch(() => {
            // Silenciosamente ignora erro de arquivos
        });
    
    modal.classList.remove('hidden');
}

// Helper: ícone do arquivo
function getFileIcon(ext) {
    const icons = {
        'pdf': 'fas fa-file-pdf',
        'doc': 'fas fa-file-word',
        'docx': 'fas fa-file-word',
        'xls': 'fas fa-file-excel',
        'xlsx': 'fas fa-file-excel',
        'zip': 'fas fa-file-archive',
        'rar': 'fas fa-file-archive',
        'jpg': 'fas fa-file-image',
        'jpeg': 'fas fa-file-image',
        'png': 'fas fa-file-image',
        'txt': 'fas fa-file-alt'
    };
    return icons[ext] || 'fas fa-file';
}

// Helper: formatar tamanho do arquivo
function formatFileSize(bytes) {
    if (!bytes) return '';
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(1024));
    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
}

function fecharModalScore() {
    document.getElementById('modalScore').classList.add('hidden');
}

// Fechar modal ao clicar fora
document.getElementById('modalScore')?.addEventListener('click', function(e) {
    if (e.target === this) fecharModalScore();
});

// Toast notification helper
function showToast(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const toast = document.createElement('div');
    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-50`;
    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle mr-2"></i>${message}`;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(100%)';
        toast.style.transition = 'all 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>
