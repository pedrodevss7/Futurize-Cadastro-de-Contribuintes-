<?php namespace App\Controllers;

use App\Models\UsuarioModel;
use App\Models\AuthTokenModel;
use App\Models\LoginAttemptModel;
use CodeIgniter\Controller;

class AuthController extends BaseController
{
    protected $maxAttempts = 5;       // tentativas para lockout
    protected $lockoutMinutes = 1;   // bloqueio
    protected $rememberTTL = 60*60*24*30; // 30 dias
    protected $resetTTL = 60*30;      // 30 minutos
    protected $usuarioModel;
    protected $authTokenModel;
    protected $attemptModel;

    public function __construct()
    {
        helper(['form', 'url', 'text']);
        $this->usuarioModel = new UsuarioModel();
        $this->authTokenModel = new AuthTokenModel();
        $this->attemptModel = new LoginAttemptModel();
        session();
    }

    // POST /auth/login
    public function login()
    {
        if ($this->request->getMethod() !== 'get' && $this->request->getMethod() !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['status'=>'error','message'=>'Método não permitido']);
        }

        $login = trim((string)$this->request->getPost('username') ?? $this->request->getPost('login'));
        $password = $this->request->getPost('password');
        $tipo = $this->request->getPost('tipo') ?? null;
        $remember = $this->request->getPost('remember') ? true : false;
        $ip = $this->request->getIPAddress();

        // validação básica
        if (!$login || !$password) {
            return $this->response->setStatusCode(422)->setJSON(['status'=>'error','message'=>'Informe usuário/e-mail e senha.']);
        }

        // bloqueio: checar tentativas
        $failures = $this->attemptModel->recentFailures($login, $this->lockoutMinutes);
        if ($failures >= $this->maxAttempts) {
            return $this->response->setStatusCode(429)->setJSON([
                'status'=>'error',
                'message' => "Muitas tentativas. Aguarde {$this->lockoutMinutes} minutos antes de tentar novamente."
            ]);
        }

        // buscar usuário
        $usuario = $this->usuarioModel->where('ativo',1)
                    ->groupStart()
                      ->where('username', $login)
                      ->orWhere('email', $login)
                    ->groupEnd()
                    ->first();

        if (!$usuario) {
            $this->attemptModel->record($login, $ip, false);
            $this->log('login_failed', null, $ip, ['login' => $login]);
            return $this->response->setStatusCode(401)->setJSON(['status'=>'error','message'=>'Usuário ou senha inválidos.']);
        }

        // se for filtro por tipo
        if ($tipo && $usuario['tipo'] !== $tipo) {
            $this->attemptModel->record($login, $ip, false);
            $this->log('login_failed_tipo_mismatch', $usuario['id'], $ip);
            return $this->response->setStatusCode(401)->setJSON(['status'=>'error','message'=>'Credenciais inválidas.']);
        }

        // verificar senha com suporte Argon2id fallback
        $valid = password_verify($password, $usuario['password']);
        if (!$valid) {
            $this->attemptModel->record($login, $ip, false);
            $this->log('login_failed', $usuario['id'], $ip);
            return $this->response->setStatusCode(401)->setJSON(['status'=>'error','message'=>'Usuário ou senha inválidos.']);
        }

        // sucesso: limpar tokens remember antigos (opcional)
        $this->authTokenModel->revokeByUser($usuario['id'], 'remember');

        // set session de forma segura
        $sessData = [
            'user_id' => $usuario['id'],
            'username' => $usuario['username'],
            'email' => $usuario['email'],
            'tipo' => $usuario['tipo'],
            'isLoggedIn' => true,
            'csrf_token' => csrf_hash()
        ];
        session()->regenerate();
        session()->set($sessData);

        $this->usuarioModel->updateLastLogin($usuario['id']);
        $this->attemptModel->record($login, $ip, true);
        $this->log('login_success', $usuario['id'], $ip);

        // remember me
        $redirect = base_url($usuario['tipo']=='admin' ? 'admin/dashboard' : 'servidor/dashboard');
        $response = ['status'=>'success','message'=>'Login efetuado.','redirect'=>$redirect];

        if ($remember) {
            $tokenPlain = $this->authTokenModel->createToken($usuario['id'], 'remember', $this->rememberTTL);
            // cookie seguro
            setcookie('remember_token', $usuario['id'].'|'.$tokenPlain, time()+$this->rememberTTL, '/', '', isset($_SERVER['HTTPS']), true);
            $response['remember'] = true;
        }

