<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuthLogs extends Migration
{
    public function up()
    {
        // ======================== TABELA: auth_logs ========================
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
                'null'       => true,
                'comment'    => 'ID do usuário (NULL se login não autenticado)',
            ],
            'action' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
                'comment'    => 'login_success, login_failed, logout, password_reset_done, etc',
            ],
            'ip' => [
                'type'       => 'VARCHAR',
                'constraint' => 45,
                'null'       => false,
                'comment'    => 'Endereço IP (IPv4 ou IPv6)',
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
                'comment'    => 'User-Agent do navegador',
            ],
            'meta' => [
                'type' => 'JSON',
                'null' => true,
                'comment'    => 'Dados adicionais em JSON',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('usuario_id');
        $this->forge->addKey('action');
        $this->forge->addKey('ip');
        $this->forge->addKey('created_at');
        $this->forge->addForeignKey('usuario_id', 'usuarios', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('auth_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('auth_logs', true);
    }
}
