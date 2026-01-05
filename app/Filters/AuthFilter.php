<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (!session()->get('isLoggedIn')) {
            if ($request->isAJAX()) {
                return service('response')->setStatusCode(403)->setJSON(['status'=>'error','message'=>'Acesso negado']);
            }
            return redirect()->to('/');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Evita que páginas autenticadas sejam armazenadas em cache pelo navegador.
        // Isso ajuda a impedir que usuário volte 'para dentro' do sistema usando o botão Voltar
        // depois de um logout.
        try {
            if (session()->get('isLoggedIn')) {
                $response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
                $response->setHeader('Pragma', 'no-cache');
                $response->setHeader('Expires', '0');
            }
        } catch (\Exception $e) {
            // não bloquear a resposta por problemas de sessão
        }

        return $response;
    }


}