        return $this->response->setJSON($response);
    }

    public function logout()
    {
        $userId = session('user_id') ?? null;
        if ($userId) {
            $this->log('logout', $userId, $this->request->getIPAddress());
        }
        // destruir sessão e cookie remember
        session()->destroy();
        setcookie('remember_token', '', time()-3600, '/', '', isset($_SERVER['HTTPS']), true);

        // Se a requisição foi via AJAX (fetch do frontend), retornar JSON para que o JS trate a navegação.
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true]);
        }

        // Para requisições normais, redirecionar para a home e garantir cabeçalhos para não-cache.
        $resp = redirect()->to('/');
        $resp->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $resp->setHeader('Pragma', 'no-cache');
        $resp->setHeader('Expires', '0');
        return $resp;
    }

    // Endpoints para reset de senha (envio por e-mail) e validação do token
    public function requestPasswordReset()
    {
        $email = $this->request->getPost('email');
        if (!$email) return $this->response->setStatusCode(422)->setJSON(['status'=>'error','message'=>'Informe o e-mail.']);

        $usuario = $this->usuarioModel->where('email', $email)->first();
        if (!$usuario) {
            // não revelar existência: enviar mensagem genérica
            return $this->response->setJSON(['status'=>'success','message'=>'Se este e-mail existir em nosso sistema, você receberá instruções.']);
        }

        $this->authTokenModel->revokeByUser($usuario['id'], 'password_reset');
        $tokenPlain = $this->authTokenModel->createToken($usuario['id'], 'password_reset', $this->resetTTL);

        // TODO: enviar email real com link contendo tokenPlain
        // ex: https://seusite.com/auth/reset-password?token=TOKEN&email=EMAIL
        $this->log('password_reset_requested', $usuario['id'], $this->request->getIPAddress());

        return $this->response->setJSON(['status'=>'success','message'=>'Se este e-mail existir em nosso sistema, você receberá instruções.']);
    }

    public function resetPassword()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');

        if (!$token || !$password || !$passwordConfirm) {
            return $this->response->setStatusCode(422)->setJSON(['status'=>'error','message'=>'Dados incompletos.']);
        }
        if ($password !== $passwordConfirm) {
            return $this->response->setStatusCode(422)->setJSON(['status'=>'error','message'=>'Senhas não conferem.']);
        }
        if (!$this->isStrongPassword($password)) {
            return $this->response->setStatusCode(422)->setJSON(['status'=>'error','message'=>'Senha não atende os requisitos de segurança.']);
        }

        $row = $this->authTokenModel->validateToken($token, 'password_reset');
        if (!$row) {
            return $this->response->setStatusCode(400)->setJSON(['status'=>'error','message'=>'Token inválido ou expirado.']);
        }

        $userId = $row['usuario_id'];
        $hash = password_hash($password, defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_DEFAULT);
        $this->usuarioModel->update($userId, ['password' => $hash]);
        $this->authTokenModel->revokeByUser($userId, 'password_reset');
        $this->log('password_reset_done', $userId, $this->request->getIPAddress());

        return $this->response->setJSON(['status'=>'success','message'=>'Senha redefinida com sucesso.']);
    }

    // util: registra eventos em auth_logs
    protected function log($action, $userId = null, $ip = null, $meta = null)
    {
        $db = \Config\Database::connect();
        $db->table('auth_logs')->insert([
            'usuario_id' => $userId,
            'action' => $action,
            'ip' => $ip ?? $this->request->getIPAddress(),
            'user_agent' => $this->request->getUserAgent()->getAgentString(),
            'meta' => is_array($meta) ? json_encode($meta) : $meta,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    protected function isStrongPassword($pass)
    {
        // politica: minimo 10, uma maiuscula, uma minuscula, um numero, um simbolo
        if (strlen($pass) < 10) return false;
        if (!preg_match('/[A-Z]/', $pass)) return false;
        if (!preg_match('/[a-z]/', $pass)) return false;
        if (!preg_match('/\d/', $pass)) return false;
        if (!preg_match('/[^a-zA-Z0-9]/', $pass)) return false;
        return true;
    }

    // Endpoint para refresh de CSRF (opcional)
    public function refreshCsrf()
    {
        return $this->response->setJSON(['csrf' => csrf_hash(), 'tokenName' => csrf_token()]);
    }
}
