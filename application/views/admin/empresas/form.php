<div x-data="empresaForm()" x-init="init()" x-cloak>
    <form method="post" action="<?php echo base_url('admin/empresa_salvar'); ?>" enctype="multipart/form-data" @submit.prevent="submitForm()">
        <?php if(isset($empresa) && $empresa): ?>
            <input type="hidden" name="id" value="<?php echo $empresa->id; ?>">
        <?php endif; ?>
        
        <!-- Progress Bar -->
        <div class="glass rounded-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center" :class="index < steps.length - 1 ? 'flex-1' : ''">
                        <div class="flex items-center">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full transition-all"
                                 :class="currentStep > index ? 'bg-green-500 text-white' : (currentStep === index ? 'bg-primary-500 text-white neon-glow' : 'bg-dark-700 text-gray-400')">
                                <template x-if="currentStep > index">
                                    <i class="fas fa-check"></i>
                                </template>
                                <template x-if="currentStep <= index">
                                    <span x-text="index + 1"></span>
                                </template>
                            </div>
                            <div class="ml-3 hidden md:block">
                                <p class="text-sm font-medium" :class="currentStep >= index ? 'text-white' : 'text-gray-400'" x-text="step.title"></p>
                                <p class="text-xs text-gray-500" x-text="step.subtitle"></p>
                            </div>
                        </div>
                        <template x-if="index < steps.length - 1">
                            <div class="mx-4 h-1 flex-1 rounded-full" 
                                 :class="currentStep > index ? 'bg-green-500' : 'bg-dark-700'"></div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Step 1: Dados Básicos -->
        <div x-show="currentStep === 0" x-transition class="glass rounded-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-building mr-2 text-primary-400"></i>
                Dados Básicos da Empresa
            </h3>
            
            <!-- Logo da Empresa -->
            <div class="mb-6 p-4 bg-dark-800/50 rounded-xl border border-dark-700">
                <label class="block text-sm font-medium text-gray-300 mb-3">Logo da Empresa</label>
                <div class="flex items-center gap-6">
                    <!-- Preview do Logo -->
                    <div class="relative">
                        <div class="w-24 h-24 rounded-xl overflow-hidden bg-gradient-to-br from-primary-500/20 to-purple-500/20 border-2 border-dashed border-dark-600 flex items-center justify-center"
                             :class="logoPreview || logoAtual ? 'border-solid border-primary-500/50' : ''">
                            <template x-if="logoPreview">
                                <img :src="logoPreview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview && logoAtual">
                                <img :src="logoAtual" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!logoPreview && !logoAtual">
                                <div class="text-center">
                                    <i class="fas fa-building text-3xl text-gray-600"></i>
                                    <p class="text-xs text-gray-500 mt-1">Sem logo</p>
                                </div>
                            </template>
                        </div>
                        <!-- Botão remover -->
                        <button type="button" x-show="logoPreview || logoAtual" @click="removerLogo()"
                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 hover:bg-red-600 rounded-full flex items-center justify-center text-white text-xs transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <!-- Upload -->
                    <div class="flex-1">
                        <input type="file" name="logo" id="logo_input" accept="image/*" class="hidden" @change="handleLogoChange($event)">
                        <input type="hidden" name="remover_logo" x-model="removerLogoFlag">
                        <label for="logo_input" 
                               class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg cursor-pointer transition-colors">
                            <i class="fas fa-upload"></i>
                            <span>Escolher Imagem</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-2">PNG, JPG ou GIF. Máximo 2MB. Recomendado: 200x200px</p>
                    </div>
                </div>
            </div>
            
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Nome Fantasia <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="nome" x-model="formData.nome"
                           :class="formData.nome ? 'border-dark-700' : 'border-yellow-500/50'"
                           class="w-full rounded-lg bg-dark-800 border px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Ex: Empresa XYZ">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Razão Social <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="razao_social" x-model="formData.razao_social"
                           :class="formData.razao_social ? 'border-dark-700' : 'border-yellow-500/50'"
                           class="w-full rounded-lg bg-dark-800 border px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Ex: Empresa XYZ Ltda">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        CNPJ <span class="text-red-400">*</span>
                    </label>
                    <div class="flex gap-2">
                        <input type="text" name="cnpj" x-model="formData.cnpj"
                               x-mask="99.999.999/9999-99"
                               @input="if (formData.cnpj.replace(/\D/g, '').length === 14 && !buscandoCNPJ) buscarCNPJ()"
                               :class="formData.cnpj && formData.cnpj.replace(/\D/g, '').length === 14 ? 'border-dark-700' : 'border-yellow-500/50'"
                               class="flex-1 rounded-lg bg-dark-800 border px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none font-mono"
                               placeholder="00.000.000/0000-00">
                        <button type="button" @click="buscarCNPJ()" :disabled="buscandoCNPJ || formData.cnpj.replace(/\D/g, '').length !== 14"
                                class="px-4 py-2 rounded-lg bg-primary-600 hover:bg-primary-700 text-white transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                :class="{'animate-pulse': buscandoCNPJ}">
                            <i class="fas" :class="buscandoCNPJ ? 'fa-spinner fa-spin' : 'fa-search'"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Porte <span class="text-red-400">*</span>
                    </label>
                    <select name="porte" x-model="formData.porte"
                            :class="formData.porte ? 'border-dark-700' : 'border-yellow-500/50'"
                            class="w-full rounded-lg bg-dark-800 border px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                        <option value="">Selecione...</option>
                        <option value="MEI">MEI - Microempreendedor Individual</option>
                        <option value="ME">ME - Microempresa</option>
                        <option value="EPP">EPP - Empresa de Pequeno Porte</option>
                        <option value="MEDIA">Média Empresa</option>
                        <option value="GRANDE">Grande Empresa</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Inscrição Estadual</label>
                    <input type="text" name="inscricao_estadual" x-model="formData.inscricao_estadual"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Inscrição Municipal</label>
                    <input type="text" name="inscricao_municipal" x-model="formData.inscricao_municipal"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Natureza Jurídica</label>
                    <input type="text" name="natureza_juridica" x-model="formData.natureza_juridica"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Ex: Sociedade Empresária Limitada">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">CNAE Principal</label>
                    <input type="text" name="cnae_principal" x-model="formData.cnae_principal"
                           x-mask="9999-9/99"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none font-mono"
                           placeholder="0000-0/00">
                </div>
            </div>
        </div>

        <!-- Step 2: Contato e Endereço -->
        <div x-show="currentStep === 1" x-transition class="glass rounded-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-address-book mr-2 text-primary-400"></i>
                Contato e Endereço
            </h3>
            
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Email</label>
                    <input type="email" name="email" x-model="formData.email"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="contato@empresa.com.br">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Telefone</label>
                    <input type="text" name="telefone" x-model="formData.telefone"
                           x-mask="(99) 99999-9999"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none font-mono"
                           placeholder="(00) 00000-0000">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Website</label>
                    <input type="url" name="site" x-model="formData.site"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="https://www.empresa.com.br">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">CEP</label>
                    <div class="flex space-x-2">
                        <input type="text" name="cep" x-model="formData.cep"
                               x-mask="99999-999"
                               @blur="buscarCep()"
                               class="flex-1 rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none font-mono"
                               placeholder="00000-000">
                        <button type="button" @click="buscarCep()" 
                                class="rounded-lg bg-primary-600 px-4 py-2 text-white hover:bg-primary-700 transition-colors">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">UF</label>
                    <select name="uf" x-model="formData.uf"
                            class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white focus:border-primary-500 focus:outline-none">
                        <option value="">Selecione...</option>
                        <?php 
                        $ufs = ['AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA', 'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN', 'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'];
                        foreach ($ufs as $uf): 
                        ?>
                            <option value="<?php echo $uf; ?>"><?php echo $uf; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Logradouro</label>
                    <input type="text" name="logradouro" x-model="formData.logradouro"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Rua, Avenida, etc.">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número</label>
                    <input type="text" name="numero" x-model="formData.numero"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Complemento</label>
                    <input type="text" name="complemento" x-model="formData.complemento"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Sala, Andar, etc.">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bairro</label>
                    <input type="text" name="bairro" x-model="formData.bairro"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Cidade</label>
                    <input type="text" name="cidade" x-model="formData.cidade"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none">
                </div>
            </div>
        </div>

        <!-- Step 3: Dados Financeiros e Segmentos -->
        <div x-show="currentStep === 2" x-transition class="glass rounded-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-chart-line mr-2 text-primary-400"></i>
                Dados Financeiros e Atuação
            </h3>
            
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Faturamento Anual (R$)</label>
                    <input type="number" step="0.01" name="faturamento_anual" x-model="formData.faturamento_anual"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="0.00">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Capital Social (R$)</label>
                    <input type="number" step="0.01" name="capital_social" x-model="formData.capital_social"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="0.00">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Segmentos de Atuação</label>
                    <div class="space-y-2 mb-2">
                        <template x-for="(seg, index) in formData.segmentos" :key="index">
                            <div class="flex items-center space-x-2">
                                <input type="text" :name="'segmentos[' + index + ']'" x-model="formData.segmentos[index]"
                                       class="flex-1 rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                                       placeholder="Ex: Construção Civil">
                                <button type="button" @click="formData.segmentos.splice(index, 1)"
                                        class="rounded-lg bg-red-600 px-3 py-2 text-white hover:bg-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="formData.segmentos.push('')"
                            class="w-full rounded-lg border-2 border-dashed border-dark-700 px-4 py-2 text-gray-400 hover:border-primary-500 hover:text-primary-400 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Adicionar Segmento
                    </button>
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Certificações</label>
                    <div class="space-y-2 mb-2">
                        <template x-for="(cert, index) in formData.certificacoes" :key="index">
                            <div class="flex items-center space-x-2">
                                <input type="text" :name="'certificacoes[' + index + ']'" x-model="formData.certificacoes[index]"
                                       class="flex-1 rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                                       placeholder="Ex: ISO 9001">
                                <button type="button" @click="formData.certificacoes.splice(index, 1)"
                                        class="rounded-lg bg-red-600 px-3 py-2 text-white hover:bg-red-700">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </template>
                    </div>
                    <button type="button" @click="formData.certificacoes.push('')"
                            class="w-full rounded-lg border-2 border-dashed border-dark-700 px-4 py-2 text-gray-400 hover:border-primary-500 hover:text-primary-400 transition-colors">
                        <i class="fas fa-plus mr-2"></i>Adicionar Certificação
                    </button>
                </div>
                
                <div class="md:col-span-2">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input type="checkbox" name="ativo" x-model="formData.ativo" value="1"
                               class="w-5 h-5 rounded bg-dark-800 border-dark-700 text-primary-600 focus:ring-primary-500">
                        <span class="text-sm font-medium text-gray-300">Empresa Ativa</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Step 4: Perfil da Empresa -->
        <div x-show="currentStep === 3" x-transition class="glass rounded-xl p-6 mb-6">
            <h3 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-file-alt mr-2 text-primary-400"></i>
                Perfil e Experiência da Empresa
            </h3>
            
            <div class="grid gap-6 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Currículo Completo da Empresa
                        <span class="text-xs text-gray-500 ml-2">(Descreva a história, principais projetos, diferenciais)</span>
                    </label>
                    <textarea name="curriculo_completo" x-model="formData.curriculo_completo" rows="8"
                              class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                              placeholder="Descreva a trajetória da empresa, principais projetos executados, cases de sucesso, diferenciais competitivos..."></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Anos de Experiência</label>
                    <input type="number" name="anos_experiencia" x-model="formData.anos_experiencia" min="0"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Ex: 10">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Número de Projetos Realizados</label>
                    <input type="number" name="numero_projetos_realizados" x-model="formData.numero_projetos_realizados" min="0"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Ex: 50">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Valor Total de Contratos Executados (R$)</label>
                    <input type="number" step="0.01" name="valor_total_contratos" x-model="formData.valor_total_contratos" min="0"
                           class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="0.00">
                    <p class="text-xs text-gray-500 mt-1">Soma total de todos os contratos já executados pela empresa</p>
                </div>
            </div>
        </div>

        <!-- Step 5: Keywords e Monitoramento -->
        <div x-show="currentStep === 4" x-transition class="space-y-6">
            <!-- Seção de Keywords -->
            <div class="glass rounded-xl p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-white">
                        <i class="fas fa-tags mr-2 text-primary-400"></i>
                        Palavras-chave para Monitoramento
                    </h3>
                    <button type="button" @click="gerarKeywordsIA()" 
                            :disabled="gerandoKeywords"
                            class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-500 hover:to-blue-500 text-white rounded-lg transition-all transform hover:scale-105 disabled:opacity-50 disabled:transform-none">
                        <i :class="gerandoKeywords ? 'fa-spinner fa-spin' : 'fa-magic'" class="fas mr-2"></i>
                        <span x-text="gerandoKeywords ? 'Gerando...' : 'Gerar com IA'"></span>
                    </button>
                </div>
                
                <div class="mb-4 p-4 bg-blue-900/20 border border-blue-500/30 rounded-xl">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-info-circle text-blue-400 mt-1"></i>
                        <div class="text-sm text-blue-200">
                            <p class="font-medium mb-1">Como funciona?</p>
                            <p class="text-blue-300/80">
                                As palavras-chave são usadas para encontrar automaticamente licitações compatíveis com sua empresa.
                                O sistema monitora continuamente novas licitações e cria alertas quando encontra matches.
                                Quanto mais específicas as keywords, melhores serão os resultados.
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Tags de Keywords -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Keywords Configuradas
                        <span class="text-xs text-gray-500 ml-2">(<span x-text="formData.keywords.length"></span> palavras)</span>
                    </label>
                    
                    <div class="flex flex-wrap gap-2 p-4 bg-dark-800/50 rounded-xl border border-dark-700 min-h-[100px]">
                        <template x-for="(keyword, index) in formData.keywords" :key="index">
                            <span class="inline-flex items-center gap-1 px-3 py-1.5 bg-primary-600/20 text-primary-300 rounded-full text-sm border border-primary-500/30">
                                <span x-text="keyword"></span>
                                <button type="button" @click="removerKeyword(index)" class="ml-1 hover:text-red-400 transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
                            </span>
                        </template>
                        <template x-if="formData.keywords.length === 0">
                            <span class="text-gray-500 text-sm">Nenhuma keyword configurada. Clique em "Gerar com IA" ou adicione manualmente.</span>
                        </template>
                    </div>
                    <input type="hidden" name="keywords" :value="JSON.stringify(formData.keywords)">
                </div>
                
                <!-- Adicionar manualmente -->
                <div class="flex gap-2">
                    <input type="text" x-model="novaKeyword" @keydown.enter.prevent="adicionarKeyword()"
                           class="flex-1 rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                           placeholder="Digite uma nova keyword e pressione Enter...">
                    <button type="button" @click="adicionarKeyword()"
                            class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                
                <!-- Sugestões de Keywords -->
                <div x-show="sugestoesKeywords.length > 0" class="mt-4">
                    <label class="block text-xs font-medium text-gray-400 mb-2">Sugestões (clique para adicionar):</label>
                    <div class="flex flex-wrap gap-2">
                        <template x-for="sugestao in sugestoesKeywords" :key="sugestao">
                            <button type="button" @click="adicionarSugestao(sugestao)"
                                    class="px-3 py-1 bg-dark-700/50 hover:bg-primary-600/30 text-gray-400 hover:text-primary-300 rounded-full text-xs border border-dark-600 hover:border-primary-500/30 transition-all">
                                + <span x-text="sugestao"></span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>
            
            <!-- Configurações de Monitoramento -->
            <div class="glass rounded-xl p-6">
                <h3 class="text-xl font-bold text-white mb-6">
                    <i class="fas fa-bell mr-2 text-yellow-400"></i>
                    Configurações de Monitoramento
                </h3>
                
                <div class="grid gap-6 md:grid-cols-2">
                    <!-- Ativar Monitoramento -->
                    <div class="md:col-span-2">
                        <label class="flex items-center justify-between p-4 bg-dark-800/50 rounded-xl border border-dark-700 cursor-pointer hover:border-primary-500/50 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-satellite-dish text-green-400"></i>
                                </div>
                                <div>
                                    <span class="text-white font-medium">Monitoramento Ativo</span>
                                    <p class="text-xs text-gray-500">Receba alertas automáticos quando novas licitações compatíveis forem encontradas</p>
                                </div>
                            </div>
                            <input type="checkbox" name="monitoramento_ativo" x-model="formData.monitoramento_ativo" value="1"
                                   class="w-5 h-5 rounded bg-dark-800 border-dark-700 text-green-500 focus:ring-green-500">
                        </label>
                    </div>
                    
                    <!-- UFs de Interesse -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i>
                            UFs de Interesse
                            <span class="text-xs text-gray-500 ml-1">(deixe vazio para todas)</span>
                        </label>
                        <div class="grid grid-cols-9 gap-2">
                            <?php 
                            $ufs = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
                            foreach ($ufs as $uf): ?>
                            <label class="flex items-center justify-center p-2 bg-dark-800/50 rounded-lg border border-dark-700 cursor-pointer hover:border-primary-500/50 transition-colors"
                                   :class="formData.ufs_interesse.includes('<?php echo $uf; ?>') ? 'bg-primary-600/20 border-primary-500' : ''">
                                <input type="checkbox" value="<?php echo $uf; ?>" 
                                       @change="toggleUF('<?php echo $uf; ?>')"
                                       :checked="formData.ufs_interesse.includes('<?php echo $uf; ?>')"
                                       class="hidden">
                                <span class="text-sm" :class="formData.ufs_interesse.includes('<?php echo $uf; ?>') ? 'text-primary-300 font-medium' : 'text-gray-400'"><?php echo $uf; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="ufs_interesse" :value="JSON.stringify(formData.ufs_interesse)">
                    </div>
                    
                    <!-- Faixa de Valor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-dollar-sign mr-1 text-gray-500"></i>
                            Valor Mínimo (R$)
                        </label>
                        <input type="number" step="0.01" name="valor_minimo_monitoramento" x-model="formData.valor_minimo_monitoramento" min="0"
                               class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                               placeholder="0.00">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-dollar-sign mr-1 text-gray-500"></i>
                            Valor Máximo (R$)
                        </label>
                        <input type="number" step="0.01" name="valor_maximo_monitoramento" x-model="formData.valor_maximo_monitoramento" min="0"
                               class="w-full rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none"
                               placeholder="Sem limite">
                    </div>
                    
                    <!-- Score Mínimo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-percentage mr-1 text-gray-500"></i>
                            Score Mínimo para Alerta
                        </label>
                        <div class="flex items-center gap-4">
                            <input type="range" name="score_minimo_alerta" x-model="formData.score_minimo_alerta" min="0" max="100" step="5"
                                   class="flex-1 h-2 bg-dark-700 rounded-lg appearance-none cursor-pointer accent-primary-500">
                            <span class="text-white font-medium w-12 text-center" x-text="formData.score_minimo_alerta + '%'"></span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Alertas com score abaixo deste valor não serão exibidos</p>
                    </div>
                    
                    <!-- Alertas -->
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">
                            <i class="fas fa-envelope mr-1 text-gray-500"></i>
                            Tipo de Alertas
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="alerta_sistema" x-model="formData.alerta_sistema" value="1"
                                       class="w-4 h-4 rounded bg-dark-800 border-dark-700 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-300">Alertas no sistema</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="alerta_email" x-model="formData.alerta_email" value="1"
                                       class="w-4 h-4 rounded bg-dark-800 border-dark-700 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-gray-300">Alertas por email</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex justify-between">
            <button type="button" @click="prevStep()" x-show="currentStep > 0"
                    class="rounded-lg bg-dark-800 border border-dark-700 px-6 py-3 text-white hover:bg-dark-700 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Anterior
            </button>
            
            <div class="flex space-x-3 ml-auto">
                <a href="<?php echo base_url('admin/empresas'); ?>" 
                   class="rounded-lg bg-dark-800 border border-dark-700 px-6 py-3 text-white hover:bg-dark-700 transition-colors">
                    Cancelar
                </a>
                
                <button type="button" @click="nextStep()" x-show="currentStep < steps.length - 1"
                        class="rounded-lg bg-primary-600 px-6 py-3 text-white hover:bg-primary-700 transition-colors neon-glow">
                    Próximo <i class="fas fa-arrow-right ml-2"></i>
                </button>
                
                <button type="submit" x-show="currentStep === steps.length - 1"
                        class="rounded-lg bg-green-600 px-6 py-3 text-white hover:bg-green-700 transition-colors neon-glow">
                    <i class="fas fa-save mr-2"></i>Salvar Empresa
                </button>
            </div>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/imask@6.4.3/dist/imask.min.js"></script>

