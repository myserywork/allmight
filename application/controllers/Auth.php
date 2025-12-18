<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->helper(['url', 'form']);
        $this->load->library(['session', 'form_validation']);
    }

    /**
     * Página de login
     */
    public function index() {
        // Se já estiver logado, redireciona para o dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('admin/dashboard');
        }

        $this->login();
    }

    /**
     * Processar login
     */
    public function login() {
        // Se já estiver logado, redireciona para o dashboard
        if ($this->session->userdata('logged_in')) {
            redirect('admin/dashboard');
        }

        if ($this->input->method() == 'post') {
            // Validar dados
            $this->form_validation->set_rules('email', 'E-mail', 'required|valid_email');
            $this->form_validation->set_rules('password', 'Senha', 'required');

            if ($this->form_validation->run()) {
                $email = $this->input->post('email');
                $password = $this->input->post('password');

                // Buscar usuário no banco
                $user = $this->db->get_where('usuarios', ['email' => $email, 'ativo' => 1])->row();

                if ($user && password_verify($password, $user->senha_hash)) {
                    // Login bem-sucedido
                    $session_data = [
                        'user_id' => $user->id,
                        'user_name' => $user->nome,
                        'user_email' => $user->email,
                        'logged_in' => true
                    ];

                    $this->session->set_userdata($session_data);

                    // Lembrar-me: salvar email em cookie por 30 dias
                    $remember = $this->input->post('remember');
                    if ($remember) {
                        setcookie('remember_email', $email, time() + (30 * 24 * 60 * 60), '/');
                    } else {
                        // Remover cookie se não marcou
                        setcookie('remember_email', '', time() - 3600, '/');
                    }

                    // Atualizar último acesso
                    $this->db->where('id', $user->id);
                    $this->db->update('usuarios', ['data_ultimo_acesso' => date('Y-m-d H:i:s')]);

                    redirect('admin/dashboard');
                } else {
                    // Login falhou
                    $this->session->set_flashdata('error', 'E-mail ou senha incorretos!');
                    redirect('auth/login');
                }
            }
        }

        // Carregar view de login
        $data['page_title'] = 'Login - AllMight';
        $data['remembered_email'] = isset($_COOKIE['remember_email']) ? $_COOKIE['remember_email'] : '';
        $this->load->view('auth/login', $data);
    }

    /**
     * Logout
     */
    public function logout() {
        // Destruir sessão
        $this->session->unset_userdata('user_id');
        $this->session->unset_userdata('user_name');
        $this->session->unset_userdata('user_email');
        $this->session->unset_userdata('logged_in');
        $this->session->sess_destroy();

        // Redirecionar para login
        $this->session->set_flashdata('success', 'Você saiu do sistema!');
        redirect('auth/login');
    }

    /**
     * Verificar se está logado (helper para outras controllers)
     */
    public function check_auth() {
        if (!$this->session->userdata('logged_in')) {
            $this->session->set_flashdata('error', 'Você precisa estar logado para acessar esta página!');
            redirect('auth/login');
        }
    }
}
