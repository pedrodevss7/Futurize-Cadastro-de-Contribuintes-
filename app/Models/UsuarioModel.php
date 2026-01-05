<?php namespace App\Models;

use CodeIgniter\Model;

class UsuarioModel extends Model
{
    protected $table = 'usuarios';
    protected $primaryKey = 'id';
    protected $allowedFields = ['username','email','password','tipo','ativo','email_verified','last_login_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    // busca por username ou email
    public function findByLogin($login)
    {
        return $this->where('username', $login)
                    ->orWhere('email', $login)
                    ->first();
    }

    public function activate($id)
    {
        return $this->update($id, ['ativo' => 1]);
    }

    public function updateLastLogin($id)
    {
        return $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }
}