<script>
function empresaForm() {
    return {
        currentStep: 0,
        buscandoCNPJ: false,
        logoPreview: null,
        logoAtual: <?php echo json_encode(isset($empresa) && $empresa && $empresa->logo ? base_url('uploads/logos/' . $empresa->logo) : ''); ?>,
        removerLogoFlag: '',
        steps: [
            { title: 'Dados Básicos', subtitle: 'Informações principais' },
            { title: 'Contato e Endereço', subtitle: 'Localização e contato' },
            { title: 'Financeiro e Atuação', subtitle: 'Dados complementares' },
            { title: 'Perfil e Experiência', subtitle: 'Currículo da empresa' },
            { title: 'Keywords e Monitoramento', subtitle: 'Alertas automáticos' }
        ],
        formData: {
            nome: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->nome : ''); ?>,
            razao_social: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->razao_social : ''); ?>,
            cnpj: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->cnpj : ''); ?>,
            inscricao_estadual: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->inscricao_estadual : ''); ?>,
            inscricao_municipal: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->inscricao_municipal : ''); ?>,
            email: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->email : ''); ?>,
            telefone: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->telefone : ''); ?>,
            site: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->site : ''); ?>,
            cep: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->cep : ''); ?>,
            logradouro: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->logradouro : ''); ?>,
            numero: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->numero : ''); ?>,
            complemento: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->complemento : ''); ?>,
            bairro: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->bairro : ''); ?>,
            cidade: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->cidade : ''); ?>,
            uf: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->uf : ''); ?>,
            porte: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->porte : ''); ?>,
            natureza_juridica: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->natureza_juridica : ''); ?>,
            cnae_principal: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->cnae_principal : ''); ?>,
            faturamento_anual: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->faturamento_anual : ''); ?>,
            capital_social: <?php echo json_encode(isset($empresa) && $empresa ? $empresa->capital_social : ''); ?>,
            segmentos: <?php echo isset($empresa) && $empresa && $empresa->segmentos ? json_encode($empresa->segmentos) : '[]'; ?>,
            certificacoes: <?php echo isset($empresa) && $empresa && $empresa->certificacoes ? json_encode($empresa->certificacoes) : '[]'; ?>,
            ativo: <?php echo isset($empresa) && $empresa ? ($empresa->ativo ? 'true' : 'false') : 'true'; ?>,
            curriculo_completo: <?php echo json_encode(isset($perfil) && $perfil ? $perfil->curriculo_completo : ''); ?>,
            anos_experiencia: <?php echo json_encode(isset($perfil) && $perfil ? $perfil->anos_experiencia : ''); ?>,
            numero_projetos_realizados: <?php echo json_encode(isset($perfil) && $perfil ? $perfil->numero_projetos_realizados : ''); ?>,
            valor_total_contratos: <?php echo json_encode(isset($perfil) && $perfil ? $perfil->valor_total_contratos : ''); ?>,
            // Keywords e Monitoramento
            keywords: <?php echo json_encode(isset($keywords) && is_array($keywords) ? $keywords : []); ?>,
            monitoramento_ativo: <?php echo isset($empresa) && $empresa && isset($empresa->monitoramento_ativo) ? ($empresa->monitoramento_ativo ? 'true' : 'false') : 'true'; ?>,
            ufs_interesse: <?php echo isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->ufs_interesse) ? (is_string($monitoramento_config->ufs_interesse) ? $monitoramento_config->ufs_interesse : json_encode($monitoramento_config->ufs_interesse)) : '[]'; ?>,
            valor_minimo_monitoramento: <?php echo json_encode(isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->valor_minimo) ? $monitoramento_config->valor_minimo : ''); ?>,
            valor_maximo_monitoramento: <?php echo json_encode(isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->valor_maximo) ? $monitoramento_config->valor_maximo : ''); ?>,
            score_minimo_alerta: <?php echo isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->score_minimo_alerta) ? $monitoramento_config->score_minimo_alerta : '50'; ?>,
            alerta_sistema: <?php echo isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->alerta_sistema) ? ($monitoramento_config->alerta_sistema ? 'true' : 'false') : 'true'; ?>,
            alerta_email: <?php echo isset($monitoramento_config) && $monitoramento_config && isset($monitoramento_config->alerta_email) ? ($monitoramento_config->alerta_email ? 'true' : 'false') : 'true'; ?>
        },
        novaKeyword: '',
        gerandoKeywords: false,
        sugestoesKeywords: [],
        
        init() {
            // Inicializa arrays vazios se necessário
            if (!Array.isArray(this.formData.segmentos) || this.formData.segmentos.length === 0) {
                this.formData.segmentos = [''];
            }
            if (!Array.isArray(this.formData.certificacoes) || this.formData.certificacoes.length === 0) {
                this.formData.certificacoes = [''];
            }
        },
        
        handleLogoChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            
            // Validar tipo
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                showToast('Tipo de arquivo não permitido. Use: JPG, PNG, GIF ou WEBP', 'error');
                event.target.value = '';
                return;
            }
            
            // Validar tamanho (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showToast('Arquivo muito grande. Máximo: 2MB', 'error');
                event.target.value = '';
                return;
            }
            
            // Criar preview
            const reader = new FileReader();
            reader.onload = (e) => {
                this.logoPreview = e.target.result;
                this.removerLogoFlag = '';
            };
            reader.readAsDataURL(file);
        },
        
        removerLogo() {
            this.logoPreview = null;
            this.logoAtual = null;
            this.removerLogoFlag = '1';
            document.getElementById('logo_input').value = '';
        },
        
        nextStep() {
            // Valida campos da etapa atual antes de avançar
            if (!this.validateCurrentStep()) {
                return;
            }
            
            if (this.currentStep < this.steps.length - 1) {
                this.currentStep++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        prevStep() {
            if (this.currentStep > 0) {
                this.currentStep--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        
        validateCurrentStep() {
            let isValid = true;
            let errorMessage = '';
            
            // Validação Etapa 1: Dados Básicos
            if (this.currentStep === 0) {
                if (!this.formData.nome || this.formData.nome.trim() === '') {
                    errorMessage = 'O campo Nome Fantasia é obrigatório';
                    isValid = false;
                } else if (!this.formData.razao_social || this.formData.razao_social.trim() === '') {
                    errorMessage = 'O campo Razão Social é obrigatório';
                    isValid = false;
                } else if (!this.formData.cnpj || this.formData.cnpj.trim() === '') {
                    errorMessage = 'O campo CNPJ é obrigatório';
                    isValid = false;
                } else if (this.formData.cnpj.replace(/\D/g, '').length !== 14) {
                    errorMessage = 'CNPJ inválido. Deve conter 14 dígitos';
                    isValid = false;
                } else if (!this.formData.porte || this.formData.porte === '') {
                    errorMessage = 'O campo Porte é obrigatório';
                    isValid = false;
                }
            }
            
            // Validação Etapa 2: Contato e Endereço
            if (this.currentStep === 1) {
                // Validação de email se preenchido
                if (this.formData.email && this.formData.email.trim() !== '') {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(this.formData.email)) {
                        errorMessage = 'Email inválido';
                        isValid = false;
                    }
                }
                
                // Validação de site se preenchido
                if (this.formData.site && this.formData.site.trim() !== '') {
                    const urlRegex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
                    if (!urlRegex.test(this.formData.site)) {
                        errorMessage = 'Website inválido. Use o formato: https://www.exemplo.com.br';
                        isValid = false;
                    }
                }
            }
            
            // Validação Etapa 3: Financeiro
            if (this.currentStep === 2) {
                // Validações opcionais para valores numéricos
                if (this.formData.faturamento_anual && this.formData.faturamento_anual < 0) {
                    errorMessage = 'Faturamento anual não pode ser negativo';
                    isValid = false;
                } else if (this.formData.capital_social && this.formData.capital_social < 0) {
                    errorMessage = 'Capital social não pode ser negativo';
                    isValid = false;
                }
            }
            
            if (!isValid) {
                showToast(errorMessage, 'error');
            }
            
            return isValid;
        },
        
        submitForm() {
            // Valida todos os campos antes de enviar
            let allValid = true;
            
            // Valida etapa 1
            this.currentStep = 0;
            if (!this.validateCurrentStep()) {
                showToast('Preencha corretamente os Dados Básicos', 'error');
                return;
            }
            
            // Valida etapa 2
            this.currentStep = 1;
            if (!this.validateCurrentStep()) {
                showToast('Verifique os dados de Contato e Endereço', 'error');
                return;
            }
            
            // Valida etapa 3
            this.currentStep = 2;
            if (!this.validateCurrentStep()) {
                showToast('Verifique os Dados Financeiros', 'error');
                return;
            }
            
            // Valida etapa 4 (não tem validação obrigatória, apenas avança)
            this.currentStep = 3;
            
            // Se tudo válido, prepara e envia o formulário
            const form = document.querySelector('form');
            
            // Adiciona campos hidden para arrays (segmentos e certificações)
            // Remove inputs anteriores se existirem
            form.querySelectorAll('input[name^="segmentos"]').forEach(el => el.remove());
            form.querySelectorAll('input[name^="certificacoes"]').forEach(el => el.remove());
            
            // Adiciona segmentos
            if (Array.isArray(this.formData.segmentos)) {
                this.formData.segmentos.forEach((seg, index) => {
                    if (seg && seg.trim() !== '') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `segmentos[${index}]`;
                        input.value = seg;
                        form.appendChild(input);
                    }
                });
            }
            
            // Adiciona certificações
            if (Array.isArray(this.formData.certificacoes)) {
                this.formData.certificacoes.forEach((cert, index) => {
                    if (cert && cert.trim() !== '') {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = `certificacoes[${index}]`;
                        input.value = cert;
                        form.appendChild(input);
                    }
                });
            }
            
            // Remove prevent default e submete
            form.onsubmit = null;
            form.submit();
        },
        
        async buscarCep() {
            const cep = this.formData.cep.replace(/\D/g, '');
            
            if (cep.length !== 8) {
                showToast('CEP deve conter 8 dígitos', 'warning');
                return;
            }
            
            try {
                showToast('Buscando CEP...', 'info');
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                
                if (!data.erro) {
                    this.formData.logradouro = data.logradouro;
                    this.formData.bairro = data.bairro;
                    this.formData.cidade = data.localidade;
                    this.formData.uf = data.uf;
                    showToast('CEP encontrado!', 'success');
                } else {
                    showToast('CEP não encontrado!', 'error');
                }
            } catch (error) {
                showToast('Erro ao buscar CEP. Tente novamente.', 'error');
            }
        },
        
        async buscarCNPJ() {
            const cnpj = this.formData.cnpj.replace(/\D/g, '');
            
            if (cnpj.length !== 14) {
                showToast('CNPJ deve conter 14 dígitos', 'warning');
                return;
            }
            
            this.buscandoCNPJ = true;
            
            try {
                showToast('Buscando dados da empresa...', 'info');
                
                const formData = new FormData();
                formData.append('cnpj', cnpj);
                
                const response = await fetch('<?php echo base_url('admin/api/buscar-cnpj'); ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success && result.data) {
                    const data = result.data;
                    
                    // Preenche dados básicos
                    this.formData.razao_social = data.razao_social || this.formData.razao_social;
                    this.formData.nome = data.nome || this.formData.nome;
                    this.formData.porte = data.porte || this.formData.porte;
                    this.formData.natureza_juridica = data.natureza_juridica || this.formData.natureza_juridica;
                    this.formData.cnae_principal = data.cnae_principal || this.formData.cnae_principal;
                    this.formData.capital_social = data.capital_social || this.formData.capital_social;
                    this.formData.inscricao_estadual = data.inscricao_estadual || this.formData.inscricao_estadual;
                    this.formData.inscricao_municipal = data.inscricao_municipal || this.formData.inscricao_municipal;
                    
                    // Preenche contato
                    this.formData.email = data.email || this.formData.email;
                    this.formData.telefone = data.telefone || this.formData.telefone;
                    
                    // Preenche endereço
                    this.formData.cep = data.cep || this.formData.cep;
                    this.formData.logradouro = data.logradouro || this.formData.logradouro;
                    this.formData.numero = data.numero || this.formData.numero;
                    this.formData.complemento = data.complemento || this.formData.complemento;
                    this.formData.bairro = data.bairro || this.formData.bairro;
                    this.formData.cidade = data.cidade || this.formData.cidade;
                    this.formData.uf = data.uf || this.formData.uf;
                    
                    showToast('Dados da empresa carregados com sucesso!', 'success');
                } else {
                    showToast(result.message || 'Erro ao buscar dados do CNPJ', 'error');
                }
            } catch (error) {
                console.error('Erro ao buscar CNPJ:', error);
                showToast('Erro ao buscar dados do CNPJ. Tente novamente.', 'error');
            } finally {
                this.buscandoCNPJ = false;
            }
        },
        
        // ========== FUNÇÕES DE KEYWORDS E MONITORAMENTO ==========
        
        adicionarKeyword() {
            const keyword = this.novaKeyword.trim().toLowerCase();
            if (keyword && !this.formData.keywords.includes(keyword)) {
                this.formData.keywords.push(keyword);
                this.novaKeyword = '';
                showToast('Keyword adicionada!', 'success');
            } else if (this.formData.keywords.includes(keyword)) {
                showToast('Keyword já existe!', 'warning');
            }
        },
        
        removerKeyword(index) {
            this.formData.keywords.splice(index, 1);
        },
        
        adicionarSugestao(sugestao) {
            const keyword = sugestao.trim().toLowerCase();
            if (!this.formData.keywords.includes(keyword)) {
                this.formData.keywords.push(keyword);
                // Remove da lista de sugestões
                this.sugestoesKeywords = this.sugestoesKeywords.filter(s => s !== sugestao);
                showToast('Keyword adicionada!', 'success');
            }
        },
        
        toggleUF(uf) {
            const index = this.formData.ufs_interesse.indexOf(uf);
            if (index > -1) {
                this.formData.ufs_interesse.splice(index, 1);
            } else {
                this.formData.ufs_interesse.push(uf);
            }
        },
        
        async gerarKeywordsIA() {
            if (this.gerandoKeywords) return;
            
            // Validar dados básicos
            if (!this.formData.nome && !this.formData.razao_social) {
                showToast('Preencha pelo menos o nome ou razão social da empresa', 'warning');
                return;
            }
            
            this.gerandoKeywords = true;
            showToast('Gerando keywords com IA...', 'info');
            
            try {
                const response = await fetch('<?php echo base_url("admin/gerar_keywords_ia"); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        nome: this.formData.nome,
                        razao_social: this.formData.razao_social,
                        cnae_principal: this.formData.cnae_principal,
                        segmentos: this.formData.segmentos.filter(s => s && s.trim()),
                        certificacoes: this.formData.certificacoes.filter(c => c && c.trim()),
                        porte: this.formData.porte,
                        uf: this.formData.uf,
                        cidade: this.formData.cidade,
                        curriculo: this.formData.curriculo_completo
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Adicionar keywords geradas (evitando duplicatas)
                    const novasKeywords = result.keywords || [];
                    novasKeywords.forEach(kw => {
                        const keyword = kw.trim().toLowerCase();
                        if (keyword && !this.formData.keywords.includes(keyword)) {
                            this.formData.keywords.push(keyword);
                        }
                    });
                    
                    // Guardar sugestões extras
                    this.sugestoesKeywords = (result.sugestoes || []).filter(s => !this.formData.keywords.includes(s.toLowerCase()));
                    
                    showToast(`${novasKeywords.length} keywords geradas com sucesso!`, 'success');
                } else {
                    showToast(result.message || 'Erro ao gerar keywords', 'error');
                }
            } catch (error) {
                console.error('Erro ao gerar keywords:', error);
                showToast('Erro ao gerar keywords. Tente novamente.', 'error');
            } finally {
                this.gerandoKeywords = false;
            }
        }
    }
}

window.empresaForm = empresaForm;
</script>
