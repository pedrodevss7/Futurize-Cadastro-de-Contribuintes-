<?php

namespace App\Controllers;

use App\Models\ContribuinteModel;
use App\Models\CnaeModel;
use App\Models\AtividadeModel;
use CodeIgniter\API\ResponseTrait;
use Exception;

class ContribuinteController extends BaseController
{
    use ResponseTrait;

    protected $contribuinteModel;
    protected $cnaeModel;
    protected $atividadeModel;

    public function __construct()
    {
        $this->contribuinteModel = new ContribuinteModel();
        $this->cnaeModel = new CnaeModel();
        $this->atividadeModel = new AtividadeModel();
        helper(['form']);
    }

    // 🔹 Listar todos
    public function listar()
    {
        try {
            $contribuintes = $this->contribuinteModel->findAll();

            // Normaliza os campos para o formato que o frontend (public/js/dashboard.js)
            // espera (campos em snake_case/minúsculos ou chaves específicas).
            $mapped = array_map(function($c) {
                return [
                    'CON_codigo' => $c['CON_Codigo'] ?? ($c['CON_codigo'] ?? null),
                    'CON_razao_social' => $c['CON_Nome'] ?? ($c['CON_razao_social'] ?? null),
                    'CON_nome_fantasia' => $c['CON_NomeFantasia'] ?? ($c['CON_nome_fantasia'] ?? null),
                    'CON_cpf_cnpj' => $c['CON_CPFCNPJ'] ?? ($c['CON_cpf_cnpj'] ?? null),
                    'CON_tipo_pessoa' => $c['CON_TipoPessoa'] ?? ($c['CON_tipo_pessoa'] ?? null),
                    'CON_tipo_pessoa_rj' => $c['CON_TipoPessoaPJ'] ?? ($c['CON_tipo_pessoa_rj'] ?? null),
                    'CON_endereco' => $c['CON_Endereco'] ?? ($c['CON_endereco'] ?? null),
                    'CON_numero' => $c['CON_Numero'] ?? ($c['CON_numero'] ?? null),
                    'CON_bairro' => $c['CON_Bairro'] ?? ($c['CON_bairro'] ?? null),
                    'CON_cidade' => $c['CON_Cidade'] ?? ($c['CON_cidade'] ?? null),
                    'CON_cep' => $c['CON_CEP'] ?? ($c['CON_cep'] ?? null),
                    'CON_telefone1' => $c['CON_Telefone1'] ?? ($c['CON_telefone1'] ?? null),
                    'CON_telefone2' => $c['CON_Telefone2'] ?? ($c['CON_telefone2'] ?? null),
                    'CON_email' => $c['CON_Email'] ?? ($c['CON_email'] ?? null),
                    'CON_inscricao_estadual' => $c['CON_InscricaoEstadual'] ?? ($c['CON_inscricao_estadual'] ?? null),
                    'CON_inscricao_municipal' => $c['CON_InscricaoMunicipal'] ?? ($c['CON_inscricao_municipal'] ?? null),
                    'CON_status' => $c['CON_Status'] ?? ($c['CON_status'] ?? 'ativo'),
                    // mantém os campos originais por segurança
                    '_raw' => $c,
                ];
            }, $contribuintes ?: []);

            return $this->respond(['success' => true, 'data' => $mapped]);
        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function obter($id = null)
    {
        try {
            $pre = $this->getPrefeituraCodigo();
            if (!$id) return $this->respond(['success' => false, 'message' => 'Cód. contribuinte não informado'], 400);

            $contribuinte = $this->contribuinteModel->findByComposite((int)$pre, (int)$id);
            if (!$contribuinte) {
                return $this->respond(['success' => false, 'message' => 'Contribuinte não encontrado'], 404);
            }

            $db = \Config\Database::connect();

            // Buscar atividades do contribuinte (tabela pivot: atividades_contribuintes)
            $atividades = $db->table('atividades_contribuintes as ac')
                ->join('atividades a', 'a.ATI_Codigo = ac.ATI_Codigo')
                ->where('ac.CON_PRE_Codigo', $pre)
                ->where('ac.CON_Codigo', $id)
                ->select('a.ATI_Codigo as atividade_id, a.ATI_Descricao as descricao')
                ->get()
                ->getResultArray();

            // Buscar CNAEs do contribuinte (tabela pivot: cnaes_contribuintes)
            $cnaes = $db->table('cnaes_contribuintes as cc')
                ->join('cnaes c', 'c.CNAE_Codigo = cc.CNAE_Codigo')
                ->where('cc.CON_PRE_Codigo', $pre)
                ->where('cc.CON_Codigo', $id)
                ->select('c.CNAE_Codigo as cnae_id, c.CNAE_Numero as numero, c.CNAE_Descricao as nome')
                ->get()
                ->getResultArray();

            // Mapeia CNAEs para o formato esperado pelo frontend
            // Se não houver informação de "primário" persistida, marcamos o primeiro como primário
            $mappedCnaes = [];
            foreach ($cnaes as $i => $c) {
                $mappedCnaes[] = [
                    'cnae_id' => $c['cnae_id'] ?? null,
                    'numero' => $c['numero'] ?? $c['CNAE_Numero'] ?? null,
                    'nome' => $c['nome'] ?? $c['CNAE_Descricao'] ?? null,
                    // manter compatibilidade: se o frontend enviou 'tipo' anteriormente, respeitar;
                    // caso contrário, marcar o primeiro CNAE retornado como primário para que a UI exiba algo consistente
                    'tipo' => isset($c['tipo']) ? $c['tipo'] : ($i === 0 ? 'primario' : 'secundario'),
                ];
            }

            // Mapeia atividades (inclui flags para a UI: numero, nome, data e se é principal)
            $mappedAtividades = [];
            foreach ($atividades as $i => $a) {
                $mappedAtividades[] = [
                    'atividade_id' => $a['atividade_id'] ?? $a['ATI_Codigo'] ?? null,
                    // número/ código da atividade (compatibilidade com frontend)
                    'numero' => $a['numero'] ?? ($a['ATI_Codigo'] ?? null),
                    'nome' => $a['nome'] ?? $a['descricao'] ?? $a['ATI_Descricao'] ?? null,
                    // data da atividade: se o contribuinte tiver CON_InicioAtividade, retornamos para popular o formulário
                    'data' => $contribuinte['CON_InicioAtividade'] ?? $a['data'] ?? $a['data_atividade'] ?? null,
                    // principal: marcar verdadeiro se corresponder ao CON_ATI_Codigo (campo que passa a ser usado como principal)
                    'principal' => isset($contribuinte['CON_ATI_Codigo']) && $contribuinte['CON_ATI_Codigo'] == ($a['atividade_id'] ?? $a['ATI_Codigo'] ?? null),
                    'descricao' => $a['descricao'] ?? $a['ATI_Descricao'] ?? null,
                ];
            }

            // Normaliza o objeto contribuinte para o frontend (campos em minúsculas/underscore)
            $out = [
                'CON_codigo' => $contribuinte['CON_Codigo'] ?? ($contribuinte['CON_codigo'] ?? null),
                'CON_razao_social' => $contribuinte['CON_Nome'] ?? ($contribuinte['CON_razao_social'] ?? null),
                'CON_nome_fantasia' => $contribuinte['CON_NomeFantasia'] ?? ($contribuinte['CON_nome_fantasia'] ?? null),
                'CON_cpf_cnpj' => $contribuinte['CON_CPFCNPJ'] ?? ($contribuinte['CON_cpf_cnpj'] ?? null),
                'CON_tipo_pessoa' => $contribuinte['CON_TipoPessoa'] ?? ($contribuinte['CON_tipo_pessoa'] ?? null),
                'CON_tipo_pessoa_rj' => $contribuinte['CON_TipoPessoaPJ'] ?? ($contribuinte['CON_tipo_pessoa_rj'] ?? null),
                'CON_inscricao_municipal' => $contribuinte['CON_InscricaoMunicipal'] ?? ($contribuinte['CON_inscricao_municipal'] ?? null),
                'CON_inscricao_estadual' => $contribuinte['CON_InscricaoEstadual'] ?? ($contribuinte['CON_inscricao_estadual'] ?? null),
                'CON_endereco' => $contribuinte['CON_Endereco'] ?? ($contribuinte['CON_endereco'] ?? null),
                'CON_numero' => $contribuinte['CON_Numero'] ?? ($contribuinte['CON_numero'] ?? null),
                'CON_complemento' => $contribuinte['CON_Complemento'] ?? ($contribuinte['CON_complemento'] ?? null),
                'CON_bairro' => $contribuinte['CON_Bairro'] ?? ($contribuinte['CON_bairro'] ?? null),
                'CON_cidade' => $contribuinte['CON_Cidade'] ?? ($contribuinte['CON_cidade'] ?? null),
                'CON_cep' => $contribuinte['CON_CEP'] ?? ($contribuinte['CON_cep'] ?? null),
                'CON_telefone1' => $contribuinte['CON_Telefone1'] ?? ($contribuinte['CON_telefone1'] ?? null),
                'CON_telefone2' => $contribuinte['CON_Telefone2'] ?? ($contribuinte['CON_telefone2'] ?? null),
                'CON_email' => $contribuinte['CON_Email'] ?? ($contribuinte['CON_email'] ?? null),
                'CON_status' => $contribuinte['CON_Status'] ?? ($contribuinte['CON_status'] ?? 'ativo'),
                'atividades' => $mappedAtividades,
                'cnaes' => $mappedCnaes,
                '_raw' => $contribuinte,
            ];

            return $this->respond(['success' => true, 'data' => $out]);

        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Cadastrar
    public function cadastrar()
    {
        try {
            $pre = $this->getPrefeituraCodigo();
            $dados = $this->request->getJSON(true);
            log_message('debug', 'Payload cadastrar: ' . json_encode($dados));
            if (empty($dados)) return $this->respond(['success' => false, 'message' => 'Nenhum dado recebido'], 400);

            $erro = $this->validarDados($dados);
            if ($erro) return $this->respond(['success' => false, 'message' => $erro], 400);

            // se não forneceu CON_Codigo, gerar próximo para a prefeitura
            $conCodigo = $dados['codigo'] ?? null;
            if (!$conCodigo) {
                $conCodigo = $this->getNextConCodigo($pre);
            }

            $dadosInsert = $this->prepararDados($dados, $pre, $conCodigo);
            // usa insertComposite que exige CON_PRE_Codigo e CON_Codigo
            $this->contribuinteModel->insertComposite($dadosInsert);

            $contribuinte_nome = $dadosInsert['CON_Nome'] ?? null;

            $db = \Config\Database::connect();

            // Iniciar transação para garantir integridade entre atividades e pivot
            $db->transStart();

            // 🔹 Salvar atividades (pivot: atividades_contribuintes)
            $atividades = $dados['atividades'] ?? [];
            if (!empty($atividades)) {
                foreach ($atividades as $atividade) {
                    log_message('debug', 'Processando atividade (cadastrar) payload: ' . json_encode($atividade));
                    // Para atividades novas, criar na tabela atividades
                    if (($atividade['tipo'] ?? '') === 'nova') {
                        $atividade_id = $this->atividadeModel->insert([
                            'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                        ], true);
                        log_message('debug', 'Atividade criada id=' . var_export($atividade_id, true));
                    } else {
                        // aceitar 'atividade_id' ou 'id' vindos do frontend
                        $atividade_id = $atividade['atividade_id'] ?? $atividade['id'] ?? null;
                        if (empty($atividade_id)) {
                            // tentar localizar pela descrição/nome antes de pular
                            $descricaoBusca = trim((string)($atividade['descricao'] ?? ($atividade['nome'] ?? '')));
                            if (!empty($descricaoBusca)) {
                                $exist = $this->atividadeModel->where('ATI_Descricao', $descricaoBusca)->first();
                                if ($exist) {
                                    $atividade_id = $exist[$this->atividadeModel->primaryKey] ?? $exist['ATI_Codigo'] ?? null;
                                    log_message('debug', 'Atividade encontrada por descrição no cadastrar, id=' . var_export($atividade_id, true) . ' descricao=' . $descricaoBusca);
                                } else {
                                    // criar nova atividade se não existir
                                    $atividade_id = $this->atividadeModel->insert([
                                        'ATI_Descricao' => $descricaoBusca
                                    ], true);
                                    log_message('debug', 'Atividade criada por fallback no cadastrar, id=' . var_export($atividade_id, true) . ' descricao=' . $descricaoBusca);
                                }
                            } else {
                                log_message('warning', 'Atividade sem id e sem descrição ignorada no cadastrar: ' . json_encode($atividade));
                                continue;
                            }
                        }
                    }

                    // Normalizar atividade_id: se veio em formatos como 'pre_4', tentar extrair número
                    if (!is_null($atividade_id) && !is_numeric($atividade_id)) {
                        // preferir campo 'numero' quando existir
                        if (!empty($atividade['numero']) && is_numeric($atividade['numero'])) {
                            $atividade_id = $atividade['numero'];
                        } else {
                            // extrair dígitos do valor (ex: 'pre_4' -> 4)
                            if (preg_match('/(\d+)/', (string)$atividade_id, $m)) {
                                $atividade_id = $m[1];
                            } else {
                                $atividade_id = null;
                            }
                        }
                    }

                    // confirmar que a atividade realmente existe (restrição FK)
                    $atividade_id = (int)$atividade_id;
                    $exists = $db->table('atividades')->where('ATI_Codigo', $atividade_id)->get()->getRowArray();
                    if (!$exists) {
                        // tentar recriar por segurança
                        log_message('warning', 'Atividade id não encontrada antes do insert pivot, tentando criar novamente id=' . var_export($atividade_id, true));
                        $novaId = $this->atividadeModel->insert([
                            'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                        ], true);
                        $atividade_id = (int)$novaId;
                        log_message('debug', 'Atividade criada novamente id=' . var_export($atividade_id, true));
                    }

                    if (empty($atividade_id)) {
                        log_message('error', 'Não foi possível obter um id válido para atividade ao inserir pivot (cadastrar): ' . json_encode($atividade));
                        continue;
                    }

                    try {
                        $res = $db->table('atividades_contribuintes')->insert([
                            'CON_PRE_Codigo' => $pre,
                            'CON_Codigo'     => $conCodigo,
                            'ATI_Codigo'     => $atividade_id,
                        ]);
                        log_message('debug', 'Insert pivot atividades_contribuintes result=' . var_export($res, true) . ' atividade_id=' . var_export($atividade_id, true));
                    } catch (\Exception $e) {
                        log_message('error', 'Erro insert pivot atividades_contribuintes: ' . $e->getMessage() . ' payload=' . json_encode(['pre'=>$pre,'con'=>$conCodigo,'atividade_id'=>$atividade_id]));
                        // se falhar por FK, tentar criar a atividade novamente
                        try {
                            $novaId = $this->atividadeModel->insert([
                                'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                            ], true);
                            if ($novaId) {
                                $db->table('atividades_contribuintes')->insert([
                                    'CON_PRE_Codigo' => $pre,
                                    'CON_Codigo'     => $conCodigo,
                                    'ATI_Codigo'     => (int)$novaId,
                                ]);
                                log_message('debug', 'Insert pivot realizado após recriar atividade, id=' . var_export($novaId, true));
                            }
                        } catch (\Exception $e2) {
                            log_message('error', 'Falha ao recriar atividade e inserir pivot: ' . $e2->getMessage());
                        }
                    }
                }
            }

            // 🔹 Salvar CNAES (pivot: cnaes_contribuintes)
            $cnaes = $dados['cnaes'] ?? [];
            foreach ($cnaes as $cnae) {
                // Buscar pelo número do CNAE (CNAE_Numero)
                $cnaeNumero = $cnae['numero'] ?? $cnae['codigo'] ?? null;
                if (!$cnaeNumero) continue; // Pular se não tiver número
                
                $cnaeExistente = $this->cnaeModel->where('CNAE_Numero', $cnaeNumero)->first();
                if ($cnaeExistente) {
                    $cnae_id = $cnaeExistente['CNAE_Codigo'];
                } else {
                    $cnae_id = $this->cnaeModel->insert([
                        'CNAE_Numero'    => $cnaeNumero,
                        'CNAE_Descricao' => $cnae['nome'] ?? ($cnae['descricao'] ?? null),
                    ], true);
                }

                $db->table('cnaes_contribuintes')->insert([
                    'CON_PRE_Codigo' => $pre,
                    'CON_Codigo'     => $conCodigo,
                    'CNAE_Codigo'    => $cnae_id,
                ]);
            }

            // completar transação
            $db->transComplete();
            if ($db->transStatus() === false) {
                log_message('error', 'Transação falhou ao cadastrar contribuinte pre=' . $pre . ' con=' . $conCodigo);
                return $this->respond(['success' => false, 'message' => 'Erro ao salvar contribuinte (transação falhou)'], 500);
            }

            return $this->respondCreated([
                'success' => true,
                'message' => 'Contribuinte cadastrado com sucesso',
                'CON_PRE_Codigo' => $pre,
                'CON_Codigo' => $conCodigo,
            ]);

        } catch (Exception $e) {
            log_message('error', 'Erro no cadastro: ' . $e->getMessage());
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Editar
    public function editar($id = null)
    {
        try {
            $pre = $this->getPrefeituraCodigo();
            if (!$id) return $this->respond(['success' => false, 'message' => 'Cód. contribuinte não informado'], 400);

            $contribuinte = $this->contribuinteModel->findByComposite((int)$pre, (int)$id);
            if (!$contribuinte) return $this->respond(['success' => false, 'message' => 'Contribuinte não encontrado'], 404);

            $dados = $this->request->getJSON(true);
            log_message('debug', 'Payload editar id=' . var_export($id, true) . ' : ' . json_encode($dados));
            if (empty($dados)) return $this->respond(['success' => false, 'message' => 'Nenhum dado recebido'], 400);

            $erro = $this->validarDados($dados, $id);
            if ($erro) return $this->respond(['success' => false, 'message' => $erro], 400);

            $dadosUpdate = $this->prepararDados($dados, $pre, $id);
            $this->contribuinteModel->updateComposite((int)$pre, (int)$id, $dadosUpdate);

            $contribuinte_nome = $dadosUpdate['CON_Nome'] ?? null;

            $db = \Config\Database::connect();

            // 🔹 Atualizar atividades (pivot)
            $atividades = $dados['atividades'] ?? [];
            $db->table('atividades_contribuintes')->where('CON_PRE_Codigo', $pre)->where('CON_Codigo', $id)->delete();
            foreach ($atividades as $atividade) {
                log_message('debug', 'Processando atividade (editar) payload: ' . json_encode($atividade));
                if (($atividade['tipo'] ?? '') === 'nova') {
                    $atividade_id = $this->atividadeModel->insert([
                        'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                    ], true);
                    log_message('debug', 'Atividade criada (editar) id=' . var_export($atividade_id, true));
                } else {
                    $atividade_id = $atividade['atividade_id'] ?? $atividade['id'] ?? null;
                    // se veio em formato não-numérico (ex: 'pre_4'), tentar normalizar
                    if (!is_null($atividade_id) && !is_numeric($atividade_id)) {
                        if (!empty($atividade['numero']) && is_numeric($atividade['numero'])) {
                            $atividade_id = $atividade['numero'];
                        } elseif (preg_match('/(\d+)/', (string)$atividade_id, $m)) {
                            $atividade_id = $m[1];
                        } else {
                            $atividade_id = null;
                        }
                    }
                    if (empty($atividade_id)) {
                        // tentar localizar pela descrição/nome antes de pular
                        $descricaoBusca = trim((string)($atividade['descricao'] ?? ($atividade['nome'] ?? '')));
                        if (!empty($descricaoBusca)) {
                            $exist = $this->atividadeModel->where('ATI_Descricao', $descricaoBusca)->first();
                            if ($exist) {
                                $atividade_id = $exist[$this->atividadeModel->primaryKey] ?? $exist['ATI_Codigo'] ?? null;
                                log_message('debug', 'Atividade encontrada por descrição no editar, id=' . var_export($atividade_id, true) . ' descricao=' . $descricaoBusca);
                            } else {
                                $atividade_id = $this->atividadeModel->insert([
                                    'ATI_Descricao' => $descricaoBusca
                                ], true);
                                log_message('debug', 'Atividade criada por fallback no editar, id=' . var_export($atividade_id, true) . ' descricao=' . $descricaoBusca);
                            }
                        } else {
                            log_message('warning', 'Atividade sem id e sem descrição ignorada no editar: ' . json_encode($atividade));
                            // evitar erro/avisos se payload estiver incompleto
                            continue;
                        }
                    }
                }

                // Normalizar atividade_id quando necessário (ex: 'pre_4')
                if (!is_null($atividade_id) && !is_numeric($atividade_id)) {
                    if (!empty($atividade['numero']) && is_numeric($atividade['numero'])) {
                        $atividade_id = $atividade['numero'];
                    } elseif (preg_match('/(\d+)/', (string)$atividade_id, $m)) {
                        $atividade_id = $m[1];
                    } else {
                        $atividade_id = null;
                    }
                }

                $atividade_id = (int)$atividade_id;
                $exists = $db->table('atividades')->where('ATI_Codigo', $atividade_id)->get()->getRowArray();
                if (!$exists) {
                    log_message('warning', 'Atividade id não encontrada antes do insert pivot (editar), tentando criar novamente id=' . var_export($atividade_id, true));
                    $novaId = $this->atividadeModel->insert([
                        'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                    ], true);
                    $atividade_id = (int)$novaId;
                    log_message('debug', 'Atividade criada novamente (editar) id=' . var_export($atividade_id, true));
                }

                if (empty($atividade_id)) {
                    log_message('error', 'Não foi possível obter um id válido para atividade ao inserir pivot (editar): ' . json_encode($atividade));
                    continue;
                }

                try {
                    $res = $db->table('atividades_contribuintes')->insert([
                        'CON_PRE_Codigo' => $pre,
                        'CON_Codigo'     => $id,
                        'ATI_Codigo'     => $atividade_id,
                    ]);
                    log_message('debug', 'Insert pivot (editar) result=' . var_export($res, true) . ' atividade_id=' . var_export($atividade_id, true));
                } catch (\Exception $e) {
                    log_message('error', 'Erro insert pivot atividades_contribuintes (editar): ' . $e->getMessage() . ' payload=' . json_encode(['pre'=>$pre,'con'=>$id,'atividade_id'=>$atividade_id]));
                    // tentar recriar atividade e inserir novamente
                    try {
                        $novaId = $this->atividadeModel->insert([
                            'ATI_Descricao' => $atividade['descricao'] ?? ($atividade['nome'] ?? '')
                        ], true);
                        if ($novaId) {
                            $db->table('atividades_contribuintes')->insert([
                                'CON_PRE_Codigo' => $pre,
                                'CON_Codigo'     => $id,
                                'ATI_Codigo'     => (int)$novaId,
                            ]);
                            log_message('debug', 'Insert pivot (editar) realizado após recriar atividade, id=' . var_export($novaId, true));
                        }
                    } catch (\Exception $e2) {
                        log_message('error', 'Falha ao recriar atividade e inserir pivot (editar): ' . $e2->getMessage());
                    }
                }
            }

            // 🔹 Atualizar CNAEs (pivot)
            $cnaes = $dados['cnaes'] ?? [];
            $db->table('cnaes_contribuintes')->where('CON_PRE_Codigo', $pre)->where('CON_Codigo', $id)->delete();
            foreach ($cnaes as $cnae) {
                // Buscar pelo número do CNAE (CNAE_Numero)
                $cnaeNumero = $cnae['numero'] ?? $cnae['codigo'] ?? null;
                if (!$cnaeNumero) continue; // Pular se não tiver número
                
                $cnaeExistente = $this->cnaeModel->where('CNAE_Numero', $cnaeNumero)->first();
                if ($cnaeExistente) {
                    $cnae_id = $cnaeExistente['CNAE_Codigo'];
                } else {
                    $cnae_id = $this->cnaeModel->insert([
                        'CNAE_Numero'    => $cnaeNumero,
                        'CNAE_Descricao' => $cnae['nome'] ?? ($cnae['descricao'] ?? null),
                    ], true);
                }

                $db->table('cnaes_contribuintes')->insert([
                    'CON_PRE_Codigo' => $pre,
                    'CON_Codigo'     => $id,
                    'CNAE_Codigo'    => $cnae_id,
                ]);
            }

            return $this->respond(['success' => true, 'message' => 'Contribuinte atualizado com sucesso']);

        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Excluir
    public function excluir($id = null)
    {
        try {
            $pre = $this->getPrefeituraCodigo();
            if (!$id) return $this->respond(['success' => false, 'message' => 'Cód. contribuinte não informado'], 400);

            $contribuinte = $this->contribuinteModel->findByComposite((int)$pre, (int)$id);
            if (!$contribuinte) return $this->respond(['success' => false, 'message' => 'Contribuinte não encontrado'], 404);

            $db = \Config\Database::connect();
            $db->table('atividades_contribuintes')->where('CON_PRE_Codigo', $pre)->where('CON_Codigo', $id)->delete();
            $db->table('cnaes_contribuintes')->where('CON_PRE_Codigo', $pre)->where('CON_Codigo', $id)->delete();
            $this->contribuinteModel->deleteComposite((int)$pre, (int)$id);

            return $this->respond(['success' => true, 'message' => 'Contribuinte excluído com sucesso']);
        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Listar atividades disponíveis
    public function getAtividades()
    {
        try {
            $atividades = $this->atividadeModel->findAll();
            return $this->respond(['success' => true, 'data' => $atividades]);
        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 🔹 Listar CNAEs disponíveis
    public function cnaes()
    {
        try {
            $cnaes = $this->cnaeModel->findAll();
            return $this->respond(['success' => true, 'data' => $cnaes]);
        } catch (Exception $e) {
            return $this->respond(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ------------------------
    // 🔹 Métodos Auxiliares
    // ------------------------

    private function validarDados($dados, $id = null)
    {
        $camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'bairro', 'cidade'];

        foreach ($camposObrigatorios as $campo) {
            if (empty($dados[$campo])) return "O campo {$campo} é obrigatório";
        }

    $cpf_cnpj = preg_replace('/\D/', '', $dados['cpf_cnpj']);

        if (strlen($cpf_cnpj) === 11 && !$this->validarCpf($cpf_cnpj)) return 'CPF inválido';
        if (strlen($cpf_cnpj) === 14 && !$this->validarCnpj($cpf_cnpj)) return 'CNPJ inválido';
        if (!in_array(strlen($cpf_cnpj), [11, 14])) return 'CPF/CNPJ inválido';

    // Verifica se já existe CPF/CNPJ para a mesma prefeitura
    $pre = $this->getPrefeituraCodigo();
    $query = $this->contribuinteModel->where('CON_CPFCNPJ', $cpf_cnpj)->where('CON_PRE_Codigo', $pre);
    if ($id) $query->where('CON_Codigo !=', $id);
    if ($query->first()) return 'CPF/CNPJ já cadastrado em outro contribuinte';

        // Validação status inativo
        if (($dados['status'] ?? 'ativo') === 'inativo') {
            if (empty($dados['data_baixa'])) return 'Data de baixa é obrigatória para status inativo';
            if (!strtotime($dados['data_baixa'])) return 'Data de baixa inválida';
        }

        return null;
    }

    /**
     * Prepara dados no formato das colunas do banco (nomes conforme migration)
     * Recebe também $pre (CON_PRE_Codigo) e $conCodigo (CON_Codigo)
     */
    private function prepararDados($dados, int $pre, int $conCodigo)
    {
        $cpf_cnpj = preg_replace('/\D/', '', $dados['cpf_cnpj'] ?? '');
        // Detectar atividade primária (se houver) para persistir em campos de contribuinte
        $conAtiCodigo = null;
        $conInicioAtividade = null;
        if (!empty($dados['atividades']) && is_array($dados['atividades'])) {
            // usamos a primeira atividade enviada como primária no modelo de formulário atual
            $first = $dados['atividades'][0];
            $atividade_id = $first['atividade_id'] ?? $first['id'] ?? null;
            // normalizar formatos (ex: 'pre_4' -> 4)
            if (!is_null($atividade_id) && !is_numeric($atividade_id)) {
                if (!empty($first['numero']) && is_numeric($first['numero'])) {
                    $atividade_id = $first['numero'];
                } elseif (preg_match('/(\d+)/', (string)$atividade_id, $m)) {
                    $atividade_id = $m[1];
                } else {
                    $atividade_id = null;
                }
            }
            if (!empty($atividade_id)) $conAtiCodigo = (int)$atividade_id;

            // data da atividade (campo usado no formulário)
            if (!empty($first['data'])) {
                // aceitar strings que o DB consiga armazenar
                $conInicioAtividade = $first['data'];
            }
        }

        $out = [
            'CON_PRE_Codigo'            => $pre,
            'CON_Codigo'                => $conCodigo,
            'CON_CPFCNPJ'               => $cpf_cnpj,
            'CON_Nome'                  => $dados['razao_social'] ?? '',
            'CON_Endereco'              => $dados['endereco'] ?? '',
            'CON_TipoPessoa'           => $dados['tipo_pessoa'] ?? null,
            'CON_TipoPessoaPJ'         => $dados['tipo_pessoa_rj'] ?? null,
            'CON_Numero'                => isset($dados['numero']) ? intval($dados['numero']) : 0,
            'CON_Complemento'           => $dados['complemento'] ?? null,
            'CON_Bairro'                => $dados['bairro'] ?? '',
            'CON_Cidade'                => $dados['cidade'] ?? '',
            'CON_CEP'                   => preg_replace('/\D/', '', $dados['cep'] ?? ''),
            'CON_Estado'                => $dados['estado'] ?? null,
            'CON_Telefone1'             => preg_replace('/\D/', '', $dados['telefone1'] ?? ''),
            'CON_Telefone2'             => preg_replace('/\D/', '', $dados['telefone2'] ?? ''),
            'CON_Email'                 => $dados['email'] ?? null,
            'CON_InscricaoEstadual'     => $dados['inscricao_estadual'] ?? null,
            'CON_InscricaoMunicipal'    => $dados['inscricao_municipal'] ?? null,
            'CON_InscricaoMunicipalAno' => $dados['inscricao_municipal_ano'] ?? null,
        ];

        if (!is_null($conAtiCodigo)) $out['CON_ATI_Codigo'] = $conAtiCodigo;
        if (!is_null($conInicioAtividade)) $out['CON_InicioAtividade'] = $conInicioAtividade;

        return $out;
    }

    /**
     * Retorna o código da prefeitura a ser usado nas operações.
     * Procura no header 'X-PRE-Codigo', depois no GET 'pre' e por fim usa 1 como default.
     * Ajuste conforme sua lógica de seleção de prefeitura (sessão, subdomínio, etc.).
     */
    private function getPrefeituraCodigo(): int
    {
        $fromHeader = $this->request->getHeaderLine('X-PRE-Codigo');
        if ($fromHeader && is_numeric($fromHeader)) return (int)$fromHeader;

        $fromGet = $this->request->getGet('pre');
        if ($fromGet && is_numeric($fromGet)) return (int)$fromGet;

        // Assunção razoável: utilizar prefeitura 1 como default. Ajuste se necessário.
        return 1;
    }

    /**
     * Calcula próximo CON_Codigo para uma prefeitura (max + 1).
     */
    private function getNextConCodigo(int $pre): int
    {
        $db = \Config\Database::connect();
        $row = $db->table('contribuintes')->select('MAX(CON_Codigo) as max_codigo')->where('CON_PRE_Codigo', $pre)->get()->getRowArray();
        $max = isset($row['max_codigo']) ? (int)$row['max_codigo'] : 0;
        return $max + 1;
    }

    private function validarCpf($cpf)
    {
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) return false;
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) $d += $cpf[$c] * (($t + 1) - $c);
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) return false;
        }
        return true;
    }

    private function validarCnpj($cnpj)
    {
        if (strlen($cnpj) != 14 || preg_match('/(\d)\1{13}/', $cnpj)) return false;
        for ($t = 12; $t < 14; $t++) {
            for ($d = 0, $m = ($t - 7), $i = 0; $i < $t; $i++) {
                $d += $cnpj[$i] * $m;
                $m = ($m == 2) ? 9 : --$m;
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cnpj[$i] != $d) return false;
        }
        return true;
    }
}