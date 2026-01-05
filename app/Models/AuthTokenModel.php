<?php namespace App\Models;

use CodeIgniter\Model;

class AuthTokenModel extends Model
{
    protected $table = 'auth_tokens';
    protected $primaryKey = 'id';
    protected $allowedFields = ['usuario_id','token','type','expires_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function createToken($userId, $type, $ttlSeconds)
    {
        helper('text');
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + $ttlSeconds);

        $this->insert([
            'usuario_id' => $userId,
            'token' => password_hash($token, PASSWORD_DEFAULT), // guardamos o hash
            'type' => $type,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $token;
    }

    public function validateToken($tokenPlain, $type)
    {
        $rows = $this->where('type', $type)
                     ->where('expires_at >=', date('Y-m-d H:i:s'))
                     ->findAll();

        foreach ($rows as $row) {
            if (password_verify($tokenPlain, $row['token'])) {
                return $row;
            }
        }
        return null;
    }

    public function revokeByUser($userId, $type = null)
    {
        $builder = $this->db->table($this->table)->where('usuario_id', $userId);
        if ($type) $builder->where('type', $type);
        return $builder->delete();
    }
}
