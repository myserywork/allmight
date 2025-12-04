<div x-data="propostaFormData()" x-cloak class="space-y-8">
    
    <!-- Header -->
    <header class="bg-gray-900/50 backdrop-blur-sm border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <div class="flex items-center mb-2">
                        <a href="<?php echo base_url('admin/propostas'); ?>" class="text-gray-400 hover:text-white mr-4">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h1 class="text-2xl font-bold text-white flex items-center">
                            <i class="fas fa-file-invoice mr-3 text-cyan-400"></i>
                            <?php echo $proposta ? 'Editar Proposta' : 'Nova Proposta'; ?>
                        </h1>
                    </div>
                    <?php if ($proposta): ?>
                    <p class="text-gray-400 text-sm ml-10">ID: <?php echo $proposta->id; ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($proposta): ?>
                    <button @click="gerarIA()" 
                            :disabled="loading"
                            class="px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white rounded-lg transition-colors text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                        <i :class="loading ? 'fas fa-spinner fa-spin mr-2' : 'fas fa-robot mr-2'"></i>
                        <span x-text="loading ? 'Gerando...' : 'Regenerar com IA'"></span>
                    </button>
                    <?php endif; ?>
                    <button type="submit" form="proposta-form"
                            :disabled="loading"
                            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-save mr-2"></i>Salvar
                    </button>
                </div>
            </div>
        </div>
    </header>

    <div class="container mx-auto px-6 py-8">
        
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            
            <!-- Coluna Principal (3/4) -->
            <div class="lg:col-span-3 space-y-6">
                
                <!-- Formulário -->
                <form id="proposta-form" method="post" action="<?php echo base_url('admin/proposta/salvar'); ?>" class="space-y-6">
                    <input type="hidden" name="proposta_id" value="<?php echo $proposta->id ?? ''; ?>">
                    <input type="hidden" name="match_id" value="<?php echo $match->id ?? ''; ?>">
                    <input type="hidden" name="empresa_id" value="<?php echo $empresa->id ?? ''; ?>">
                    <input type="hidden" name="licitacao_id" value="<?php echo $licitacao->id ?? ''; ?>">
                    
                    <!-- Dados Básicos -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <i class="fas fa-info-circle mr-3 text-cyan-400"></i>
                            Dados Básicos
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Título da Proposta *</label>
                                <input type="text" name="titulo" required
                                       value="<?php echo $proposta->titulo ?? 'Proposta para ' . ($licitacao->titulo ?? ''); ?>"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Número da Proposta</label>
                                <input type="text" name="numero_proposta"
                                       value="<?php echo $proposta->numero_proposta ?? ''; ?>"
                                       placeholder="Ex: PROP-2025-001"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Status</label>
                                <select name="status" class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                                    <option value="RASCUNHO" <?php echo ($proposta->status ?? 'RASCUNHO') == 'RASCUNHO' ? 'selected' : ''; ?>>Rascunho</option>
                                    <option value="EM_ELABORACAO" <?php echo ($proposta->status ?? '') == 'EM_ELABORACAO' ? 'selected' : ''; ?>>Em Elaboração</option>
                                    <option value="AGUARDANDO_APROVACAO" <?php echo ($proposta->status ?? '') == 'AGUARDANDO_APROVACAO' ? 'selected' : ''; ?>>Aguardando Aprovação</option>
                                    <option value="APROVADA" <?php echo ($proposta->status ?? '') == 'APROVADA' ? 'selected' : ''; ?>>Aprovada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Valores -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <i class="fas fa-dollar-sign mr-3 text-green-400"></i>
                            Valores
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Valor Total *</label>
                                <input type="number" name="valor_total" step="0.01" required
                                       value="<?php echo $proposta->valor_total ?? ($licitacao->valor_estimado ?? 0); ?>"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Desconto (%)</label>
                                <input type="number" name="desconto_percentual" step="0.01"
                                       value="<?php echo $proposta->desconto_percentual ?? ''; ?>"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Valor Desconto</label>
                                <input type="number" name="valor_desconto" step="0.01"
                                       value="<?php echo $proposta->valor_desconto ?? ''; ?>"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Valor Final *</label>
                                <input type="number" name="valor_final" step="0.01" required
                                       value="<?php echo $proposta->valor_final ?? ($proposta->valor_total ?? ($licitacao->valor_estimado ?? 0)); ?>"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- Condições Comerciais -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <i class="fas fa-handshake mr-3 text-purple-400"></i>
                            Condições Comerciais
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Prazo de Entrega</label>
                                <input type="text" name="prazo_entrega"
                                       value="<?php echo $proposta->prazo_entrega ?? '30 dias'; ?>"
                                       placeholder="Ex: 30 dias"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Validade da Proposta</label>
                                <input type="text" name="validade_proposta"
                                       value="<?php echo $proposta->validade_proposta ?? '60 dias'; ?>"
                                       placeholder="Ex: 60 dias"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Condições de Pagamento</label>
                                <input type="text" name="condicoes_pagamento"
                                       value="<?php echo $proposta->condicoes_pagamento ?? 'Conforme edital'; ?>"
                                       placeholder="Ex: 30 dias"
                                       class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- Editor de Conteúdo -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-white flex items-center">
                                <i class="fas fa-file-alt mr-3 text-cyan-400"></i>
                                Conteúdo da Proposta
                            </h2>
                            <?php 
                            $mostra_botao_ia = !$proposta || empty($proposta->conteudo_html);
                            if ($mostra_botao_ia): 
                            ?>
                            <button type="button" @click="gerarIA()"
                                    :disabled="loading"
                                    class="px-4 py-2 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-lg transition-colors font-semibold disabled:opacity-50 disabled:cursor-not-allowed">
                                <i :class="loading ? 'fas fa-spinner fa-spin mr-2' : 'fas fa-robot mr-2'"></i>
                                <span x-text="loading ? 'Gerando...' : 'Gerar com IA'"></span>
                            </button>
                            <?php endif; ?>
                        </div>
                        
                        <textarea id="editor" name="conteudo_html"><?php echo isset($proposta->conteudo_html) ? $proposta->conteudo_html : ''; ?></textarea>
                    </div>

                    <!-- Observações -->
                    <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                        <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                            <i class="fas fa-comments mr-3 text-yellow-400"></i>
                            Observações
                        </h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Observações Internas</label>
                                <textarea name="observacoes_internas" rows="3"
                                          class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500"
                                          placeholder="Notas internas, não aparecerão na proposta exportada..."><?php echo $proposta->observacoes_internas ?? ''; ?></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-semibold text-gray-300 mb-2">Observações para o Cliente</label>
                                <textarea name="observacoes_cliente" rows="3"
                                          class="w-full px-4 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-cyan-500"
                                          placeholder="Informações adicionais que aparecerão na proposta..."><?php echo $proposta->observacoes_cliente ?? ''; ?></textarea>
                            </div>
                        </div>
                    </div>

                </form>

            </div>

            <!-- Coluna Lateral (1/4) -->
            <div class="lg:col-span-1 space-y-6">
                
                <!-- Empresa -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-building mr-2 text-purple-400"></i>
                        Empresa
                    </h3>
                    <p class="text-sm text-white font-semibold"><?php echo $empresa->nome; ?></p>
                    <p class="text-xs text-gray-400 mt-1"><?php echo format_cnpj($empresa->cnpj); ?></p>
                </div>

                <!-- Licitação -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-gavel mr-2 text-blue-400"></i>
                        Licitação
                    </h3>
                    <p class="text-sm text-white font-semibold"><?php echo truncate_text($licitacao->titulo, 60); ?></p>
                    <p class="text-xs text-gray-400 mt-2"><?php echo $licitacao->orgao_nome; ?></p>
                    
                    <!-- Valor da Licitação -->
                    <?php if (!empty($licitacao->valor_estimado) && $licitacao->valor_estimado > 0): ?>
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-xs text-gray-400">Valor Estimado</p>
                        <p class="text-xl font-bold text-green-400 mt-1">
                            R$ <?php echo number_format($licitacao->valor_estimado, 2, ',', '.'); ?>
                        </p>
                        <?php if ($licitacao->valor_estimado >= 1000000): ?>
                        <p class="text-xs text-gray-500 mt-1">
                            <?php echo number_format($licitacao->valor_estimado / 1000000, 2, ',', '.'); ?> milhões
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($match): ?>
                    <div class="mt-4 pt-4 border-t border-gray-700">
                        <p class="text-xs text-gray-400">Score de Compatibilidade</p>
                        <div class="flex items-center mt-2">
                            <div class="flex-1 h-2 bg-gray-700 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-500 to-green-400" style="width: <?php echo $match->score_total ?? 0; ?>%"></div>
                            </div>
                            <span class="ml-3 text-sm font-bold text-green-400"><?php echo number_format($match->score_total ?? 0, 1); ?>%</span>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Ações Rápidas -->
                <div class="bg-gray-800/50 backdrop-blur-sm rounded-2xl border border-gray-700 shadow-xl p-6">
                    <h3 class="text-lg font-bold text-white mb-4">Ações</h3>
                    <div class="space-y-2">
                        <?php if ($proposta): ?>
                        <a href="<?php echo base_url('admin/proposta/preview/' . $proposta->id); ?>" target="_blank"
                           class="w-full px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition-colors text-sm text-center block">
                            <i class="fas fa-eye mr-2"></i>Preview
                        </a>
                        <a href="<?php echo base_url('admin/proposta/exportar/pdf/' . $proposta->id); ?>"
                           class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors text-sm text-center block">
                            <i class="fas fa-file-pdf mr-2"></i>Exportar PDF
                        </a>
                        <a href="<?php echo base_url('admin/proposta/exportar/docx/' . $proposta->id); ?>"
                           class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors text-sm text-center block">
                            <i class="fas fa-file-word mr-2"></i>Exportar DOCX
                        </a>
                        <?php endif; ?>
                        <a href="<?php echo base_url('admin/propostas'); ?>"
                           class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition-colors text-sm text-center block">
                            <i class="fas fa-times mr-2"></i>Cancelar
                        </a>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>

