<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsuariosSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'username'       => 'admin',
                'email'          => 'admin@futurize.com',
                'password'       => password_hash('admin123', PASSWORD_DEFAULT),
                'tipo'           => 'admin',
                'ativo'          => 1,
                'email_verified' => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'username'       => 'servidor',
                'email'          => 'servidor@futurize.com',
                'password'       => password_hash('servidor123', PASSWORD_DEFAULT),
                'tipo'           => 'servidor',
                'ativo'          => 1,
                'email_verified' => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('usuarios')->insertBatch($data);
    }
}
