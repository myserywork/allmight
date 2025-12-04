            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-dark-700 bg-dark-900 px-6 py-4">
            <div class="flex flex-col items-center justify-between space-y-3 text-sm text-gray-400 md:flex-row md:space-y-0">
                <div>
                    &copy; <?php echo date('Y'); ?> AllMight. Todos os direitos reservados.
                </div>
                <div class="flex items-center space-x-4">
                    <span>Versão 1.0.0</span>
                    <span>•</span>
                    <a href="#" class="hover:text-primary-400">Documentação</a>
                    <span>•</span>
                    <a href="#" class="hover:text-primary-400">Suporte</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Mobile Menu Overlay -->
    <div x-show="mobileMenuOpen" 
         @click="mobileMenuOpen = false"
         class="fixed inset-0 z-30 bg-black/50 lg:hidden"
         x-cloak>
    </div>

    <!-- Toast Notifications Container -->
    <div id="toast-container" class="fixed top-24 right-6 z-50 space-y-2">
        <!-- Toasts will be inserted here via JavaScript -->
    </div>

    <!-- Custom Scripts -->
    <script>
        // Toast notification system
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            const icons = {
                success: 'fa-check-circle text-green-500',
                error: 'fa-exclamation-circle text-red-500',
                warning: 'fa-exclamation-triangle text-yellow-500',
                info: 'fa-info-circle text-blue-500'
            };
            
            const colors = {
                success: 'border-green-500',
                error: 'border-red-500',
                warning: 'border-yellow-500',
                info: 'border-blue-500'
            };
            
            toast.className = `glass rounded-lg border-l-4 ${colors[type]} p-4 shadow-lg animate-slide-in max-w-sm`;
            toast.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas ${icons[type]} text-xl"></i>
                    <p class="text-sm text-white">${message}</p>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-auto text-gray-400 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 5000);
        }

        // Confirm dialog
        function confirmAction(message, callback) {
            if (confirm(message)) {
                callback();
            }
        }

        // Format currency
        function formatCurrency(value) {
            return new Intl.NumberFormat('pt-BR', {
                style: 'currency',
                currency: 'BRL'
            }).format(value);
        }

        // Format date
        function formatDate(date) {
            return new Intl.DateTimeFormat('pt-BR').format(new Date(date));
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showToast('Copiado para área de transferência!', 'success');
            });
        }

        // Check for flash messages
        <?php if($this->session->flashdata('success')): ?>
            showToast('<?php echo $this->session->flashdata('success'); ?>', 'success');
        <?php endif; ?>
        
        <?php if($this->session->flashdata('error')): ?>
            showToast('<?php echo $this->session->flashdata('error'); ?>', 'error');
        <?php endif; ?>
        
        <?php if($this->session->flashdata('warning')): ?>
            showToast('<?php echo $this->session->flashdata('warning'); ?>', 'warning');
        <?php endif; ?>
        
        <?php if($this->session->flashdata('info')): ?>
            showToast('<?php echo $this->session->flashdata('info'); ?>', 'info');
        <?php endif; ?>
    </script>
</body>
</html>
