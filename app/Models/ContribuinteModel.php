<?php

namespace App\Models;

use CodeIgniter\Model;

class ContribuinteModel extends Model
{
    protected $table = 'contribuintes';

    // Nota: CI4 não suporta primaryKey composta nativamente.
    // Mantemos o CON_Codigo como primaryKey "formal" aqui, mas
    // todas as buscas/updates devem incluir CON_PRE_Codigo no where.
    protected $primaryKey = 'CON_Codigo';
    protected $useAutoIncrement = false; // dado que CON_Codigo é manual

    // Se preferir forçar operações seguras, não deixe CON_PRE_Codigo em allowedFields,
    // mas aqui eu incluo tudo porque você pediu completo.
    protected $allowedFields = [
        'CON_PRE_Codigo',
        'CON_Codigo',
        'CON_Nome',
        'CON_Endereco',
        'CON_Numero',
        'CON_Complemento',
        'CON_Bairro',
        'CON_Cidade',
        'CON_CEP',
        'CON_Estado',
        'CON_Telefone1',
        'CON_Telefone2',
        'CON_Observacao',
        'CON_CPFCNPJ',
        'CON_InscricaoEstatual',
        'CON_TipoPessoa',
        'CON_CampoLivre1',
        'CON_CampoLivre2',
        'CON_CampoLivre3',
        'CON_CampoLivre4',
        'CON_CampoLivre5',
        'CON_Temp',
        'CON_Temp2',
        'CON_InscricaoMunicipal',
        'CON_InscricaoMunicipalAno',
        'CON_Email',
        'CON_NAT_Codigo',
        'CON_CCE_Codigo',
        'CON_ATI_Codigo',
        'CON_TipoPessoaPJ',
        'CON_Codigo1RedeSimMG',
        'CON_Codigo2RedeSimMG',
        'CON_DividaDA',
        'CON_AOT_Codigo',
        'CON_InicioAtividade',
    ];

    protected $useTimestamps = false; // ative se quiser `created_at`/`updated_at`
    protected $returnType = 'array';

    /**
     * Busca um contribuinte pela chave composta (prefeitura + codigo).
     */
    public function findByComposite(int $preCodigo, int $conCodigo)
    {
        return $this->where('CON_PRE_Codigo', $preCodigo)
                    ->where('CON_Codigo', $conCodigo)
                    ->first();
    }

    /**
     * Insert seguro respeitando chave composta.
     * Recebe array $data com todas as colunas (incluindo CON_PRE_Codigo e CON_Codigo).
     */
    public function insertComposite(array $data)
    {
        // Verifica presença de chaves obrigatórias
        if (!isset($data['CON_PRE_Codigo']) || !isset($data['CON_Codigo'])) {
            throw new \InvalidArgumentException('CON_PRE_Codigo e CON_Codigo são obrigatórios.');
        }

        // checa se já existe
        $exists = $this->where('CON_PRE_Codigo', $data['CON_PRE_Codigo'])
                       ->where('CON_Codigo', $data['CON_Codigo'])
                       ->first();

        if ($exists) {
            throw new \RuntimeException('Contribuinte já existe para essa prefeitura com esse CON_Codigo.');
        }

        // insert (usa save pra respeitar $allowedFields)
        return $this->save($data);
    }

    /**
     * Update seguro via chave composta.
     */
    public function updateComposite(int $preCodigo, int $conCodigo, array $data)
    {
        $this->where('CON_PRE_Codigo', $preCodigo)
             ->where('CON_Codigo', $conCodigo);

        return $this->set($data)->update();
    }

    /**
     * Delete via chave composta.
     */
    public function deleteComposite(int $preCodigo, int $conCodigo)
    {
        return $this->where('CON_PRE_Codigo', $preCodigo)
                    ->where('CON_Codigo', $conCodigo)
                    ->delete();
    }
}
