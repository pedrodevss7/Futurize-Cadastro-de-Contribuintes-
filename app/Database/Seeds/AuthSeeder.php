<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AuthSeeder extends Seeder
{
    public function run()
    {
        $db = \Config\Database::connect();
        $password = password_hash('SenhaForte123!', PASSWORD_ARGON2ID) ?: password_hash('SenhaForte123!', PASSWORD_DEFAULT);

        $db->table('usuarios')->insertBatch([
            [
                'username' => 'admin1',
                'email' => 'admin@example.com',
                'password' => $password,
                'tipo' => 'admin',
                'ativo' => 1,
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'username' => 'servidor1',
                'email' => 'servidor@example.com',
                'password' => $password,
                'tipo' => 'servidor',
                'ativo' => 1,
                'email_verified' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ]
        ]);
    }
}
