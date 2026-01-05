<?php
namespace App\Models;
use CodeIgniter\Model;

class CnaeModel extends Model
{
    protected $table = 'cnaes';
    protected $primaryKey = 'CNAE_Codigo';
    protected $allowedFields = ['CNAE_Numero', 'CNAE_Descricao'];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}
