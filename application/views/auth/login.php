<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Login - AllMight'; ?></title>
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        .glass {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(10px);
        }
    </style>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen flex items-center justify-center p-4">
    
    <div class="w-full max-w-md">
        <!-- Logo e Título -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 mb-4">
                <img src="<?php echo base_url('logo.png'); ?>" alt="AllMight Logo" class="w-16 h-16 object-contain">
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">AllMight</h1>
            <p class="text-gray-400">Sistema Inteligente de Licitações</p>
        </div>

        <!-- Card de Login -->
        <div class="glass rounded-2xl border border-gray-700 p-8">
            <h2 class="text-2xl font-bold text-white mb-6">Entrar no Sistema</h2>

            <!-- Mensagens de erro/sucesso -->
            <?php if ($this->session->flashdata('error')): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-500/20 border border-red-500/50 text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo $this->session->flashdata('error'); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->session->flashdata('success')): ?>
                <div class="mb-4 p-4 rounded-lg bg-green-500/20 border border-green-500/50 text-green-400">
                    <i class="fas fa-check-circle mr-2"></i>
                    <?php echo $this->session->flashdata('success'); ?>
                </div>
            <?php endif; ?>

            <?php if (validation_errors()): ?>
                <div class="mb-4 p-4 rounded-lg bg-red-500/20 border border-red-500/50 text-red-400">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo validation_errors(); ?>
                </div>
            <?php endif; ?>

            <!-- Formulário -->
            <form method="post" action="<?php echo base_url('auth/login'); ?>" class="space-y-4">
                
                <!-- E-mail -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-envelope mr-2"></i>E-mail
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        required
                        value="<?php echo set_value('email', isset($remembered_email) ? $remembered_email : ''); ?>"
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="seu@email.com"
                    >
                    <?php echo form_error('email', '<p class="mt-1 text-sm text-red-400">', '</p>'); ?>
                </div>

                <!-- Senha -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        <i class="fas fa-lock mr-2"></i>Senha
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        class="w-full px-4 py-3 rounded-lg bg-slate-800 border border-gray-700 text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="••••••••"
                    >
                    <?php echo form_error('password', '<p class="mt-1 text-sm text-red-400">', '</p>'); ?>
                </div>

                <!-- Lembrar-me -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" <?php echo (isset($remembered_email) && $remembered_email) ? 'checked' : ''; ?> class="rounded bg-slate-800 border-gray-700 text-primary-500 focus:ring-primary-500">
                        <span class="ml-2 text-sm text-gray-300">Lembrar-me</span>
                    </label>
                    <a href="#" class="text-sm text-primary-400 hover:text-primary-300">
                        Esqueceu a senha?
                    </a>
                </div>

                <!-- Botão de Login -->
                <button 
                    type="submit"
                    class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white font-semibold hover:from-primary-600 hover:to-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-slate-900 transition-all"
                >
                    <i class="fas fa-sign-in-alt mr-2"></i>
                    Entrar
                </button>
            </form>

            <!-- Credenciais de teste -->
            <div class="mt-6 p-4 rounded-lg bg-blue-500/10 border border-blue-500/30">
                <p class="text-xs text-blue-300 mb-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    <strong>Credenciais de teste:</strong>
                </p>
                <p class="text-xs text-gray-400">
                    E-mail: <code class="text-blue-300">admin@allmight.com</code><br>
                    Senha: <code class="text-blue-300">admin123</code>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <p class="text-center text-sm text-gray-500 mt-6">
            © 2025 AllMight. Todos os direitos reservados.
        </p>
    </div>

</body>
</html>
