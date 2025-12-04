<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Licitação - AllMight Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 min-h-screen">
    
    <!-- Header -->
    <header class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="<?php echo base_url('admin/licitacoes'); ?>" 
                       class="text-gray-400 hover:text-white transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>Voltar
                    </a>
                    <h1 class="text-2xl font-bold text-white">Detalhes da Licitação</h1>
                </div>
                <div class="flex items-center space-x-3">
                    <?php echo situacao_badge($licitacao->status); ?>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        
        <!-- Informações Principais -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2">
                    <h2 class="text-3xl font-bold text-white mb-4"><?php echo $licitacao->titulo; ?></h2>
                    
                    <?php if ($licitacao->numero_controle_pncp): ?>
                    <div class="mb-4 flex items-center gap-2">
                        <span class="text-xs text-gray-500">PNCP:</span>
                        <code class="text-xs font-mono bg-gray-700/50 px-2 py-1 rounded text-blue-400">
                            <?php echo $licitacao->numero_controle_pncp; ?>
                        </code>
                    </div>
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-2 gap-4 text-sm"><?php if ($licitacao->numero_edital): ?>
                        <div>
                            <span class="text-gray-400">Número do Edital:</span>
                            <p class="text-white font-semibold"><?php echo $licitacao->numero_edital; ?></p>
                        </div>
                        <?php endif; ?>
                        <?php if ($licitacao->numero_processo): ?>
                        <div>
                            <span class="text-gray-400">Número do Processo:</span>
                            <p class="text-white font-semibold"><?php echo $licitacao->numero_processo; ?></p>
                        </div>
                        <?php endif; ?>
                        <div>
                            <span class="text-gray-400">Modalidade:</span>
                            <p class="text-white">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                                    <?php echo $licitacao->modalidade; ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <span class="text-gray-400">Situação:</span>
                            <p class="text-white font-semibold"><?php echo $licitacao->situacao; ?></p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <div class="bg-gradient-to-br from-green-500/10 to-green-600/10 border border-green-500/30 rounded-xl p-4">
                        <span class="text-green-400 text-sm">Valor Estimado</span>
                        <?php if ($licitacao->orcamento_sigiloso): ?>
                            <p class="text-xl font-bold text-yellow-400 mt-1">
                                <i class="fas fa-lock mr-2"></i>Orçamento Sigiloso
                            </p>
                            <span class="text-xs text-gray-400 mt-2 block">
                                Valor não divulgado pela administração pública
                            </span>
                        <?php elseif ($licitacao->valor_estimado): ?>
                            <p class="text-2xl font-bold text-white mt-1">
                                <?php echo format_currency($licitacao->valor_estimado); ?>
                            </p>
                        <?php elseif ($valor_total_itens > 0): ?>
                            <p class="text-2xl font-bold text-white mt-1">
                                <?php echo format_currency($valor_total_itens); ?>
                            </p>
                            <span class="text-xs text-blue-400 mt-2 block">
                                <i class="fas fa-calculator mr-1"></i>Calculado a partir dos itens
                            </span>
                        <?php else: ?>
                            <p class="text-xl font-bold text-gray-400 mt-1">
                                Não informado
                            </p>
                            <span class="text-xs text-gray-500 mt-2 block">
                                <i class="fas fa-info-circle mr-1"></i>Valor não disponível
                            </span>
                        <?php endif; ?>
                    </div>

                    <button onclick="gerarMatches()" 
                            class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-magic mr-2"></i>Gerar Matches com Empresas
                    </button>
                </div>
            </div>

            <!-- Objeto -->
            <?php if ($licitacao->objeto): ?>
            <div class="border-t border-gray-700 pt-6">
                <h3 class="text-lg font-semibold text-white mb-3">
                    <i class="fas fa-file-alt mr-2 text-blue-400"></i>Objeto da Licitação
                </h3>
                <p class="text-gray-300 leading-relaxed"><?php echo nl2br($licitacao->objeto); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($licitacao->descricao): ?>
            <div class="border-t border-gray-700 pt-6 mt-6">
                <h3 class="text-lg font-semibold text-white mb-3">
                    <i class="fas fa-align-left mr-2 text-blue-400"></i>Descrição
                </h3>
                <p class="text-gray-300 leading-relaxed"><?php echo nl2br($licitacao->descricao); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            
            <!-- Informações do Órgão -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-building mr-3 text-blue-400"></i>Órgão Responsável
                </h3>
                <div class="space-y-3 text-sm">
                    <div>
                        <span class="text-gray-400">Nome:</span>
                        <p class="text-white font-semibold"><?php echo $licitacao->orgao_nome; ?></p>
                    </div>
                    <?php if ($licitacao->orgao_cnpj): ?>
                    <div>
                        <span class="text-gray-400">CNPJ:</span>
                        <p class="text-white font-mono"><?php echo $licitacao->orgao_cnpj; ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($licitacao->unidade_compradora): ?>
                    <div>
                        <span class="text-gray-400">Unidade Compradora:</span>
                        <p class="text-white"><?php echo $licitacao->unidade_compradora; ?></p>
                    </div>
                    <?php endif; ?>
                    <div class="flex gap-2">
                        <?php if ($licitacao->orgao_esfera): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                            <?php echo $licitacao->orgao_esfera; ?>
                        </span>
                        <?php endif; ?>
                        <?php if ($licitacao->orgao_poder): ?>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
                            <?php echo $licitacao->orgao_poder; ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Localização e Data -->
            <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-map-marker-alt mr-3 text-red-400"></i>Localização
                </h3>
                <div class="space-y-3 text-sm">
                    <?php if ($licitacao->uf && $licitacao->municipio): ?>
                    <div>
                        <span class="text-gray-400">Local:</span>
                        <p class="text-white font-semibold text-lg">
                            <?php echo $licitacao->municipio; ?> - <?php echo $licitacao->uf; ?>
                        </p>
                        <?php if ($licitacao->regiao): ?>
                        <p class="text-xs text-gray-500 mt-1">Região <?php echo $licitacao->regiao; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($licitacao->data_publicacao): ?>
                    <div class="pt-3 border-t border-gray-700">
                        <span class="text-gray-400">Publicado em:</span>
                        <p class="text-white font-semibold">
                            <i class="far fa-calendar text-blue-400 mr-2"></i><?php echo format_date($licitacao->data_publicacao); ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Timeline de Datas -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-6 flex items-center">
                <i class="fas fa-calendar-alt mr-3 text-green-400"></i>Cronograma
            </h3>
            <div class="relative">
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gradient-to-b from-blue-500 to-purple-500"></div>
                <div class="space-y-6 ml-12">
                    <?php if ($licitacao->data_publicacao): ?>
                    <div class="relative">
                        <div class="absolute -left-[3.25rem] w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center">
                            <i class="fas fa-plus text-white text-xs"></i>
                        </div>
                        <div>
                            <span class="text-gray-400 text-sm">Publicação</span>
                            <p class="text-white font-semibold"><?php echo format_date($licitacao->data_publicacao); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($licitacao->data_abertura_proposta): ?>
                    <div class="relative">
                        <div class="absolute -left-[3.25rem] w-8 h-8 rounded-full bg-green-500 flex items-center justify-center">
                            <i class="fas fa-door-open text-white text-xs"></i>
                        </div>
                        <div>
                            <span class="text-gray-400 text-sm">Abertura de Propostas</span>
                            <p class="text-white font-semibold"><?php echo format_date($licitacao->data_abertura_proposta); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($licitacao->data_encerramento_proposta): ?>
                    <div class="relative">
                        <div class="absolute -left-[3.25rem] w-8 h-8 rounded-full bg-yellow-500 flex items-center justify-center">
                            <i class="fas fa-hourglass-end text-white text-xs"></i>
                        </div>
                        <div>
                            <span class="text-gray-400 text-sm">Encerramento de Propostas</span>
                            <p class="text-white font-semibold"><?php echo format_date($licitacao->data_encerramento_proposta); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if ($licitacao->data_sessao_publica): ?>
                    <div class="relative">
                        <div class="absolute -left-[3.25rem] w-8 h-8 rounded-full bg-purple-500 flex items-center justify-center">
                            <i class="fas fa-users text-white text-xs"></i>
                        </div>
                        <div>
                            <span class="text-gray-400 text-sm">Sessão Pública</span>
                            <p class="text-white font-semibold"><?php echo format_date($licitacao->data_sessao_publica); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Características -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                <i class="fas fa-cogs mr-3 text-yellow-400"></i>Características
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
                <?php if ($licitacao->criterio_julgamento): ?>
                <div>
                    <span class="text-gray-400">Critério de Julgamento:</span>
                    <p class="text-white font-semibold"><?php echo $licitacao->criterio_julgamento; ?></p>
                </div>
                <?php endif; ?>
                <?php if ($licitacao->modo_disputa): ?>
                <div>
                    <span class="text-gray-400">Modo de Disputa:</span>
                    <p class="text-white font-semibold"><?php echo $licitacao->modo_disputa; ?></p>
                </div>
                <?php endif; ?>
                <?php if ($licitacao->tipo_contratacao): ?>
                <div>
                    <span class="text-gray-400">Tipo de Contratação:</span>
                    <p class="text-white font-semibold"><?php echo $licitacao->tipo_contratacao; ?></p>
                </div>
                <?php endif; ?>
            </div>

            <?php if ($licitacao->reserva_cota_me_epp || $licitacao->exclusiva_me_epp || $licitacao->ampla_participacao): ?>
            <div class="mt-4 pt-4 border-t border-gray-700">
                <span class="text-gray-400 text-sm mb-2 block">Benefícios:</span>
                <div class="flex flex-wrap gap-2">
                    <?php if ($licitacao->reserva_cota_me_epp): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-green-500/20 text-green-400 border border-green-500/30">
                        <i class="fas fa-check mr-1"></i>Cota ME/EPP
                    </span>
                    <?php endif; ?>
                    <?php if ($licitacao->exclusiva_me_epp): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-blue-500/20 text-blue-400 border border-blue-500/30">
                        <i class="fas fa-star mr-1"></i>Exclusiva ME/EPP
                    </span>
                    <?php endif; ?>
                    <?php if ($licitacao->ampla_participacao): ?>
                    <span class="px-3 py-1 rounded-full text-xs font-semibold bg-purple-500/20 text-purple-400 border border-purple-500/30">
                        <i class="fas fa-users mr-1"></i>Ampla Participação
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Itens -->
        <?php if (!empty($itens)): ?>
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                <i class="fas fa-list mr-3 text-orange-400"></i>Itens da Licitação (<?php echo count($itens); ?>)
            </h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="border-b border-gray-700">
                        <tr class="text-left text-gray-400">
                            <th class="pb-3 font-semibold">Item</th>
                            <th class="pb-3 font-semibold">Descrição</th>
                            <th class="pb-3 font-semibold">Quantidade</th>
                            <th class="pb-3 font-semibold">Unidade</th>
                            <th class="pb-3 font-semibold text-right">Valor Unit.</th>
                            <th class="pb-3 font-semibold text-right">Valor Total</th>
                        </tr>
                    </thead>
                    <tbody class="text-white">
                        <?php foreach ($itens as $item): ?>
                        <tr class="border-b border-gray-700/50 hover:bg-gray-700/30 transition-colors">
                            <td class="py-3 font-mono text-gray-400"><?php echo $item->numero_item; ?></td>
                            <td class="py-3">
                                <p class="font-semibold"><?php echo truncate_text($item->descricao, 80); ?></p>
                                <?php if (!empty($item->codigo_catalogo)): ?>
                                <p class="text-xs text-gray-400 mt-1">Código: <?php echo $item->codigo_catalogo; ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="py-3"><?php echo $item->quantidade ? number_format($item->quantidade, 2, ',', '.') : '-'; ?></td>
                            <td class="py-3"><?php echo $item->unidade_medida ?: '-'; ?></td>
                            <td class="py-3 text-right"><?php echo !empty($item->valor_unitario_estimado) ? format_currency($item->valor_unitario_estimado) : '-'; ?></td>
                            <td class="py-3 text-right font-semibold"><?php echo !empty($item->valor_total_estimado) ? format_currency($item->valor_total_estimado) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Arquivos - Gerenciador Avançado -->
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6 mb-6" 
             x-data="gerenciadorArquivos()" x-init="init()">
            
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <i class="fas fa-folder-open mr-3 text-yellow-400"></i>
                    Gerenciador de Documentos
                </h3>
                <div class="flex items-center gap-3">
                    <!-- Status Pills -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-1 rounded-full bg-blue-500/20 text-blue-400" x-text="stats.total + ' arquivos'"></span>
                        <span class="px-2 py-1 rounded-full bg-green-500/20 text-green-400" x-text="stats.baixados + ' baixados'"></span>
                        <span class="px-2 py-1 rounded-full bg-purple-500/20 text-purple-400" x-text="stats.com_texto + ' processados'"></span>
                    </div>
                </div>
            </div>
            
            <!-- Barra de Ações -->
            <div class="flex flex-wrap gap-3 mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-700">
                <button @click="baixarTodos()" 
                        :disabled="processando"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 disabled:opacity-50 text-white rounded-lg transition-all text-sm font-medium">
                    <i class="fas fa-cloud-download-alt" :class="{'fa-spin': processando && etapaAtual === 'download'}"></i>
                    <span>Baixar Todos</span>
                </button>
                
                <button @click="processarCompleto()" 
                        :disabled="processando"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 disabled:opacity-50 text-white rounded-lg transition-all text-sm font-medium">
                    <i class="fas fa-magic" :class="{'fa-spin': processando && etapaAtual === 'completo'}"></i>
                    <span>Processar Completo</span>
                </button>
                
                <button @click="processarPDFs()" 
                        :disabled="processando"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 disabled:opacity-50 text-white rounded-lg transition-all text-sm font-medium">
                    <i class="fas fa-file-pdf" :class="{'fa-spin': processando && etapaAtual === 'pdf'}"></i>
                    <span>Extrair Textos</span>
                </button>
                
                <button @click="verContexto()" 
                        :disabled="stats.com_texto === 0"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 disabled:opacity-50 text-white rounded-lg transition-all text-sm font-medium">
                    <i class="fas fa-brain"></i>
                    <span>Ver Contexto IA</span>
                </button>
                
                <!-- Botão Gerar Proposta IA -->
                <button @click="abrirModalProposta()" 
                        :disabled="processando"
                        class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-700 hover:to-cyan-700 disabled:opacity-50 text-white rounded-lg transition-all text-sm font-medium shadow-lg shadow-emerald-500/20">
                    <i class="fas fa-robot" :class="{'fa-spin': processando && etapaAtual === 'proposta'}"></i>
                    <span>🚀 Gerar Proposta com IA</span>
                </button>
                
                <!-- Busca -->
                <div class="flex-1 min-w-[200px]">
                    <div class="relative">
                        <input type="text" x-model="termoBusca" @keyup.enter="buscarTexto()"
                               placeholder="Buscar nos documentos..."
                               class="w-full bg-gray-800 border border-gray-600 rounded-lg px-4 py-2 pl-10 text-white text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-500"></i>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div x-show="processando" x-transition class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-400" x-text="mensagemProgresso"></span>
                    <span class="text-sm text-blue-400" x-text="progressoPercent + '%'"></span>
                </div>
                <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300"
                         :style="'width: ' + progressoPercent + '%'"></div>
                </div>
            </div>
            
            <!-- Filtros de Tipo -->
            <div class="flex flex-wrap gap-2 mb-4">
                <button @click="filtroTipo = ''" 
                        :class="filtroTipo === '' ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300'"
                        class="px-3 py-1 rounded-full text-xs transition-colors">
                    Todos
                </button>
                <template x-for="(count, tipo) in stats.tipos" :key="tipo">
                    <button @click="filtroTipo = tipo" 
                            :class="filtroTipo === tipo ? 'bg-blue-600 text-white' : 'bg-gray-700 text-gray-300'"
                            class="px-3 py-1 rounded-full text-xs transition-colors">
                        <span x-text="tipo"></span>
                        <span class="ml-1 opacity-70" x-text="'(' + count + ')'"></span>
                    </button>
                </template>
            </div>
            
            <!-- Lista de Arquivos -->
            <div class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                <template x-for="arquivo in arquivosFiltrados" :key="arquivo.id">
                    <div class="group flex items-center gap-4 p-4 bg-gray-700/30 rounded-xl hover:bg-gray-700/50 transition-all border border-gray-700/50 hover:border-gray-600">
                        
                        <!-- Ícone -->
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0"
                             :class="getIconBackground(arquivo)">
                            <i :class="getIconClass(arquivo)" class="text-2xl"></i>
                        </div>
                        
                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="text-white font-semibold truncate" x-text="arquivo.titulo"></p>
                                <!-- Status badges -->
                                <span x-show="arquivo.arquivo_baixado == 1" 
                                      class="px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400">
                                    <i class="fas fa-check mr-1"></i>Baixado
                                </span>
                                <span x-show="arquivo.conteudo_analisado == 1" 
                                      class="px-2 py-0.5 rounded-full text-xs bg-purple-500/20 text-purple-400">
                                    <i class="fas fa-brain mr-1"></i>Texto extraído
                                </span>
                                <span x-show="arquivo.arquivo_origem_id" 
                                      class="px-2 py-0.5 rounded-full text-xs bg-yellow-500/20 text-yellow-400">
                                    <i class="fas fa-file-archive mr-1"></i>Extraído
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-400">
                                <span x-text="arquivo.tipo_documento || 'Documento'"></span>
                                <span x-show="arquivo.arquivo_tamanho" x-text="formatSize(arquivo.arquivo_tamanho)"></span>
                                <span x-show="arquivo.data_publicacao" x-text="formatDate(arquivo.data_publicacao)"></span>
                            </div>
                        </div>
                        
                        <!-- Ações -->
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                            <!-- Download/Baixar -->
                            <button @click="baixarArquivo(arquivo)" 
                                    x-show="arquivo.arquivo_baixado != 1"
                                    class="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-colors"
                                    title="Baixar arquivo">
                                <i class="fas fa-download"></i>
                            </button>
                            
                            <!-- Extrair ZIP -->
                            <button @click="extrairZip(arquivo)" 
                                    x-show="isArchive(arquivo) && arquivo.arquivo_baixado == 1"
                                    class="p-2 rounded-lg bg-yellow-600 hover:bg-yellow-700 text-white transition-colors"
                                    title="Extrair arquivo">
                                <i class="fas fa-file-archive"></i>
                            </button>
                            
                            <!-- Extrair Texto PDF -->
                            <button @click="extrairTextoPDF(arquivo)" 
                                    x-show="isPDF(arquivo) && arquivo.arquivo_baixado == 1 && arquivo.conteudo_analisado != 1"
                                    class="p-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white transition-colors"
                                    title="Extrair texto do PDF">
                                <i class="fas fa-file-alt"></i>
                            </button>
                            
                            <!-- Ver Texto -->
                            <button @click="verTexto(arquivo)" 
                                    x-show="arquivo.conteudo_analisado == 1"
                                    class="p-2 rounded-lg bg-purple-600 hover:bg-purple-700 text-white transition-colors"
                                    title="Ver texto extraído">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            <!-- Download Externo -->
                            <a :href="arquivo.url_download || arquivo.uri_original" 
                               target="_blank"
                               x-show="arquivo.url_download || arquivo.uri_original"
                               class="p-2 rounded-lg bg-gray-600 hover:bg-gray-700 text-white transition-colors"
                               title="Abrir no PNCP">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </template>
                
                <!-- Empty State -->
                <div x-show="arquivos.length === 0" class="text-center py-12">
                    <i class="fas fa-folder-open text-6xl text-gray-600 mb-4"></i>
                    <p class="text-gray-400">Nenhum documento encontrado para esta licitação</p>
                </div>
            </div>
        
        <!-- Modal de Texto Extraído -->
        <div x-show="modalTexto" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @click.self="modalTexto = false" style="display: none;">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-white" x-text="arquivoSelecionado?.titulo"></h3>
                        <p class="text-sm text-gray-400" x-text="arquivoSelecionado?.tipo_documento"></p>
                    </div>
                    <button @click="modalTexto = false" class="p-2 rounded-lg hover:bg-gray-700 text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Tabs -->
                <div class="flex border-b border-gray-700">
                    <button @click="tabTexto = 'texto'" 
                            :class="tabTexto === 'texto' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-gray-400'"
                            class="px-6 py-3 text-sm font-medium transition-colors">
                        <i class="fas fa-file-alt mr-2"></i>Texto Extraído
                    </button>
                    <button @click="tabTexto = 'keywords'" 
                            :class="tabTexto === 'keywords' ? 'border-b-2 border-blue-500 text-blue-400' : 'text-gray-400'"
                            class="px-6 py-3 text-sm font-medium transition-colors">
                        <i class="fas fa-tags mr-2"></i>Palavras-chave
                    </button>
                </div>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Texto -->
                    <div x-show="tabTexto === 'texto'">
                        <div x-show="textoExtraido" class="prose prose-invert max-w-none">
                            <pre class="whitespace-pre-wrap text-sm text-gray-300 font-mono bg-gray-900/50 p-4 rounded-xl" x-text="textoExtraido"></pre>
                        </div>
                        <div x-show="!textoExtraido" class="text-center py-12 text-gray-500">
                            Nenhum texto extraído disponível
                        </div>
                    </div>
                    
                    <!-- Keywords -->
                    <div x-show="tabTexto === 'keywords'">
                        <div class="flex flex-wrap gap-2">
                            <template x-for="kw in keywordsExtraidas" :key="kw">
                                <span class="px-3 py-1.5 bg-blue-500/20 text-blue-400 rounded-full text-sm border border-blue-500/30" x-text="kw"></span>
                            </template>
                        </div>
                        <div x-show="!keywordsExtraidas || keywordsExtraidas.length === 0" class="text-center py-12 text-gray-500">
                            Nenhuma palavra-chave extraída
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Modal de Contexto IA -->
        <div x-show="modalContexto" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @click.self="modalContexto = false" style="display: none;">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl w-full max-w-5xl max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-700">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-2">
                            <i class="fas fa-brain text-purple-400"></i>
                            Contexto para Propostas
                        </h3>
                        <p class="text-sm text-gray-400">Análise consolidada de todos os documentos</p>
                    </div>
                    <button @click="modalContexto = false" class="p-2 rounded-lg hover:bg-gray-700 text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Status dos Documentos -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="p-4 rounded-xl" :class="contextoIA?.tem_edital ? 'bg-green-500/10 border border-green-500/30' : 'bg-gray-700/30 border border-gray-600'">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-contract text-2xl" :class="contextoIA?.tem_edital ? 'text-green-400' : 'text-gray-500'"></i>
                                <div>
                                    <p class="text-sm text-gray-400">Edital</p>
                                    <p class="font-semibold" :class="contextoIA?.tem_edital ? 'text-green-400' : 'text-gray-500'" 
                                       x-text="contextoIA?.tem_edital ? 'Encontrado' : 'Não encontrado'"></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl" :class="contextoIA?.tem_termo_referencia ? 'bg-green-500/10 border border-green-500/30' : 'bg-gray-700/30 border border-gray-600'">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-clipboard-list text-2xl" :class="contextoIA?.tem_termo_referencia ? 'text-green-400' : 'text-gray-500'"></i>
                                <div>
                                    <p class="text-sm text-gray-400">Termo de Referência</p>
                                    <p class="font-semibold" :class="contextoIA?.tem_termo_referencia ? 'text-green-400' : 'text-gray-500'"
                                       x-text="contextoIA?.tem_termo_referencia ? 'Encontrado' : 'Não encontrado'"></p>
                                </div>
                            </div>
                        </div>
                        <div class="p-4 rounded-xl" :class="contextoIA?.tem_minuta_contrato ? 'bg-green-500/10 border border-green-500/30' : 'bg-gray-700/30 border border-gray-600'">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-file-signature text-2xl" :class="contextoIA?.tem_minuta_contrato ? 'text-green-400' : 'text-gray-500'"></i>
                                <div>
                                    <p class="text-sm text-gray-400">Minuta de Contrato</p>
                                    <p class="font-semibold" :class="contextoIA?.tem_minuta_contrato ? 'text-green-400' : 'text-gray-500'"
                                       x-text="contextoIA?.tem_minuta_contrato ? 'Encontrado' : 'Não encontrado'"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Keywords Unificadas -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                            <i class="fas fa-tags text-blue-400"></i>
                            Palavras-chave Identificadas
                        </h4>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="kw in (contextoIA?.keywords_unificadas || []).slice(0, 20)" :key="kw">
                                <span class="px-3 py-1 bg-blue-500/20 text-blue-400 rounded-full text-sm" x-text="kw"></span>
                            </template>
                        </div>
                    </div>
                    
                    <!-- Resumo -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                            <i class="fas fa-align-left text-green-400"></i>
                            Resumo Geral
                        </h4>
                        <div class="bg-gray-900/50 rounded-xl p-4 border border-gray-700">
                            <p class="text-gray-300 leading-relaxed" x-text="contextoIA?.resumo || 'Processando...'"></p>
                        </div>
                    </div>
                    
                    <!-- Documentos Processados -->
                    <div>
                        <h4 class="text-lg font-semibold text-white mb-3 flex items-center gap-2">
                            <i class="fas fa-file-alt text-yellow-400"></i>
                            Documentos Processados
                        </h4>
                        <div class="space-y-3">
                            <template x-for="doc in (contextoIA?.documentos || [])" :key="doc.id">
                                <div class="bg-gray-700/30 rounded-xl p-4 border border-gray-600">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-medium text-white" x-text="doc.titulo"></p>
                                        <span class="text-xs text-gray-400" x-text="doc.tipo"></span>
                                    </div>
                                    <p class="text-sm text-gray-400 line-clamp-3" x-text="doc.resumo"></p>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="p-6 border-t border-gray-700 flex justify-end gap-3">
                    <button @click="copiarContexto()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors">
                        <i class="fas fa-copy mr-2"></i>Copiar Contexto
                    </button>
                    <a href="<?php echo base_url('admin/proposta_nova/' . $licitacao->id); ?>" 
                       class="px-4 py-2 bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-plus mr-2"></i>Criar Proposta com Contexto
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Modal de Geração de Proposta -->
        <div x-show="modalProposta" x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm"
             @click.self="modalProposta = false" style="display: none;">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <!-- Header -->
                <div class="flex items-center justify-between p-6 border-b border-gray-700 bg-gradient-to-r from-emerald-900/50 to-cyan-900/50 rounded-t-2xl">
                    <div>
                        <h3 class="text-xl font-bold text-white flex items-center gap-3">
                            <i class="fas fa-robot text-emerald-400"></i>
                            Gerar Proposta com IA
                        </h3>
                        <p class="text-sm text-gray-400 mt-1">Pipeline automatizado de geração</p>
                    </div>
                    <button @click="modalProposta = false" class="p-2 rounded-lg hover:bg-gray-700 text-gray-400 hover:text-white">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Content -->
                <div class="flex-1 overflow-y-auto p-6">
                    <!-- Explicação do Pipeline -->
                    <div class="mb-6 p-4 bg-gray-900/50 rounded-xl border border-gray-700">
                        <h4 class="text-white font-medium mb-3 flex items-center gap-2">
                            <i class="fas fa-info-circle text-blue-400"></i>
                            Pipeline de Geração Inteligente:
                        </h4>
                        <div class="space-y-2 text-sm text-gray-400">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-blue-500/20 text-blue-400 rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                <span>Baixar todos os documentos da licitação (edital, anexos, etc.)</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-purple-500/20 text-purple-400 rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                <span>Extrair arquivos ZIP recursivamente (incluindo ZIPs dentro de ZIPs)</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-orange-500/20 text-orange-400 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                <span>Extrair texto completo de todos os PDFs</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-green-500/20 text-green-400 rounded-full flex items-center justify-center text-xs font-bold">4</span>
                                <span><strong>Análise profunda com Gemini 2.5 Pro</strong> de TODO o conteúdo</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 bg-cyan-500/20 text-cyan-400 rounded-full flex items-center justify-center text-xs font-bold">5</span>
                                <span>Geração de proposta técnica e comercial completa</span>
                            </div>
                        </div>
                        <div class="mt-3 p-2 bg-emerald-900/20 rounded-lg border border-emerald-500/20">
                            <p class="text-emerald-400 text-xs flex items-center gap-2">
                                <i class="fas fa-brain"></i>
                                <span>A IA analisará TODOS os textos extraídos para criar uma proposta personalizada e detalhada.</span>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Seleção de Empresa -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-building mr-2 text-emerald-400"></i>
                            Selecionar Empresa
                        </label>
                        <select x-model="empresaSelecionada" 
                                class="w-full bg-gray-900 border border-gray-600 rounded-xl px-4 py-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                            <option value="">Carregando empresas...</option>
                            <template x-for="emp in empresasDisponiveis" :key="emp.id">
                                <option :value="emp.id" x-text="emp.nome + ' - ' + emp.cnpj"></option>
                            </template>
                        </select>
                    </div>
                    
                    <!-- Opções -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Prazo de Entrega</label>
                            <input type="text" x-model="opcoesProposta.prazo_entrega" 
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Condições de Pagamento</label>
                            <input type="text" x-model="opcoesProposta.condicoes_pagamento" 
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">Validade da Proposta</label>
                            <input type="text" x-model="opcoesProposta.validade_proposta" 
                                   class="w-full bg-gray-900 border border-gray-600 rounded-lg px-4 py-2 text-white text-sm focus:border-emerald-500">
                        </div>
                    </div>
                    
                    <!-- Progress de Geração -->
                    <div x-show="gerandoProposta" class="mb-6">
                        <div class="p-4 bg-gray-900/50 rounded-xl border border-emerald-500/30">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-white font-medium flex items-center gap-2">
                                    <i class="fas fa-spinner fa-spin text-emerald-400"></i>
                                    <span x-text="etapaGeracaoAtual"></span>
                                </span>
                                <span class="text-sm text-emerald-400" x-text="progressoGeracao + '%'"></span>
                            </div>
                            <div class="h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-emerald-500 to-cyan-500 transition-all duration-500"
                                     :style="'width: ' + progressoGeracao + '%'"></div>
                            </div>
                            
                            <!-- Info do Modelo -->
                            <div class="mt-3 flex items-center gap-2 text-xs text-gray-500">
                                <i class="fas fa-microchip"></i>
                                <span>Modelo: Gemini 2.5 Pro Preview (análise profunda de documentos)</span>
                            </div>
                            
                            <!-- Log de Etapas -->
                            <div class="mt-4 space-y-2 text-sm">
                                <template x-for="(log, idx) in logGeracao" :key="idx">
                                    <div class="flex items-center gap-2 text-gray-400">
                                        <i :class="log.success ? 'fas fa-check-circle text-green-400' : 'fas fa-spinner fa-spin text-blue-400'"></i>
                                        <span x-text="log.mensagem"></span>
                                    </div>
                                </template>
                            </div>
                            
                            <!-- Aviso de tempo -->
                            <div class="mt-4 p-3 bg-yellow-900/20 rounded-lg border border-yellow-500/30 text-yellow-400 text-xs">
                                <i class="fas fa-clock mr-2"></i>
                                A geração pode levar alguns minutos devido à análise completa de todos os documentos.
                            </div>
                        </div>
                    </div>
                    
                    <!-- Resultado -->
                    <div x-show="propostaGerada" class="p-4 bg-green-900/20 rounded-xl border border-green-500/30">
                        <div class="flex items-center gap-3 text-green-400 mb-3">
                            <i class="fas fa-check-circle text-2xl"></i>
                            <span class="font-bold">Proposta gerada com sucesso!</span>
                        </div>
                        <p class="text-gray-400 text-sm mb-3">A proposta foi criada com base na análise completa de todos os documentos da licitação.</p>
                        <a :href="'<?php echo base_url('admin/proposta/'); ?>' + propostaGeradaId" 
                           class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                            <i class="fas fa-eye"></i>
                            Ver Proposta
                        </a>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="p-6 border-t border-gray-700 flex justify-end gap-3">
                    <button @click="modalProposta = false" 
                            :disabled="gerandoProposta"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 disabled:opacity-50 text-white rounded-lg transition-colors">
                        Cancelar
                    </button>
                    <button @click="gerarPropostaCompleta()" 
                            :disabled="gerandoProposta || !empresaSelecionada"
                            class="px-6 py-2 bg-gradient-to-r from-emerald-600 to-cyan-600 hover:from-emerald-700 hover:to-cyan-700 disabled:opacity-50 text-white rounded-lg transition-colors font-medium">
                        <i class="fas fa-magic mr-2" :class="{'fa-spin': gerandoProposta}"></i>
                        <span x-text="gerandoProposta ? 'Gerando...' : 'Gerar Proposta'"></span>
                    </button>
                </div>
            </div>
        </div>
        
        </div><!-- Fim do x-data gerenciadorArquivos -->

        <!-- Links Externos -->
        <?php if ($licitacao->link_edital || $licitacao->link_portal): ?>
        <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
            <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                <i class="fas fa-link mr-3 text-cyan-400"></i>Links Externos
            </h3>
            <div class="flex flex-wrap gap-3">
                <?php if ($licitacao->link_edital): ?>
                <a href="<?php echo $licitacao->link_edital; ?>" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-file-contract mr-2"></i>Ver Edital
                </a>
                <?php endif; ?>
                <?php if ($licitacao->link_portal): ?>
                <a href="<?php echo $licitacao->link_portal; ?>" target="_blank"
                   class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                    <i class="fas fa-external-link-alt mr-2"></i>Acessar Portal
                </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>

    <script>
        const LICITACAO_ID = '<?php echo $licitacao->id; ?>';
        
        function gerenciadorArquivos() {
            return {
                arquivos: [],
                stats: {
                    total: 0,
                    baixados: 0,
                    processados: 0,
                    com_texto: 0,
                    tipos: {}
                },
                processando: false,
                etapaAtual: '',
                mensagemProgresso: '',
                progressoPercent: 0,
                filtroTipo: '',
                termoBusca: '',
                
                // Modal de texto
                modalTexto: false,
                arquivoSelecionado: null,
                textoExtraido: '',
                keywordsExtraidas: [],
                tabTexto: 'texto',
                
                // Modal de contexto
                modalContexto: false,
                contextoIA: null,
                
                // Modal de proposta
                modalProposta: false,
                empresasDisponiveis: [],
                empresaSelecionada: '',
                opcoesProposta: {
                    prazo_entrega: '30 dias',
                    condicoes_pagamento: 'Conforme edital',
                    validade_proposta: '60 dias'
                },
                gerandoProposta: false,
                etapaGeracaoAtual: '',
                progressoGeracao: 0,
                logGeracao: [],
                propostaGerada: false,
                propostaGeradaId: null,
                
                async init() {
                    await this.carregarArquivos();
                    await this.carregarEmpresas();
                },
                
                async carregarEmpresas() {
                    try {
                        const resp = await fetch('<?php echo base_url('admin/empresas_lista_simples'); ?>');
                        const data = await resp.json();
                        if (data.success) {
                            this.empresasDisponiveis = data.empresas;
                            if (data.empresas.length > 0) {
                                this.empresaSelecionada = data.empresas[0].id;
                            }
                        }
                    } catch (error) {
                        console.error('Erro ao carregar empresas:', error);
                    }
                },
                
                async carregarArquivos() {
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_stats/'); ?>${LICITACAO_ID}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.arquivos = data.arquivos || [];
                            this.stats = data.stats || this.stats;
                        }
                    } catch (error) {
                        console.error('Erro ao carregar arquivos:', error);
                    }
                },
                
                get arquivosFiltrados() {
                    let lista = this.arquivos;
                    
                    if (this.filtroTipo) {
                        lista = lista.filter(a => a.tipo_documento === this.filtroTipo);
                    }
                    
                    if (this.termoBusca) {
                        const termo = this.termoBusca.toLowerCase();
                        lista = lista.filter(a => 
                            (a.titulo && a.titulo.toLowerCase().includes(termo)) ||
                            (a.tipo_documento && a.tipo_documento.toLowerCase().includes(termo))
                        );
                    }
                    
                    return lista;
                },
                
                async baixarTodos() {
                    this.processando = true;
                    this.etapaAtual = 'download';
                    this.mensagemProgresso = 'Baixando arquivos...';
                    this.progressoPercent = 10;
                    
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_download_todos/'); ?>${LICITACAO_ID}`);
                        const data = await resp.json();
                        
                        this.progressoPercent = 100;
                        
                        if (data.success) {
                            this.showToast(`${data.baixados}/${data.total} arquivos baixados`, 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast('Erro ao baixar arquivos', 'error');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    } finally {
                        this.processando = false;
                    }
                },
                
                async processarCompleto() {
                    if (!confirm('Isso irá:\n• Baixar todos os arquivos\n• Extrair ZIPs automaticamente\n• Processar todos os PDFs\n\nPode demorar alguns minutos. Continuar?')) {
                        return;
                    }
                    
                    this.processando = true;
                    this.etapaAtual = 'completo';
                    this.mensagemProgresso = 'Iniciando processamento completo...';
                    this.progressoPercent = 5;
                    
                    try {
                        // Simular progresso
                        const progressInterval = setInterval(() => {
                            if (this.progressoPercent < 90) {
                                this.progressoPercent += 5;
                                const etapas = [
                                    'Baixando arquivos do PNCP...',
                                    'Detectando tipos de arquivo...',
                                    'Extraindo arquivos compactados...',
                                    'Processando PDFs...',
                                    'Extraindo texto dos documentos...',
                                    'Identificando palavras-chave...',
                                    'Gerando contexto unificado...'
                                ];
                                this.mensagemProgresso = etapas[Math.floor(this.progressoPercent / 15)] || 'Processando...';
                            }
                        }, 2000);
                        
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_processar_completo/'); ?>${LICITACAO_ID}`);
                        const data = await resp.json();
                        
                        clearInterval(progressInterval);
                        this.progressoPercent = 100;
                        this.mensagemProgresso = 'Concluído!';
                        
                        if (data.success) {
                            const stats = data.stats_finais;
                            this.showToast(`Processamento completo! ${stats.com_texto} documentos com texto extraído`, 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast('Erro no processamento', 'error');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    } finally {
                        this.processando = false;
                    }
                },
                
                async processarPDFs() {
                    this.processando = true;
                    this.etapaAtual = 'pdf';
                    this.mensagemProgresso = 'Extraindo texto dos PDFs...';
                    this.progressoPercent = 20;
                    
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_processar_pdfs/'); ?>${LICITACAO_ID}`);
                        const data = await resp.json();
                        
                        this.progressoPercent = 100;
                        
                        if (data.success) {
                            this.showToast(`${data.com_texto}/${data.processados} PDFs com texto extraído`, 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast('Erro ao processar PDFs', 'error');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    } finally {
                        this.processando = false;
                    }
                },
                
                async baixarArquivo(arquivo) {
                    try {
                        this.showToast('Baixando...', 'info');
                        const resp = await fetch(`<?php echo base_url('admin/arquivo_download/'); ?>${arquivo.id}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.showToast('Arquivo baixado!', 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast(data.message || 'Erro no download', 'error');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    }
                },
                
                async extrairZip(arquivo) {
                    try {
                        this.showToast('Extraindo...', 'info');
                        const resp = await fetch(`<?php echo base_url('admin/arquivo_extrair/'); ?>${arquivo.id}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            const qtd = data.arquivos_extraidos?.length || 0;
                            this.showToast(`${qtd} arquivos extraídos!`, 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast(data.message || 'Erro na extração', 'error');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    }
                },
                
                async extrairTextoPDF(arquivo) {
                    try {
                        this.showToast('Extraindo texto...', 'info');
                        const resp = await fetch(`<?php echo base_url('admin/arquivo_extrair_texto/'); ?>${arquivo.id}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.showToast(`Texto extraído! ${data.caracteres} caracteres`, 'success');
                            await this.carregarArquivos();
                        } else {
                            this.showToast(data.message || 'Erro na extração', 'warning');
                        }
                    } catch (error) {
                        this.showToast('Erro na requisição', 'error');
                    }
                },
                
                async verTexto(arquivo) {
                    this.arquivoSelecionado = arquivo;
                    this.tabTexto = 'texto';
                    
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivo_texto/'); ?>${arquivo.id}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.textoExtraido = data.arquivo.texto_extraido || '';
                            this.keywordsExtraidas = data.arquivo.palavras_chave || [];
                            this.modalTexto = true;
                        }
                    } catch (error) {
                        this.showToast('Erro ao carregar texto', 'error');
                    }
                },
                
                async verContexto() {
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_contexto/'); ?>${LICITACAO_ID}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.contextoIA = data.contexto;
                            this.modalContexto = true;
                        }
                    } catch (error) {
                        this.showToast('Erro ao carregar contexto', 'error');
                    }
                },
                
                async buscarTexto() {
                    if (!this.termoBusca) return;
                    
                    try {
                        const resp = await fetch(`<?php echo base_url('admin/arquivos_buscar/'); ?>${LICITACAO_ID}?termo=${encodeURIComponent(this.termoBusca)}`);
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.showToast(`${data.resultados} documentos encontrados`, 'info');
                            // Filtrar para mostrar apenas resultados
                            const ids = data.arquivos.map(a => a.id);
                            this.arquivos = this.arquivos.filter(a => ids.includes(a.id));
                        }
                    } catch (error) {
                        this.showToast('Erro na busca', 'error');
                    }
                },
                
                copiarContexto() {
                    if (!this.contextoIA) return;
                    
                    const texto = `CONTEXTO DA LICITAÇÃO:
                    
Edital: ${this.contextoIA.tem_edital ? 'Sim' : 'Não'}
Termo de Referência: ${this.contextoIA.tem_termo_referencia ? 'Sim' : 'Não'}
Minuta de Contrato: ${this.contextoIA.tem_minuta_contrato ? 'Sim' : 'Não'}

PALAVRAS-CHAVE IDENTIFICADAS:
${(this.contextoIA.keywords_unificadas || []).join(', ')}

RESUMO:
${this.contextoIA.resumo || 'N/A'}`;
                    
                    navigator.clipboard.writeText(texto).then(() => {
                        this.showToast('Contexto copiado!', 'success');
                    });
                },
                
                // Helpers
                getIconClass(arquivo) {
                    const titulo = (arquivo.titulo || '').toLowerCase();
                    const tipo = (arquivo.tipo_documento || '').toLowerCase();
                    
                    if (titulo.endsWith('.pdf') || tipo.includes('pdf')) return 'fas fa-file-pdf text-red-400';
                    if (titulo.endsWith('.doc') || titulo.endsWith('.docx')) return 'fas fa-file-word text-blue-400';
                    if (titulo.endsWith('.xls') || titulo.endsWith('.xlsx')) return 'fas fa-file-excel text-green-400';
                    if (titulo.endsWith('.zip') || titulo.endsWith('.rar')) return 'fas fa-file-archive text-yellow-400';
                    if (titulo.match(/\.(jpg|jpeg|png|gif)$/)) return 'fas fa-file-image text-purple-400';
                    if (tipo.includes('edital')) return 'fas fa-file-contract text-orange-400';
                    return 'fas fa-file text-gray-400';
                },
                
                getIconBackground(arquivo) {
                    const titulo = (arquivo.titulo || '').toLowerCase();
                    const tipo = (arquivo.tipo_documento || '').toLowerCase();
                    
                    if (titulo.endsWith('.pdf') || tipo.includes('pdf')) return 'bg-red-500/20';
                    if (titulo.endsWith('.doc') || titulo.endsWith('.docx')) return 'bg-blue-500/20';
                    if (titulo.endsWith('.xls') || titulo.endsWith('.xlsx')) return 'bg-green-500/20';
                    if (titulo.endsWith('.zip') || titulo.endsWith('.rar')) return 'bg-yellow-500/20';
                    if (titulo.match(/\.(jpg|jpeg|png|gif)$/)) return 'bg-purple-500/20';
                    return 'bg-gray-500/20';
                },
                
                isPDF(arquivo) {
                    const titulo = (arquivo.titulo || '').toLowerCase();
                    return titulo.endsWith('.pdf');
                },
                
                isArchive(arquivo) {
                    const titulo = (arquivo.titulo || '').toLowerCase();
                    return titulo.endsWith('.zip') || titulo.endsWith('.rar') || titulo.endsWith('.7z');
                },
                
                formatSize(bytes) {
                    if (!bytes) return '';
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(1024));
                    return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
                },
                
                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const d = new Date(dateStr);
                    return d.toLocaleDateString('pt-BR');
                },
                
                showToast(message, type = 'info') {
                    const colors = {
                        success: 'bg-green-500',
                        error: 'bg-red-500',
                        info: 'bg-blue-500',
                        warning: 'bg-yellow-500'
                    };
                    
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-3 rounded-lg shadow-lg z-[100] transition-all`;
                    toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'}-circle mr-2"></i>${message}`;
                    document.body.appendChild(toast);
                    
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(100%)';
                        setTimeout(() => toast.remove(), 300);
                    }, 3000);
                },
                
                // Funções de Geração de Proposta
                async abrirModalProposta() {
                    this.modalProposta = true;
                    this.gerandoProposta = false;
                    this.propostaGerada = false;
                    this.propostaGeradaId = null;
                    this.logGeracao = [];
                    this.progressoGeracao = 0;
                    
                    // Carregar empresas se ainda não carregou
                    if (this.empresasDisponiveis.length === 0) {
                        await this.carregarEmpresas();
                    }
                },
                
                async gerarPropostaCompleta() {
                    if (!this.empresaSelecionada) {
                        this.showToast('Selecione uma empresa', 'warning');
                        return;
                    }
                    
                    this.gerandoProposta = true;
                    this.logGeracao = [];
                    this.progressoGeracao = 0;
                    this.propostaGerada = false;
                    
                    // Etapa 1: Download
                    this.etapaGeracaoAtual = 'Baixando documentos...';
                    this.progressoGeracao = 10;
                    this.logGeracao.push({ mensagem: 'Iniciando download dos documentos...', success: false });
                    
                    try {
                        // Criar FormData
                        const formData = new FormData();
                        formData.append('empresa_id', this.empresaSelecionada);
                        formData.append('prazo_entrega', this.opcoesProposta.prazo_entrega);
                        formData.append('condicoes_pagamento', this.opcoesProposta.condicoes_pagamento);
                        formData.append('validade_proposta', this.opcoesProposta.validade_proposta);
                        
                        // Atualizar progress simulado durante a chamada
                        const progressInterval = setInterval(() => {
                            if (this.progressoGeracao < 85) {
                                this.progressoGeracao += 5;
                                
                                // Atualizar etapas visuais
                                if (this.progressoGeracao === 20) {
                                    this.logGeracao[0].success = true;
                                    this.logGeracao.push({ mensagem: 'Extraindo arquivos compactados...', success: false });
                                    this.etapaGeracaoAtual = 'Extraindo arquivos...';
                                }
                                if (this.progressoGeracao === 40) {
                                    this.logGeracao[1].success = true;
                                    this.logGeracao.push({ mensagem: 'Extraindo texto dos PDFs...', success: false });
                                    this.etapaGeracaoAtual = 'Processando PDFs...';
                                }
                                if (this.progressoGeracao === 60) {
                                    this.logGeracao[2].success = true;
                                    this.logGeracao.push({ mensagem: 'Preparando contexto para IA...', success: false });
                                    this.etapaGeracaoAtual = 'Preparando contexto...';
                                }
                                if (this.progressoGeracao === 75) {
                                    this.logGeracao[3].success = true;
                                    this.logGeracao.push({ mensagem: 'Gerando proposta com Gemini...', success: false });
                                    this.etapaGeracaoAtual = 'Gerando proposta com IA...';
                                }
                            }
                        }, 2000);
                        
                        // Chamada real
                        const resp = await fetch(`<?php echo base_url('admin/gerar_proposta_completa/'); ?>${LICITACAO_ID}`, {
                            method: 'POST',
                            body: formData
                        });
                        
                        clearInterval(progressInterval);
                        
                        const data = await resp.json();
                        
                        if (data.success) {
                            this.progressoGeracao = 100;
                            this.etapaGeracaoAtual = 'Concluído!';
                            
                            // Marcar todas como sucesso
                            this.logGeracao.forEach(l => l.success = true);
                            this.logGeracao.push({ mensagem: 'Proposta gerada e salva com sucesso!', success: true });
                            
                            this.propostaGerada = true;
                            this.propostaGeradaId = data.proposta_id;
                            
                            this.showToast('🎉 Proposta gerada com sucesso!', 'success');
                            
                            // Atualizar lista de arquivos
                            await this.carregarArquivos();
                        } else {
                            throw new Error(data.message || 'Erro ao gerar proposta');
                        }
                        
                    } catch (error) {
                        console.error('Erro:', error);
                        this.showToast('Erro ao gerar proposta: ' + error.message, 'error');
                        this.etapaGeracaoAtual = 'Erro na geração';
                    } finally {
                        this.gerandoProposta = false;
                    }
                }
            };
        }
        
        async function gerarMatches() {
            if (!confirm('Deseja gerar matches desta licitação com as empresas cadastradas?')) {
                return;
            }

            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Gerando matches...';

            try {
                const response = await fetch('<?php echo base_url('admin/gerar_matches/' . $licitacao->id); ?>');
                const data = await response.json();

                if (data.success) {
                    alert(`✅ ${data.message}`);
                    window.location.reload();
                } else {
                    alert('❌ Erro: ' + (data.message || 'Erro desconhecido'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (error) {
                console.error('Erro:', error);
                alert('❌ Erro ao gerar matches. Verifique o console.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }
    </script>

</body>
</html>
