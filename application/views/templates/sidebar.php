    <!-- Sidebar -->
    <aside 
        x-show="sidebarOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-300"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed left-0 top-0 z-40 h-screen w-72 glass border-r border-dark-700"
        x-cloak
    >
        <!-- Logo -->
        <div class="flex h-20 items-center justify-between border-b border-dark-700 px-6">
            <div class="flex items-center space-x-3">
                <div class="flex h-12 w-12 items-center justify-center">
                    <img src="<?php echo base_url('logo.png'); ?>" alt="AllMight Logo" class="w-12 h-12 object-contain">
                </div>
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">
                        AllMight
                    </h1>
                    <p class="text-xs text-gray-400">Sistema Inteligente</p>
                </div>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-gray-400 hover:text-white">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1 px-3 py-4 overflow-y-auto">
            <!-- Dashboard -->
            <a href="<?php echo base_url('admin/dashboard'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'dashboard' ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-chart-line w-5"></i>
                <span class="font-medium">Dashboard</span>
            </a>

            <!-- Licitações -->
            <div x-data="{ open: <?php echo in_array($this->uri->segment(2), ['licitacoes', 'analises']) ? 'true' : 'false'; ?> }">
                <button @click="open = !open" 
                        class="flex w-full items-center justify-between rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white">
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-gavel w-5"></i>
                        <span class="font-medium">Licitações</span>
                    </div>
                    <i class="fas fa-chevron-down transition-transform" :class="open ? 'rotate-180' : ''"></i>
                </button>
                <div x-show="open" x-cloak class="ml-8 mt-2 space-y-1">
                    <a href="<?php echo base_url('admin/licitacoes'); ?>" 
                       class="block rounded-lg px-4 py-2 text-sm text-gray-400 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'licitacoes' && $this->uri->segment(3) == '' ? 'bg-dark-800 text-white' : ''; ?>">
                        Todas as Licitações
                    </a>
                    <a href="<?php echo base_url('admin/licitacoes/abertas'); ?>" 
                       class="block rounded-lg px-4 py-2 text-sm text-gray-400 hover:bg-dark-800 hover:text-white">
                        Abertas
                    </a>
                    <a href="<?php echo base_url('admin/analises'); ?>" 
                       class="block rounded-lg px-4 py-2 text-sm text-gray-400 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'analises' ? 'bg-dark-800 text-white' : ''; ?>">
                        Análises IA
                    </a>
                </div>
            </div>

            <!-- Monitoramento -->
            <a href="<?php echo base_url('admin/monitoramento'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'monitoramento' ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-bell w-5"></i>
                <span class="font-medium">Monitoramento</span>
                <?php
                // Buscar contador de alertas novos
                $CI =& get_instance();
                $CI->load->model('Alerta_model');
                try {
                    $alertas_novos = $CI->Alerta_model->count_novos();
                    if ($alertas_novos > 0):
                    ?>
                    <span class="ml-auto rounded-full bg-green-500 px-2 py-1 text-xs font-bold"><?php echo $alertas_novos; ?></span>
                    <?php endif;
                } catch (Exception $e) {}
                ?>
            </a>

            <!-- Matches -->
            <a href="<?php echo base_url('admin/matches'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'matches' || $this->uri->segment(2) == 'match' ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-bullseye w-5"></i>
                <span class="font-medium">Matches</span>
                <?php
                // Buscar contador de matches novos
                $CI =& get_instance();
                $CI->load->model('Match_model');
                $matches_novos = $CI->Match_model->count_novos();
                if ($matches_novos > 0):
                ?>
                <span class="ml-auto rounded-full bg-primary-500 px-2 py-1 text-xs font-bold"><?php echo $matches_novos; ?></span>
                <?php endif; ?>
            </a>

            <!-- Empresas -->
            <a href="<?php echo base_url('admin/empresas'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo in_array($this->uri->segment(2), ['empresas', 'empresa']) ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-building w-5"></i>
                <span class="font-medium">Empresas</span>
            </a>

            <!-- Propostas -->
            <a href="<?php echo base_url('admin/propostas'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo in_array($this->uri->segment(2), ['propostas', 'proposta']) ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-file-invoice-dollar w-5"></i>
                <span class="font-medium">Propostas</span>
                <?php
                // Buscar contador de propostas em rascunho
                $CI =& get_instance();
                $CI->load->model('Proposta_model');
                $rascunhos = $CI->db->where('status', 'RASCUNHO')->count_all_results('propostas');
                if ($rascunhos > 0):
                ?>
                <span class="ml-auto rounded-full bg-yellow-500 px-2 py-1 text-xs font-bold"><?php echo $rascunhos; ?></span>
                <?php endif; ?>
            </a>

            <!-- Separador -->
            <div class="my-4 border-t border-dark-700"></div>

            <!-- Relatórios -->
            <a href="<?php echo base_url('admin/relatorios'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'relatorios' ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-chart-bar w-5"></i>
                <span class="font-medium">Relatórios</span>
            </a>

            <!-- Configurações -->
            <a href="<?php echo base_url('admin/configuracoes'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white <?php echo $this->uri->segment(2) == 'configuracoes' ? 'bg-dark-800 text-white border-l-4 border-primary-500' : ''; ?>">
                <i class="fas fa-cog w-5"></i>
                <span class="font-medium">Configurações</span>
            </a>

            <!-- Ajuda -->
            <a href="<?php echo base_url('admin/ajuda'); ?>" 
               class="flex items-center space-x-3 rounded-lg px-4 py-3 text-gray-300 hover:bg-dark-800 hover:text-white">
                <i class="fas fa-question-circle w-5"></i>
                <span class="font-medium">Ajuda</span>
            </a>
        </nav>

        <!-- User Profile -->
        <div class="border-t border-dark-700 p-4">
            <div class="flex items-center space-x-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-primary-400 to-primary-600">
                    <span class="text-sm font-bold text-white"><?php echo strtoupper(substr($this->session->userdata('user_name') ?? 'AD', 0, 2)); ?></span>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-white"><?php echo $this->session->userdata('user_name') ?? 'Admin User'; ?></p>
                    <p class="text-xs text-gray-400"><?php echo $this->session->userdata('user_email') ?? 'admin@allmight.com'; ?></p>
                </div>
                <a href="<?php echo base_url('auth/logout'); ?>" class="text-gray-400 hover:text-red-400 transition-colors" title="Sair">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="lg:pl-72" :class="{ 'pl-72': sidebarOpen, 'pl-0': !sidebarOpen }">
        <!-- Top Navigation Bar -->
        <header class="sticky top-0 z-30 glass border-b border-dark-700">
            <div class="flex h-20 items-center justify-between px-6">
                <!-- Menu Toggle -->
                <button @click="sidebarOpen = !sidebarOpen" class="text-gray-400 hover:text-white">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Page Title -->
                <div class="flex-1 px-6">
                    <h2 class="text-2xl font-bold text-white">
                        <?php echo isset($page_title) ? $page_title : 'Dashboard'; ?>
                    </h2>
                    <?php if(isset($page_subtitle)): ?>
                        <p class="text-sm text-gray-400"><?php echo $page_subtitle; ?></p>
                    <?php endif; ?>
                </div>

                <!-- Top Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Search -->
                    <form action="<?php echo base_url('admin/licitacoes'); ?>" method="get" class="relative hidden md:block">
                        <input type="text" 
                               name="search"
                               placeholder="Buscar licitações..." 
                               class="w-64 rounded-lg bg-dark-800 border border-dark-700 px-4 py-2 pl-10 text-sm text-white placeholder-gray-500 focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500"
                               value="<?php echo $this->input->get('search'); ?>">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-500 pointer-events-none"></i>
                    </form>

                    <!-- Notifications -->
                    <a href="<?php echo base_url('admin/monitoramento'); ?>" class="relative rounded-lg p-2 text-gray-400 hover:bg-dark-800 hover:text-white">
                        <i class="fas fa-bell text-xl"></i>
                        <?php
                        // Buscar contador de alertas novos
                        $CI =& get_instance();
                        $CI->load->model('Alerta_model');
                        $alertas_novos = $CI->Alerta_model->count_novos();
                        if ($alertas_novos > 0):
                        ?>
                        <span class="absolute -right-1 -top-1 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white"><?php echo $alertas_novos; ?></span>
                        <?php endif; ?>
                    </a>

                    <!-- Quick Actions -->
                    <a href="<?php echo base_url('admin/analises'); ?>" class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 neon-glow inline-block">
                        <i class="fas fa-plus mr-2"></i>Nova Análise
                    </a>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="min-h-screen gradient-bg p-6">
            <div class="animate-fade-in">
