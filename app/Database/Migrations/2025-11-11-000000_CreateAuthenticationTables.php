<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthenticationTables extends Migration
{
    public function up()
    {
        // ======================== TABELA: usuarios ========================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'unique'     => true,
            ],
            'email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'unique'     => true,
            ],
            'password' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['admin', 'servidor'],
                'default'    => 'servidor',
                'comment'    => 'Tipo de usuário: admin ou servidor',
            ],
            'ativo' => [
                'type'    => 'TINYINT',
                'default' => 1,
                'comment' => '1 = ativo, 0 = inativo',
            ],
            'email_verified' => [
                'type'    => 'TINYINT',
                'default' => 0,
                'comment' => '1 = email verificado, 0 = não verificado',
            ],
            'last_login_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('ativo');
        $this->forge->createTable('usuarios', true);

        // ======================== TABELA: login_attempts ========================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username_or_email' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'comment'    => 'IPv4 ou IPv6',
            ],
            'success' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'comment'    => '1 = login bem-sucedido, 0 = falha',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('username_or_email');
        $this->forge->addKey('ip');
        $this->forge->addKey('success');
        $this->forge->addKey('created_at');
        $this->forge->createTable('login_attempts', true);

        // ======================== TABELA: auth_tokens ========================
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
                'unique'     => true,
                'comment'    => 'Hash do token (password_hash)',
            ],
            'type' => [
                'type'       => 'ENUM',
                'constraint' => ['remember', 'reset_password'],
                'comment'    => 'Tipo de token: remember (login) ou reset_password (recuperação)',
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('type');
        $this->forge->addKey('expires_at');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('auth_tokens', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_tokens', true);
        $this->forge->dropTable('login_attempts', true);
        $this->forge->dropTable('usuarios', true);
    }
}