<script>
    window.propostaFormData = function() {
        return {
            loading: false,
            matchId: '<?php echo $match->id ?? $proposta->match_id ?? ''; ?>',

            init() {
                console.log('✅ Alpine component inicializado!');
            },

            async gerarIA() {
                console.log('🤖 Função gerarIA() chamada!');

                if (this.loading) {
                    console.log('⏳ Já está gerando...');
                    return;
                }

                if (!confirm('Deseja gerar o conteúdo da proposta usando Inteligência Artificial?\n\nIsso pode levar alguns segundos.')) {
                    console.log('❌ Usuário cancelou');
                    return;
                }

                console.log('📋 Match ID:', this.matchId);

                if (!this.matchId) {
                    alert('❌ Match não encontrado!');
                    console.error('❌ Match ID vazio!');
                    return;
                }

                this.loading = true;
                console.log('⚡ Iniciando geração com IA para match:', this.matchId);

                try {
                    const url = '<?php echo base_url('admin/proposta/gerar_ia/'); ?>' + this.matchId;
                    console.log('URL:', url);

                    const response = await fetch(url, {
                        method: 'GET',
                        headers: { 'Accept': 'application/json' }
                    });

                    console.log('Response status:', response.status);

                    const text = await response.text();
                    console.log('Response text:', text.substring(0, 100));

                    let data;
                    try {
                        data = JSON.parse(text);
                    } catch (e) {
                        console.error('Erro ao fazer parse do JSON:', e);
                        alert('❌ Erro: Resposta inválida do servidor.\n\n' + text.substring(0, 200));
                        this.loading = false;
                        return;
                    }

                    if (data.success) {
                        alert('✅ ' + data.message);
                        window.location.href = '<?php echo base_url('admin/proposta/editar/'); ?>' + data.proposta_id;
                    } else {
                        alert('❌ ' + (data.message || 'Erro ao gerar proposta'));
                        this.loading = false;
                    }
                } catch (error) {
                    console.error('Erro:', error);
                    alert('❌ Erro ao gerar proposta: ' + error.message);
                    this.loading = false;
                }
            }
        }
    };

    console.log('✅ Função propostaFormData disponível no escopo global');
</script>

<script src="https://cdn.tiny.cloud/1/hfofmtfpp0gi57e6pusrep3fg46pks9j9oa9elmentmojc3a/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    // Inicializar TinyMCE
    tinymce.init({
        selector: '#editor',
        height: 600,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic underline strikethrough | ' +
                 'alignleft aligncenter alignright alignjustify | ' +
                 'bullist numlist outdent indent | forecolor backcolor | ' +
                 'table link image | removeformat code fullscreen',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
        skin: 'oxide-dark',
        content_css: 'dark',
        language: 'pt_BR',
        branding: false,
        promotion: false,
        setup: function(editor) {
            editor.on('init', function() {
                console.log('✅ TinyMCE inicializado com sucesso!');
            });
        }
    });
</script>
