<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateFuturizeDatabase extends Migration
{
    public function up()
    {
        /**
         * 1️⃣ PREFEITURAS
         */
        $this->forge->addField([
            'PRE_Codigo'   => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'PRE_Nome'     => ['type' => 'VARCHAR', 'constraint' => 100],
            'PRE_Municipio'=> ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'PRE_UF'       => ['type' => 'CHAR', 'constraint' => 2, 'null' => true],
        ]);
        $this->forge->addKey('PRE_Codigo', true);
        $this->forge->createTable('prefeituras');

        /**
         * 2️⃣ CONTRIBUINTES
         * Baseada 100% na estrutura enviada
         */
        $this->forge->addField([
            'CON_PRE_Codigo'              => ['type' => 'INT', 'unsigned' => true],
            'CON_Codigo'                  => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'CON_Nome'                    => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_Endereco'                => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_Numero'                  => ['type' => 'INT', 'null' => true],
            'CON_Complemento'             => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'CON_Bairro'                  => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_Cidade'                  => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'CON_CEP'                     => ['type' => 'VARCHAR', 'constraint' => 15, 'null' => true],
            'CON_Estado'                  => ['type' => 'CHAR', 'constraint' => 2, 'null' => true],
            'CON_Telefone1'               => ['type' => 'VARCHAR', 'constraint' => 14, 'null' => true],
            'CON_Telefone2'               => ['type' => 'VARCHAR', 'constraint' => 14, 'null' => true],
            'CON_Observacao'              => ['type' => 'TEXT', 'null' => true],
            'CON_CPFCNPJ'                 => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'CON_InscricaoEstatual'       => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'CON_TipoPessoa'              => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
            'CON_CampoLivre1'             => ['type' => 'DOUBLE', 'null' => true],
            'CON_CampoLivre2'             => ['type' => 'DOUBLE', 'null' => true],
            'CON_CampoLivre3'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_CampoLivre4'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_CampoLivre5'             => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_Temp'                    => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'CON_Temp2'                   => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'CON_InscricaoMunicipal'      => ['type' => 'INT', 'null' => true],
            'CON_InscricaoMunicipalAno'   => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'CON_Email'                   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'CON_NAT_Codigo'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_CCE_Codigo'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_ATI_Codigo'              => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
            'CON_TipoPessoaPJ'            => ['type' => 'CHAR', 'constraint' => 1, 'null' => true],
            'CON_Codigo1RedeSimMG'        => ['type' => 'VARCHAR', 'constraint' => 4, 'null' => true],
            'CON_Codigo2RedeSimMG'        => ['type' => 'VARCHAR', 'constraint' => 5, 'null' => true],
            'CON_DividaDA'                => ['type' => 'TINYINT', 'unsigned' => true, 'default' => 0],
            'CON_AOT_Codigo'              => ['type' => 'INT', 'null' => true],
            'CON_InicioAtividade'         => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey(['CON_PRE_Codigo', 'CON_Codigo']);
        $this->forge->addUniqueKey('CON_Codigo');
        $this->forge->addForeignKey('CON_PRE_Codigo', 'prefeituras', 'PRE_Codigo', 'CASCADE', 'CASCADE');
        $this->forge->createTable('contribuintes', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8mb4', 'COLLATE' => 'utf8mb4_general_ci']);

        /**
         * 3️⃣ ATIVIDADES
         */
        $this->forge->addField([
            'ATI_Codigo'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'ATI_Descricao' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('ATI_Codigo', true);
        $this->forge->createTable('atividades');

        /**
         * 4️⃣ CNAES
         */
        $this->forge->addField([
            'CNAE_Codigo'    => ['type' => 'INT', 'unsigned' => true, 'auto_increment' => true],
            'CNAE_Numero'    => ['type' => 'VARCHAR', 'constraint' => 20],
            'CNAE_Descricao' => ['type' => 'VARCHAR', 'constraint' => 255],
        ]);
        $this->forge->addKey('CNAE_Codigo', true);
        $this->forge->createTable('cnaes');

        /**
         * 5️⃣ ATIVIDADES_CONTRIBUINTES
         */
        $this->forge->addField([
            'CON_PRE_Codigo' => ['type' => 'INT', 'unsigned' => true],
            'CON_Codigo'     => ['type' => 'INT', 'unsigned' => true],
            'ATI_Codigo'     => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey(['CON_PRE_Codigo', 'CON_Codigo', 'ATI_Codigo']);
        $this->forge->addForeignKey(['CON_PRE_Codigo', 'CON_Codigo'], 'contribuintes', ['CON_PRE_Codigo', 'CON_Codigo'], 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('ATI_Codigo', 'atividades', 'ATI_Codigo', 'CASCADE', 'CASCADE');
        $this->forge->createTable('atividades_contribuintes');

        /**
         * 6️⃣ CNAES_CONTRIBUINTES
         */
        $this->forge->addField([
            'CON_PRE_Codigo' => ['type' => 'INT', 'unsigned' => true],
            'CON_Codigo'     => ['type' => 'INT', 'unsigned' => true],
            'CNAE_Codigo'    => ['type' => 'INT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey(['CON_PRE_Codigo', 'CON_Codigo', 'CNAE_Codigo']);
        $this->forge->addForeignKey(['CON_PRE_Codigo', 'CON_Codigo'], 'contribuintes', ['CON_PRE_Codigo', 'CON_Codigo'], 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('CNAE_Codigo', 'cnaes', 'CNAE_Codigo', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cnaes_contribuintes');
    }

    public function down()
    {
        $this->forge->dropTable('cnaes_contribuintes', true);
        $this->forge->dropTable('atividades_contribuintes', true);
        $this->forge->dropTable('cnaes', true);
        $this->forge->dropTable('atividades', true);
        $this->forge->dropTable('contribuintes', true);
        $this->forge->dropTable('prefeituras', true);
    }
}
