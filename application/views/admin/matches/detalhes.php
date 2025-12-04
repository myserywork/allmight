<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Match - AllMight Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen" x-data="matchApp()">
    
    <!-- Header -->
    <header class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center mb-2">
                        <a href="<?php echo base_url('admin/matches'); ?>" class="text-gray-400 hover:text-white mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-bullseye mr-3 text-pink-400"></i>
                            Detalhes do Match
                        </h1>
                    </div>
                    <p class="text-gray-400 text-sm ml-10">ID: <?php echo $match->id; ?></p>
                </div>
                <div class="flex items-center space-x-4">
                    <?php
                    $score = $match->score_total;
                    $cor = $score >= 80 ? 'green' : ($score >= 60 ? 'blue' : ($score >= 40 ? 'yellow' : 'red'));
                    ?>
                    <div class="text-right">
                        <p class="text-sm text-gray-400">Score Total</p>
                        <p class="text-3xl font-bold text-<?php echo $cor; ?>-400"><?php echo number_format($score, 1); ?>%</p>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Coluna Principal (2/3) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Licitação -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-gavel mr-3 text-blue-400"></i>
                            Licitação
                        </h2>
                        <a href="<?php echo base_url('admin/licitacao/' . $match->licitacao_id); ?>" 
                           class="text-sm text-blue-400 hover:text-blue-300" target="_blank">
                            Ver Detalhes Completos <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-lg font-semibold text-white"><?php echo $licitacao->titulo; ?></p>
                            <p class="text-sm text-gray-400 mt-2">
                                <i class="fas fa-hashtag mr-1"></i>
                                <?php echo $licitacao->numero_controle_pncp; ?>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Órgão</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo truncate_text($licitacao->orgao_nome, 30); ?></p>
                            </div>
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Localização</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo $licitacao->municipio; ?> - <?php echo $licitacao->uf; ?></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Modalidade</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo $licitacao->modalidade; ?></p>
                            </div>
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Situação</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo $licitacao->situacao; ?></p>
                            </div>
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Valor Estimado</p>
                                <p class="text-sm text-green-400 font-bold mt-1">
                                    <?php 
                                    $valor = $licitacao->valor_estimado ?: ($licitacao->valor_total_itens ?? null);
                                    if ($valor && !$licitacao->orcamento_sigiloso) {
                                        echo 'R$ ' . number_format($valor, 2, ',', '.');
                                    } else {
                                        echo 'Sigiloso';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>

                        <?php if ($licitacao->exclusiva_me_epp): ?>
                        <div class="flex items-center bg-green-500/10 border border-green-500/30 rounded-lg p-3">
                            <i class="fas fa-check-circle text-green-400 text-xl mr-3"></i>
                            <span class="text-green-400 font-semibold">Exclusiva para ME/EPP</span>
                        </div>
                        <?php endif; ?>

                        <div class="bg-gray-700/30 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-2">Objeto</p>
                            <p class="text-sm text-gray-200"><?php echo truncate_text($licitacao->objeto, 200); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Empresa -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-white flex items-center">
                            <i class="fas fa-building mr-3 text-purple-400"></i>
                            Empresa
                        </h2>
                        <a href="<?php echo base_url('admin/empresa/' . $match->empresa_id); ?>" 
                           class="text-sm text-purple-400 hover:text-purple-300" target="_blank">
                            Ver Perfil Completo <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-lg font-semibold text-white"><?php echo $empresa->nome; ?></p>
                            <p class="text-sm text-gray-400 mt-1">
                                <i class="fas fa-id-card mr-1"></i>
                                CNPJ: <?php echo format_cnpj($empresa->cnpj); ?>
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Porte</p>
                                <span class="inline-block mt-1 px-3 py-1 bg-blue-500/20 text-blue-400 rounded-lg text-sm font-semibold">
                                    <?php echo $empresa->porte; ?>
                                </span>
                            </div>
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Situação</p>
                                <span class="inline-block mt-1 px-3 py-1 bg-<?php echo $empresa->ativo ? 'green' : 'red'; ?>-500/20 text-<?php echo $empresa->ativo ? 'green' : 'red'; ?>-400 rounded-lg text-sm font-semibold">
                                    <?php echo $empresa->ativo ? 'Ativa' : 'Inativa'; ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Localização</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo $empresa->cidade; ?> - <?php echo $empresa->uf; ?></p>
                            </div>
                            <div class="bg-gray-700/30 rounded-lg p-3">
                                <p class="text-xs text-gray-400">Telefone</p>
                                <p class="text-sm text-white font-semibold mt-1"><?php echo $empresa->telefone ?? '-'; ?></p>
                            </div>
                        </div>

                        <?php if (!empty($empresa->segmentos) && $empresa->segmentos != '[]'): ?>
                        <div class="bg-gray-700/30 rounded-lg p-3">
                            <p class="text-xs text-gray-400 mb-2">Segmentos de Atuação</p>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                // Verificar se é JSON
                                $segmentos_arr = json_decode($empresa->segmentos, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($segmentos_arr)) {
                                    foreach ($segmentos_arr as $seg) {
                                        if (!empty($seg)) {
                                            echo '<span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">' . htmlspecialchars(trim($seg)) . '</span>';
                                        }
                                    }
                                } else {
                                    // Se não for JSON, tentar como CSV
                                    foreach (explode(',', $empresa->segmentos) as $seg) {
                                        if (!empty(trim($seg))) {
                                            echo '<span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">' . htmlspecialchars(trim($seg)) . '</span>';
                                        }
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Score Breakdown -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white flex items-center mb-6">
                        <i class="fas fa-chart-bar mr-3 text-pink-400"></i>
                        Análise de Compatibilidade
                    </h2>
                    
                    <div class="space-y-4">
                        <!-- Score Compatibilidade -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-300">Compatibilidade de Segmento</span>
                                <span class="text-sm font-bold text-blue-400"><?php echo number_format($match->score_compatibilidade, 1); ?>%</span>
                            </div>
                            <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-600 to-blue-400" style="width: <?php echo $match->score_compatibilidade; ?>%"></div>
                            </div>
                        </div>

                        <!-- Score Experiência -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-300">Experiência e Capacidade</span>
                                <span class="text-sm font-bold text-purple-400"><?php echo number_format($match->score_experiencia, 1); ?>%</span>
                            </div>
                            <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-600 to-purple-400" style="width: <?php echo $match->score_experiencia; ?>%"></div>
                            </div>
                        </div>

                        <!-- Score Localização -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-300">Localização Geográfica</span>
                                <span class="text-sm font-bold text-green-400"><?php echo number_format($match->score_localizacao, 1); ?>%</span>
                            </div>
                            <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-600 to-green-400" style="width: <?php echo $match->score_localizacao; ?>%"></div>
                            </div>
                        </div>

                        <!-- Score Valor -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm text-gray-300">Adequação de Valor</span>
                                <span class="text-sm font-bold text-yellow-400"><?php echo number_format($match->score_valor, 1); ?>%</span>
                            </div>
                            <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-yellow-600 to-yellow-400" style="width: <?php echo $match->score_valor; ?>%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recomendação IA (se existir) -->
                <?php if ($match->recomendacao_ia || $match->pontos_fortes || $match->pontos_fracos): ?>
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h2 class="text-xl font-bold text-white flex items-center mb-6">
                        <i class="fas fa-brain mr-3 text-cyan-400"></i>
                        Análise de IA
                    </h2>
                    
                    <?php if ($match->recomendacao_ia): ?>
                    <div class="bg-cyan-500/10 border border-cyan-500/30 rounded-lg p-4 mb-4">
                        <p class="text-sm text-cyan-200"><?php echo $match->recomendacao_ia; ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($match->pontos_fortes): ?>
                    <div class="mb-4">
                        <p class="text-sm font-semibold text-green-400 mb-2">
                            <i class="fas fa-check-circle mr-2"></i>Pontos Fortes
                        </p>
                        <ul class="space-y-2">
                            <?php foreach (json_decode($match->pontos_fortes, true) as $ponto): ?>
                            <li class="text-sm text-gray-300 flex items-start">
                                <i class="fas fa-arrow-right text-green-500 mr-2 mt-1"></i>
                                <span><?php echo $ponto; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <?php if ($match->pontos_fracos): ?>
                    <div>
                        <p class="text-sm font-semibold text-yellow-400 mb-2">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Pontos de Atenção
                        </p>
                        <ul class="space-y-2">
                            <?php foreach (json_decode($match->pontos_fracos, true) as $ponto): ?>
                            <li class="text-sm text-gray-300 flex items-start">
                                <i class="fas fa-arrow-right text-yellow-500 mr-2 mt-1"></i>
                                <span><?php echo $ponto; ?></span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>

            <!-- Coluna Lateral (1/3) -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Status Card -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Status do Match</h3>
                    
                    <div class="mb-6">
                        <?php
                        $status_colors = [
                            'NOVO' => 'yellow',
                            'ANALISADO' => 'blue',
                            'INTERESSADO' => 'green',
                            'NAO_INTERESSADO' => 'red',
                            'PROPOSTA_ENVIADA' => 'purple',
                            'VENCEU' => 'green',
                            'PERDEU' => 'gray'
                        ];
                        $cor_atual = $status_colors[$match->status] ?? 'gray';
                        ?>
                        <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-semibold bg-<?php echo $cor_atual; ?>-500/20 text-<?php echo $cor_atual; ?>-400 border border-<?php echo $cor_atual; ?>-500/30">
                            <?php echo str_replace('_', ' ', $match->status); ?>
                        </span>
                    </div>

                    <div class="space-y-2 mb-6">
                        <button @click="atualizarStatus('ANALISADO')" 
                                class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm">
                            <i class="fas fa-check mr-2"></i>Marcar como Analisado
                        </button>
                        <button @click="atualizarStatus('INTERESSADO')" 
                                class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors text-sm">
                            <i class="fas fa-thumbs-up mr-2"></i>Interessado
                        </button>
                        <button @click="atualizarStatus('NAO_INTERESSADO')" 
                                class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm">
                            <i class="fas fa-thumbs-down mr-2"></i>Não Interessado
                        </button>
                        <button @click="atualizarStatus('PROPOSTA_ENVIADA')" 
                                class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm"
                                :disabled="!temProposta">
                            <i class="fas fa-paper-plane mr-2"></i>Proposta Enviada
                        </button>
                        <a href="<?php echo base_url('admin/proposta/nova/' . $match->id); ?>" 
                           class="w-full px-4 py-2 bg-pink-600 hover:bg-pink-700 text-white rounded-lg transition-colors text-sm text-center inline-block">
                            <i class="fas fa-file-alt mr-2"></i>Criar Proposta
                        </a>
                    </div>
                </div>

                <!-- Métricas -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Métricas</h3>
                    
                    <div class="space-y-4">
                        <?php if ($match->chance_vitoria): ?>
                        <div class="bg-gradient-to-r from-green-500/10 to-green-600/10 border border-green-500/30 rounded-lg p-4">
                            <p class="text-xs text-green-400 mb-1">Chance de Vitória</p>
                            <p class="text-2xl font-bold text-green-400"><?php echo number_format($match->chance_vitoria, 1); ?>%</p>
                        </div>
                        <?php endif; ?>

                        <?php if ($match->nivel_concorrencia): ?>
                        <div class="bg-gradient-to-r from-orange-500/10 to-orange-600/10 border border-orange-500/30 rounded-lg p-4">
                            <p class="text-xs text-orange-400 mb-1">Nível de Concorrência</p>
                            <p class="text-lg font-bold text-orange-400"><?php echo ucfirst($match->nivel_concorrencia); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="bg-gray-700/30 rounded-lg p-3">
                            <p class="text-xs text-gray-400">Criado em</p>
                            <p class="text-sm text-white font-semibold mt-1"><?php echo format_date($match->data_criacao); ?></p>
                        </div>

                        <?php if ($match->data_visualizacao): ?>
                        <div class="bg-gray-700/30 rounded-lg p-3">
                            <p class="text-xs text-gray-400">Visualizado em</p>
                            <p class="text-sm text-white font-semibold mt-1"><?php echo format_date($match->data_visualizacao); ?></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <script>
    function matchApp() {
        return {
            temProposta: <?php echo isset($proposta) && $proposta ? 'true' : 'false'; ?>,
            
            async atualizarStatus(novoStatus) {
                // Validar se pode marcar como PROPOSTA_ENVIADA
                if (novoStatus === 'PROPOSTA_ENVIADA' && !this.temProposta) {
                    alert('❌ Você precisa criar uma proposta antes de marcar como enviada!');
                    return;
                }
                
                if (!confirm('Deseja realmente atualizar o status deste match?')) {
                    return;
                }

                try {
                    const response = await fetch('<?php echo base_url('admin/match_atualizar_status'); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: new URLSearchParams({
                            match_id: '<?php echo $match->id; ?>',
                            status: novoStatus
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('✅ Status atualizado com sucesso!');
                        window.location.reload();
                    } else {
                        alert('❌ Erro ao atualizar status: ' + (data.message || 'Erro desconhecido'));
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('❌ Erro ao atualizar status. Verifique o console para mais detalhes.');
                }
            }
        }
    }
    </script>

</body>
</html>
