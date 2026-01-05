<?php
namespace App\Models;
use CodeIgniter\Model;

class AtividadeModel extends Model
{
    protected $table = 'atividades';
    protected $primaryKey = 'ATI_Codigo';
    protected $allowedFields = ['ATI_Descricao'];
    protected $returnType = 'array';
    protected $useTimestamps = false;
}
