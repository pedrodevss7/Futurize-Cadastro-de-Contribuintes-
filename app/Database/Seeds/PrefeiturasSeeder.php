<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PrefeiturasSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'PRE_Codigo'    => 1,
                'PRE_Nome'      => 'Prefeitura Municipal de São Paulo',
                'PRE_Municipio' => 'São Paulo',
                'PRE_UF'        => 'SP',
            ],
            [
                'PRE_Codigo'    => 2,
                'PRE_Nome'      => 'Prefeitura Municipal de Rio de Janeiro',
                'PRE_Municipio' => 'Rio de Janeiro',
                'PRE_UF'        => 'RJ',
            ],
            [
                'PRE_Codigo'    => 3,
                'PRE_Nome'      => 'Prefeitura Municipal de Belo Horizonte',
                'PRE_Municipio' => 'Belo Horizonte',
                'PRE_UF'        => 'MG',
            ],
            [
                'PRE_Codigo'    => 4,
                'PRE_Nome'      => 'Prefeitura Municipal de Brasília',
                'PRE_Municipio' => 'Brasília',
                'PRE_UF'        => 'DF',
            ],
            [
                'PRE_Codigo'    => 5,
                'PRE_Nome'      => 'Prefeitura Municipal de Salvador',
                'PRE_Municipio' => 'Salvador',
                'PRE_UF'        => 'BA',
            ],
        ];

        $this->db->table('prefeituras')->insertBatch($data);
    }
}
