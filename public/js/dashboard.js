// ======================== VARIÁVEIS GLOBAIS ========================
let contribuintes = [];
let modoEdicao = false;
let contribuinteEditandoId = null;
let todasAtividades = [];
let catalogoAtividades = []; // catálogo de atividades disponíveis (carregado do endpoint)
let catalogoCNAEs = []; // catálogo de todos os CNAEs do banco (pré-cadastrados)
let todosCNAEs = []; // CNAEs selecionados para o contribuinte atual

// 🔹 URLs base
const API_BASE_URL = 'http://localhost/Futurize.STM/public/index.php/';
const API_CONTRIBUINTES = API_BASE_URL + 'api/contribuintes';
const API_ATIVIDADES = API_BASE_URL + 'api/contribuintes/atividades';
const LOGOUT_URL = '/Futurize.STM/public/'

// ======================== CARREGAR CONTRIBUINTES ========================
function carregarContribuintes() {
    mostrarLoading(true);
    fetch(API_CONTRIBUINTES + '/listar')
        .then(res => { if (!res.ok) throw new Error('Erro: ' + res.status); return res.json(); })
        .then(json => {
            mostrarLoading(false);
            if (json.success) {
                contribuintes = json.data;
                atualizarTabela(contribuintes);
            } else {
                alert('Erro: ' + json.message);
            }
        })
        .catch(err => {
            mostrarLoading(false);
            console.error('Erro:', err);
            alert('Erro ao carregar contribuintes: ' + err.message);
        });
}

// ======================== FILTRAGEM E PESQUISA ========================
function filtrarContribuintes() {
    const sel = document.getElementById('filterSelect');
    if (!sel) return;
    const val = (sel.value || 'all').toString().toLowerCase();

    if (val === 'all') {
        atualizarTabela(contribuintes);
        return;
    }

    const filtered = (contribuintes || []).filter(c => {
        const tipoVal = (c.CON_tipo_pessoa || '').toString().toLowerCase();
        const isFisica = tipoVal === 'f' || tipoVal.startsWith('fis') || tipoVal.includes('fís') || tipoVal.includes('fisica');
        const status = (c.CON_status || 'ativo').toString().toLowerCase();

        if (val === 'fisica') return isFisica;
        if (val === 'juridica') return !isFisica;
        if (val === 'ativo' || val === 'inativo') return status === val;
        return true;
    });

    atualizarTabela(filtered);
}

function pesquisarContribuinte() {
    const q = (document.getElementById('inputPesquisa')?.value || '').trim().toLowerCase();
    if (!q) {
        // reset
        document.getElementById('btnVoltarLista').style.display = 'none';
        document.getElementById('filterSelect').value = 'all';
        atualizarTabela(contribuintes);
        return;
    }

    const filtered = (contribuintes || []).filter(c => {
        const nome = (c.CON_razao_social || '').toString().toLowerCase();
        const cpfcnpj = (c.CON_cpf_cnpj || '').toString().toLowerCase();
        const codigo = (c.CON_codigo || '').toString().toLowerCase();
        return nome.includes(q) || cpfcnpj.includes(q) || codigo.includes(q);
    });

    const btn = document.getElementById('btnVoltarLista');
    if (btn) btn.style.display = 'inline-block';
    atualizarTabela(filtered);
}

function voltarLista() {
    const btn = document.getElementById('btnVoltarLista');
    if (btn) btn.style.display = 'none';
    const inp = document.getElementById('inputPesquisa');
    if (inp) inp.value = '';
    const sel = document.getElementById('filterSelect');
    if (sel) sel.value = 'all';
    atualizarTabela(contribuintes);
}

// ======================== TABELA ========================
function atualizarTabela(dados) {
    const tbody = document.querySelector('#contribuintesTable tbody');
    if (!tbody) return;
    tbody.innerHTML = '';

    if (!dados || !dados.length) {
        tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum contribuinte encontrado</td></tr>';
        return;
    }

    dados.forEach(c => {
        const tr = document.createElement('tr');
        const tipoVal = (c.CON_tipo_pessoa || '').toString().toLowerCase();
        const isFisica = tipoVal === 'f' || tipoVal.startsWith('fis') || tipoVal.includes('fís') || tipoVal.includes('fisica');
        const tipoExibicao = isFisica ? 'Física' : 'Jurídica';
        const status = c.CON_status || 'ativo';
        const statusBadgeClass = status === 'ativo' ? 'status-badge-ativo' : 'status-badge-inativo';

        tr.innerHTML = `
            <td>${c.CON_codigo || ''}</td>
            <td>${c.CON_razao_social || ''}</td>
            <td>${formatarCpfCnpj(c.CON_cpf_cnpj) || ''}</td>
            <td>${tipoExibicao}</td>
            <td><span class="status-badge ${statusBadgeClass}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
            <td>
                <button class="btn btn-sm btn-primary" onclick="editarContribuinte(${c.CON_codigo})">Editar</button>
                <button class="btn btn-sm btn-danger" onclick="excluirContribuinte(${c.CON_codigo})">Excluir</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// ======================== FORMATAÇÃO ========================
function formatarCpfCnpj(valor) {
    if (!valor) return '';
    const n = valor.replace(/\D/g, '');
    if (n.length <= 11) return n.replace(/(\d{3})(\d{3})(\d{3})(\d{0,2})/, "$1.$2.$3-$4");
    return n.replace(/(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, "$1.$2.$3/$4-$5");
}

function formatarCep(cep) {
    if (!cep) return '';
    const n = cep.replace(/\D/g, '');
    return n.length === 8 ? n.replace(/^(\d{5})(\d{3})/, '$1-$2') : cep;
}

function formatarTelefone(tel) {
    if (!tel) return '';
    const n = tel.replace(/\D/g, '');
    if (n.length === 10) return n.replace(/^(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
    if (n.length === 11) return n.replace(/^(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
    return tel;
}

// Formatação visual enquanto digita
function aplicarMascaras() {
    const cpfCnpjInput = document.querySelectorAll('.cpf_cnpj');
    cpfCnpjInput.forEach(input => {
        input.addEventListener('input', e => {
            let valor = e.target.value.replace(/\D/g, '');
            e.target.value = formatarCpfCnpj(valor);
        });
    });

    const telInputs = document.querySelectorAll('.telefone');
    telInputs.forEach(input => {
        input.addEventListener('input', e => {
            let valor = e.target.value.replace(/\D/g, '');
            e.target.value = formatarTelefone(valor);
        });
    });

    const cepInput = document.querySelectorAll('.cep');
    cepInput.forEach(input => {
        input.addEventListener('input', e => {
            let valor = e.target.value.replace(/\D/g, '');
            e.target.value = formatarCep(valor);
        });
    });
}

// ======================== MODAL ========================
function abrirModalCadastro() {
    modoEdicao = false;
    contribuinteEditandoId = null;
    limparFormulario();
    document.getElementById('modalContribuinte').style.display = 'block';
    document.getElementById('modalTitulo').textContent = 'CADASTRAR CONTRIBUINTE';

    let sequencia = parseInt(localStorage.getItem('codigoSequencial') || 1000) + 1;
    localStorage.setItem('codigoSequencial', sequencia);
    document.getElementById('codigo').value = sequencia;

    setTimeout(() => {
        aplicarMascaras();
        forcarCaixaAlta();
    }, 100);

    carregarCatalogoAtividades();
    carregarCatalogoCNAEs();
}

function fecharModal() { document.getElementById('modalContribuinte').style.display = 'none'; }

function limparFormulario() {
    const form = document.getElementById('contribuinteForm');
    form.reset();
    document.getElementById('pessoa_fisica').checked = true;
    document.getElementById('status').value = 'ativo';
    toggleDataBaixa();
    alterarTipoPessoa();
    todasAtividades = [];
    todosCNAEs = [];
    renderizarTabelaAtividades();
    renderizarTabelaCNAEs();
}

// ======================== SALVAR ========================
async function salvarContribuinte() {
    const form = document.getElementById("contribuinteForm");
    const dados = Object.fromEntries(new FormData(form).entries());

    // Validações simples
    if (!dados.cpf_cnpj || dados.cpf_cnpj.replace(/\D/g, '').length < 11) return alert('CPF/CNPJ inválido!');
    if (dados.cep && dados.cep.replace(/\D/g, '').length !== 8) return alert('CEP inválido!');

    processarDadosCaixaAlta(dados);

    const tipoPessoaElement = document.querySelector('input[name="tipo_pessoa"]:checked');
    if (!tipoPessoaElement) return alert('Selecione o tipo de pessoa');
    dados.tipo_pessoa = tipoPessoaElement.value;
    dados.tipo_pessoa_rj = dados.tipo_pessoa === 'juridica' ? document.getElementById('tipo_pessoa_rj').value || '' : '';

    // 🔹 VALIDAR E PROCESSAR ATIVIDADES/CNAEs conforme tipo de pessoa
    // Regras:
    // - Pessoa física: atividades e CNAEs NÃO são obrigatórios.
    // - Pessoa jurídica: atividades NÃO são obrigatórias; CNAEs SÃO obrigatórios.
    if (dados.tipo_pessoa === 'fisica') {
        // Não exigir atividades nem CNAEs
        dados.atividades = [];
        dados.cnaes = todosCNAEs.length > 0 ? todosCNAEs.map(cnae => ({
            numero: cnae.numero,
            nome: cnae.nome,
            tipo: cnae.tipo,
            novo: cnae.id.startsWith('novo_')
        })) : [];
    } else {
        // Pessoa jurídica
        // Atividades são opcionais, mas se existir uma atividade selecionada precisa ter data
        if (todasAtividades.length > 0) {
            const atividadeSelecionada = todasAtividades[0];
            const dataAtividade = (document.getElementById('atividade_data') ? document.getElementById('atividade_data').value : '') || atividadeSelecionada.data || '';
            if (!dataAtividade) {
                return alert('É necessário informar a data da atividade!');
            }
            dados.atividades = [{
                numero: atividadeSelecionada.numero,
                nome: atividadeSelecionada.nome,
                atividade_id: atividadeSelecionada.id || null,
                data: dataAtividade,
                principal: true,
                tipo: atividadeSelecionada.id && atividadeSelecionada.id.toString().startsWith('novo_') ? 'nova' : 'pre_cadastrada'
            }];
        } else {
            dados.atividades = [];
        }

        // CNAEs são obrigatórios para pessoas jurídicas
        if (todosCNAEs.length === 0) {
            return alert('É necessário cadastrar pelo menos um CNAE!');
        }
        const cnaePrimario = todosCNAEs.find(c => c.tipo === 'primario');
        if (!cnaePrimario) {
            return alert('É necessário marcar um CNAE como primário!');
        }
        dados.cnaes = todosCNAEs.map(cnae => ({
            numero: cnae.numero,
            nome: cnae.nome,
            tipo: cnae.tipo,
            novo: cnae.id.startsWith('novo_')
        }));
    }

    if (modoEdicao) dados.id = contribuinteEditandoId;

    mostrarLoading(true);
    bloquearBotaoSalvar(true);

    try {
        const url = modoEdicao ? API_CONTRIBUINTES + '/editar/' + contribuinteEditandoId : API_CONTRIBUINTES + '/cadastrar';
        const method = modoEdicao ? 'PUT' : 'POST';
    // Ensure we use the right codigo field (backend expects 'codigo')
    dados.codigo = document.getElementById('codigo').value;
    // payload logged only when DEBUG env enabled (removed noisy console.log)
        const res = await fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(dados)
        });
        const json = await res.json();
        mostrarLoading(false);
        bloquearBotaoSalvar(false);

        if (json.success) {
            alert('✅ ' + json.message);
            fecharModal();
            carregarContribuintes();
        } else alert('❌ ' + json.message);

    } catch (err) {
        mostrarLoading(false);
        bloquearBotaoSalvar(false);
        console.error('Erro na requisição:', err);
        alert('Erro ao salvar: ' + err.message);
    }
}

function bloquearBotaoSalvar(bloquear) {
    const btn = document.querySelector('.modal-footer .btn-success');
    if (btn) btn.disabled = bloquear;
}

// ======================== EDITAR ========================
function editarContribuinte(id) {
    mostrarLoading(true);
    fetch(API_CONTRIBUINTES + '/obter/' + id)
        .then(res => { if (!res.ok) throw new Error('Erro: ' + res.status); return res.json(); })
        .then(json => {
            mostrarLoading(false);
            if (json.success) {
                preencherFormulario(json.data);
                modoEdicao = true;
                // Store the CON_codigo as contribuinteEditandoId
                contribuinteEditandoId = json.data.CON_codigo;
                document.getElementById('modalContribuinte').style.display = 'block';
                document.getElementById('modalTitulo').textContent = 'EDITAR CONTRIBUINTE';
                
                setTimeout(aplicarMascaras, 100);

                // 🔹 CARREGAR ATIVIDADES EXISTENTES
                if (json.data.atividades && Array.isArray(json.data.atividades) && json.data.atividades.length > 0) {
                    // single-activity model: take the first activity and preserve its date if present
                    const a = json.data.atividades[0];
                    todasAtividades = [{
                        id: a.atividade_id ? a.atividade_id.toString() : ('novo_' + Date.now()),
                        numero: a.numero || `${String(1).padStart(3, '0')}`,
                        nome: a.nome || a.atividade_descricao || a.descricao || '',
                        data: a.data || a.data_atividade || ''
                    }];
                } else {
                    todasAtividades = [];
                }

                // 🔹 CARREGAR CNAEs EXISTENTES
                if (json.data.cnaes && Array.isArray(json.data.cnaes)) {
                    todosCNAEs = json.data.cnaes.map(c => ({
                        id: c.cnae_id ? c.cnae_id.toString() : 'novo_' + Date.now(),
                        numero: c.numero || c.codigo || c.CNAE_Numero || '',
                        nome: c.nome || c.cnae_nome || c.CNAE_Descricao || '',
                        tipo: c.tipo || (c.principal === true || c.principal === 1 ? 'primario' : 'secundario')
                    }));
                }

                renderizarTabelaAtividades();
                // garantir que os catálogos estejam carregados para popular selects e tabelas
                carregarCatalogoAtividades();
                carregarCatalogoCNAEs();
                renderizarTabelaCNAEs();
            } else alert('Erro: ' + json.message);
        })
        .catch(err => { mostrarLoading(false); console.error('Erro:', err); alert('Erro ao carregar contribuinte'); });
}

// ======================== EXCLUIR ========================
function excluirContribuinte(codigo) {
    if (!confirm('Tem certeza que deseja excluir este contribuinte?')) return;
    
    mostrarLoading(true);
    fetch(API_CONTRIBUINTES + '/excluir/' + codigo, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('Erro: ' + res.status);
        return res.json();
    })
    .then(json => {
        mostrarLoading(false);
        if (json.success) {
            alert('✅ Contribuinte excluído com sucesso!');
            carregarContribuintes();
        } else {
            alert('❌ ' + json.message);
        }
    })
    .catch(err => {
        mostrarLoading(false);
        console.error('Erro:', err);
        alert('Erro ao excluir contribuinte: ' + err.message);
    });
}

// ======================== PREENCHER FORMULÁRIO ========================
function preencherFormulario(c) {
    document.getElementById('codigo').value = c.CON_codigo || '';
    document.getElementById('tipo_pessoa_rj').value = c.CON_tipo_pessoa_rj || '';
    document.getElementById('inscricao_municipal').value = c.CON_inscricao_municipal || '';
    document.getElementById('cpf_cnpj').value = formatarCpfCnpj(c.CON_cpf_cnpj) || '';
    document.getElementById('razao_social').value = c.CON_razao_social || '';
    document.getElementById('nome_fantasia').value = c.CON_nome_fantasia || '';
    document.getElementById('inscricao_estadual').value = c.CON_inscricao_estadual || '';
    document.getElementById('endereco').value = c.CON_endereco || '';
    document.getElementById('numero').value = c.CON_numero || '';
    document.getElementById('complemento').value = c.CON_complemento || '';
    document.getElementById('bairro').value = c.CON_bairro || '';
    document.getElementById('cidade').value = c.CON_cidade || '';
    // estado field
    if (document.getElementById('estado')) document.getElementById('estado').value = c.CON_Estado || c.CON_estado || '';
    document.getElementById('cep').value = formatarCep(c.CON_cep) || '';
    document.getElementById('telefone1').value = formatarTelefone(c.CON_telefone1) || '';
    document.getElementById('telefone2').value = formatarTelefone(c.CON_telefone2) || '';
    document.getElementById('email').value = c.CON_email || '';
    document.getElementById('estado_civil').value = c.CON_estado_civil || '';
    document.getElementById('status').value = c.CON_status || 'ativo';
    document.getElementById('data_baixa').value = c.CON_data_baixa || '';

    const tipoValForm = (c.CON_tipo_pessoa || '').toString().toLowerCase();
    const isFisicaForm = tipoValForm === 'f' || tipoValForm.startsWith('fis') || tipoValForm.includes('fís') || tipoValForm.includes('fisica');
    const radioId = isFisicaForm ? 'pessoa_fisica' : 'pessoa_juridica';
    if (document.getElementById(radioId)) document.getElementById(radioId).checked = true;
    alterarTipoPessoa();
    toggleDataBaixa();
}

// ======================== CNAEs ========================
// Carrega o catálogo de CNAEs pré-cadastrados do banco
function carregarCatalogoCNAEs() {
    fetch(API_CONTRIBUINTES + '/cnaes')
        .then(res => {
            if (!res.ok) {
                throw new Error('Endpoint não encontrado: ' + res.status);
            }
            return res.json();
        })
        .then(json => {
            if (json.success && json.data) {
                const cnaesDoBanco = Array.isArray(json.data) ? json.data : [];
                
                // helper to find first available field among candidates
                const pick = (obj, candidates, fallback = '') => {
                    for (const k of candidates) {
                        if (!obj) continue;
                        if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null && String(obj[k]).toString().trim() !== '') return String(obj[k]);
                    }
                    return fallback;
                };
                
                catalogoCNAEs = cnaesDoBanco.map((c, idx) => ({
                    id: (pick(c, ['id', 'ID', 'CNAE_ID', 'cnae_id']) || ('pre_' + idx)).toString(),
                    numero: pick(c, ['numero', 'codigo', 'CNAE_Numero', 'CNAE_NUMERO', 'cnae_numero']) || `${String(idx + 1).padStart(4, '0')}`,
                    nome: pick(c, ['nome', 'descricao', 'CNAE_Descricao', 'CNAE_DESCRICAO', 'cnae_descricao']) || ''
                }));
            } else {
                catalogoCNAEs = [];
            }
        })
        .catch(err => {
            console.error('Erro ao carregar catálogo de CNAEs:', err);
            catalogoCNAEs = [];
        });
}

// Modal de seleção de CNAEs
function abrirModalSeletorCNAEs() {
    if (catalogoCNAEs.length === 0) carregarCatalogoCNAEs();
    document.getElementById('modalSeletorCNAEs').style.display = 'block';
    document.getElementById('buscaCNAEs').value = '';
    renderizarListaCNAEsCatalogo();
}

function fecharModalSeletorCNAEs() {
    document.getElementById('modalSeletorCNAEs').style.display = 'none';
    renderizarTabelaCNAEs();
}

function filtrarCNAEsCatalogo() {
    renderizarListaCNAEsCatalogo();
}

function renderizarListaCNAEsCatalogo() {
    const container = document.getElementById('listaCNAEsCatalogo');
    if (!container) return;
    const q = (document.getElementById('buscaCNAEs').value || '').toLowerCase();
    container.innerHTML = '';
    const list = catalogoCNAEs.filter(c => (`${c.numero} ${c.nome}`).toLowerCase().includes(q));
    
    if (list.length === 0) {
        container.innerHTML = '<div class="text-muted">Nenhum CNAE encontrado</div>';
        return;
    }
    
    list.forEach(c => {
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.justifyContent = 'space-between';
        div.style.alignItems = 'center';
        div.style.padding = '8px 4px';
        div.style.borderBottom = '1px solid #eee';
        
        const isSelected = todosCNAEs.some(t => t.id === c.id);
        
        const chk = document.createElement('input');
        chk.type = 'checkbox';
        chk.checked = isSelected;
        chk.style.marginRight = '8px';
        chk.onchange = (e) => {
            if (e.target.checked) {
                // Adicionar CNAE
                if (!todosCNAEs.find(t => t.id === c.id)) {
                    todosCNAEs.push({ id: c.id, numero: c.numero, nome: c.nome, tipo: 'secundario' });
                    // Se for o primeiro, marca como primário
                    if (todosCNAEs.length === 1) {
                        todosCNAEs[0].tipo = 'primario';
                    }
                }
            } else {
                // Remover CNAE
                const era_primario = todosCNAEs.find(t => t.id === c.id)?.tipo === 'primario';
                todosCNAEs = todosCNAEs.filter(t => t.id !== c.id);
                // Se era primário, marca o primeiro restante como primário
                if (era_primario && todosCNAEs.length > 0) {
                    todosCNAEs[0].tipo = 'primario';
                }
            }
            renderizarListaCNAEsCatalogo();
            renderizarTabelaCNAEs();
        };
        
        const label = document.createElement('div');
        label.style.flex = '1';
        label.innerHTML = `<strong>${c.numero}</strong> - ${c.nome}`;
        
        div.appendChild(chk);
        div.appendChild(label);
        container.appendChild(div);
    });
}

// Tabela de CNAEs selecionados
function renderizarTabelaCNAEs() {
    const tbody = document.querySelector('#cnaesTable tbody');
    
    if (!tbody) {
        console.error('Elemento #cnaesTable tbody não encontrado');
        return;
    }
    
    tbody.innerHTML = '';
    
    if (todosCNAEs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhum CNAE selecionado</td></tr>';
        return;
    }
    
    todosCNAEs.forEach(cnae => {
        const tr = document.createElement('tr');
        
        tr.innerHTML = `
            <td class="text-center" style="font-size: 0.9em; font-weight: bold;">
                ${cnae.numero}
            </td>
            <td style="font-size: 0.9em;">
                ${cnae.nome}
            </td>
            <td class="text-center">
                <div class="form-check form-check-inline">
                    <input type="radio" 
                           id="cnae_principal_${cnae.id}"
                           name="cnae_principal"
                           value="${cnae.id}"
                           ${cnae.tipo === 'primario' ? 'checked' : ''}
                           onchange="marcarCNAEPrimario('${cnae.id}')"
                           class="form-check-input">
                    <label class="form-check-label" for="cnae_principal_${cnae.id}">
                        Sim
                    </label>
                </div>
            </td>
            <td class="text-center">
                <button class="btn btn-sm btn-danger" 
                        onclick="removerCNAESelecionado('${cnae.id}')"
                        title="Remover CNAE">
                    <i class="fas fa-trash"></i> Remover
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function marcarCNAEPrimario(cnaeId) {
    todosCNAEs.forEach(c => c.tipo = (c.id === cnaeId) ? 'primario' : 'secundario');
    renderizarTabelaCNAEs();
}

function removerCNAESelecionado(cnaeId) {
    const era_primario = todosCNAEs.find(c => c.id === cnaeId)?.tipo === 'primario';
    todosCNAEs = todosCNAEs.filter(c => c.id !== cnaeId);
    // Se era primário, marca o primeiro restante como primário
    if (era_primario && todosCNAEs.length > 0) {
        todosCNAEs[0].tipo = 'primario';
    }
    renderizarTabelaCNAEs();
}

function toggleCNAE(cnaeId, selecionado) {
    if (selecionado) {
        // Verifica se já existe
        if (!cnaesSelecionados.find(c => c.id === cnaeId)) {
            // Se for o primeiro CNAE, define como primário
            if (cnaesSelecionados.length === 0) {
                cnaesSelecionados.push({
                    id: cnaeId,
                    tipo: 'primario'
                });
            } else {
                cnaesSelecionados.push({
                    id: cnaeId,
                    tipo: 'secundario'
                });
            }
        }
    } else {
        // Remove da lista de selecionados
        cnaesSelecionados = cnaesSelecionados.filter(c => c.id !== cnaeId);
        
        // Se era o primário e ainda há outros CNAEs, define o primeiro como primário
        if (cnaesSelecionados.length > 0) {
            const primeiroCNAE = cnaesSelecionados[0];
            if (primeiroCNAE.tipo !== 'primario') {
                primeiroCNAE.tipo = 'primario';
            }
        }
    }
    
    renderizarTabelaCNAEs();
}

function alterarTipoCNAE(cnaeId, novoTipo) {
    const cnaeIndex = cnaesSelecionados.findIndex(c => c.id === cnaeId);
    
    if (cnaeIndex !== -1) {
        if (novoTipo === 'primario') {
            // Remove primário atual
            cnaesSelecionados.forEach(c => {
                if (c.tipo === 'primario') {
                    c.tipo = 'secundario';
                }
            });
        }
        
        cnaesSelecionados[cnaeIndex].tipo = novoTipo;
        renderizarTabelaCNAEs();
    }
}

function filtrarCNAEs() {
    renderizarTabelaCNAEs();
}

// ======================== ATIVIDADES ========================
// Carrega catálogo (lista mestre) de atividades do endpoint (cliente-side catalog)
function carregarCatalogoAtividades() {
    fetch(API_ATIVIDADES)
        .then(res => res.json())
        .then(data => {
            const atividadesDoBanco = Array.isArray(data) ? data : data.data || [];

            // helper to find first available field among candidates
            const pick = (obj, candidates, fallback = '') => {
                for (const k of candidates) {
                    if (!obj) continue;
                    if (Object.prototype.hasOwnProperty.call(obj, k) && obj[k] != null && String(obj[k]).toString().trim() !== '') return String(obj[k]);
                }
                return fallback;
            };

            catalogoAtividades = atividadesDoBanco.map((a, idx) => ({
                id: (pick(a, ['id', 'ID', 'Ati_Id', 'ATI_Id', 'atividade_id']) || ('pre_' + idx)).toString(),
                numero: pick(a, ['numero', 'codigo', 'codigo_atividade', 'codigoAtividade', 'ATI_Codigo', 'ATI_CODIGO', 'ati_codigo']) || `${String(idx + 1).padStart(3, '0')}`,
                nome: pick(a, ['nome', 'descricao', 'atividade_descricao', 'atividade', 'ATI_Descricao', 'ATI_DESCRICAO', 'ATI_Descricao', 'ati_descricao', 'descricao_atividade']) || ''
            }));

            renderizarSelectAtividades();
        })
        .catch(err => {
            console.error('Erro ao carregar catálogo de atividades:', err);
            catalogoAtividades = [];
            renderizarSelectAtividades();
        });
}

// (deprecated) - direct add removed from main modal. Use catalog manager.

// Renderiza a seleção de atividades no modal principal
function renderizarSelectAtividades() {
    const sel = document.getElementById('atividadeSelect');
    if (!sel) return;

    const prevValue = sel.value;
    sel.innerHTML = '';
    const emptyOpt = document.createElement('option');
    emptyOpt.value = '';
    emptyOpt.textContent = '(selecione)';
    sel.appendChild(emptyOpt);

    catalogoAtividades.forEach(a => {
        const opt = document.createElement('option');
        opt.value = a.id;
        const name = a.nome || a.descricao || (todasAtividades.find(t => t.id === a.id) || {}).nome || '(sem descrição)';
        opt.textContent = `${a.numero} - ${name}`;
        if (todasAtividades.some(t => t.id === a.id)) opt.selected = true;
        if (prevValue && prevValue === a.id) opt.selected = true;
        sel.appendChild(opt);
    });

    // If there is a selected activity in todasAtividades, set select
    if (todasAtividades.length > 0) {
        sel.value = todasAtividades[0].id;
        // populate date field
        const dataInput = document.getElementById('atividade_data');
        if (dataInput) dataInput.value = todasAtividades[0].data || '';
    } else {
        sel.value = prevValue || '';
    }
}

// Backwards-compatible entry point used throughout code
function renderizarTabelaAtividades() {
    // If the select exists, render into it
    if (document.getElementById('atividadeSelect')) {
        renderizarSelectAtividades();
        return;
    }

    // Fallback: original table rendering (if still present)
    const tbody = document.querySelector('#atividadesTable tbody');
    if (!tbody) return;
    tbody.innerHTML = '';
    if (todasAtividades.length === 0) {
        tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted">Nenhuma atividade cadastrada</td></tr>';
        return;
    }
    todasAtividades.forEach((atividade) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${atividade.numero}</td><td>${atividade.nome}</td><td>${atividade.principal ? 'Sim' : ''}</td><td></td>`;
        tbody.appendChild(tr);
    });
}

// principal select removed: single activity model uses only atividadeSelect + data

// now single-activity: helper to set selected activity
function selecionarAtividadePorId(id) {
    // find in catalog
    const a = catalogoAtividades.find(x => x.id === id) || todasAtividades.find(x => x.id === id);
    if (!a) return;
    // replace todasAtividades with single entry (preserve existing date if any)
    const existing = todasAtividades.find(x => x.id === id);
    const data = existing ? existing.data : (document.getElementById('atividade_data') ? document.getElementById('atividade_data').value : '');
    todasAtividades = [{ id: a.id, numero: a.numero, nome: a.nome, data: data }];
    renderizarSelectAtividades();
}

function editarAtividade(atividadeId) {
    const atividade = todasAtividades.find(a => a.id === atividadeId);
    if (!atividade) return;
    
    const novaDescricao = prompt('Editar descrição da atividade:', atividade.nome);
    
    if (novaDescricao === null) return; // Cancelado
    
    const descricaoTrimmed = novaDescricao.trim();
    if (!descricaoTrimmed) {
        alert('A descrição não pode estar vazia');
        return;
    }
    
    // Verifica se já existe outra atividade com este nome
    const jaExiste = todasAtividades.some(a => 
        a.id !== atividadeId && 
        a.nome.toLowerCase() === descricaoTrimmed.toLowerCase()
    );
    
    if (jaExiste) {
        alert('Já existe outra atividade com este nome');
        return;
    }
    
    atividade.nome = descricaoTrimmed;
    renderizarTabelaAtividades();
}

function excluirAtividade(atividadeId) {
    const atividade = todasAtividades.find(a => a.id === atividadeId);
    if (!atividade) return;
    
    if (!confirm(`Deseja excluir a atividade "${atividade.nome}"?`)) {
        return;
    }
    
    const eraPrincipal = atividade.principal;
    todasAtividades = todasAtividades.filter(a => a.id !== atividadeId);
    
    // Se era a principal, marca outra como principal
    if (eraPrincipal && todasAtividades.length > 0) {
        todasAtividades[0].principal = true;
    }
    
    renderizarTabelaAtividades();
}

// =================== NOVAS FUNÇÕES: Gerenciamento do catálogo (modal) ===================
function abrirModalAtividades() {
    // ensure catalog is loaded before rendering the modal
    carregarCatalogoAtividades();
    document.getElementById('modalAtividades').style.display = 'block';
    document.getElementById('buscaAtividades').value = '';
    document.getElementById('formAdicionarAtividade').style.display = 'none';
    renderizarListaCatalogo();
}
function fecharModalAtividades() { document.getElementById('modalAtividades').style.display = 'none'; }

function mostrarFormAdicionarAtividade() { document.getElementById('formAdicionarAtividade').style.display = 'block'; document.getElementById('novo_nome_atividade').focus(); }
function ocultarFormAdicionarAtividade() { document.getElementById('formAdicionarAtividade').style.display = 'none'; document.getElementById('novo_numero_atividade').value=''; document.getElementById('novo_nome_atividade').value=''; }

function filtrarCatalogoAtividades() { renderizarListaCatalogo(); }

function renderizarListaCatalogo() {
    const container = document.getElementById('listaCatalogoAtividades');
    if (!container) return;
    const q = (document.getElementById('buscaAtividades').value || '').toLowerCase();
    container.innerHTML = '';
    const list = catalogoAtividades.filter(a => (`${a.numero} ${a.nome}`).toLowerCase().includes(q));
    if (list.length === 0) {
        container.innerHTML = '<div class="text-muted">Nenhuma atividade no catálogo</div>';
        return;
    }
    list.forEach(a => {
        const div = document.createElement('div');
        div.style.display = 'flex';
        div.style.justifyContent = 'space-between';
        div.style.alignItems = 'center';
        div.style.padding = '6px 4px';
        div.style.borderBottom = '1px solid #eee';
        const name = a.nome || a.descricao || (todasAtividades.find(t => t.id === a.id) || {}).nome || '(sem descrição)';
        div.innerHTML = `<div style="flex:1"><strong>${a.numero}</strong> - ${name}</div>`;

        const actions = document.createElement('div');
        actions.style.display = 'flex';
        actions.style.gap = '6px';

        const btnSelect = document.createElement('button');
        btnSelect.type = 'button';
        btnSelect.className = 'btn btn-sm btn-outline-primary';
        btnSelect.textContent = 'Selecionar';
        btnSelect.onclick = () => {
            // single-activity model: set as the selected activity (no date assigned here)
            todasAtividades = [{ id: a.id, numero: a.numero, nome: a.nome }];
            renderizarSelectAtividades();
            renderizarListaCatalogo();
        };

        const btnEdit = document.createElement('button');
        btnEdit.type = 'button';
        btnEdit.className = 'btn btn-sm btn-warning';
        btnEdit.textContent = 'Editar';
        btnEdit.onclick = () => abrirModalEditarAtividade(a.id);

        const btnDel = document.createElement('button');
        btnDel.type = 'button';
        btnDel.className = 'btn btn-sm btn-danger';
        btnDel.textContent = 'Excluir';
        btnDel.onclick = () => {
            if (!confirm(`Excluir atividade "${a.numero} - ${a.nome}" do catálogo?`)) return;
            // remove from catalogo
            catalogoAtividades = catalogoAtividades.filter(x => x.id !== a.id);
            // also remove from selected if present
            todasAtividades = todasAtividades.filter(x => x.id !== a.id);
            renderizarSelectAtividades();
            renderizarListaCatalogo();
        };

        actions.appendChild(btnSelect);
        actions.appendChild(btnEdit);
        actions.appendChild(btnDel);
        div.appendChild(actions);
        container.appendChild(div);
    });
}

function adicionarAtividadeCatalogo() {
    const numero = document.getElementById('novo_numero_atividade').value.trim() || `${String(catalogoAtividades.length + 1).padStart(3,'0')}`;
    const nome = document.getElementById('novo_nome_atividade').value.trim();
    if (!nome) return alert('Preencha a descrição da atividade.');

    // evitar duplicatas visuais
    if (catalogoAtividades.some(a => a.nome.toLowerCase() === nome.toLowerCase())) return alert('Já existe uma atividade com esse nome no catálogo.');

    const novo = { id: 'novo_' + Date.now(), numero, nome };
    catalogoAtividades.push(novo);
    // seleciona como a atividade atual (data stays empty; user supplies date in main form)
    todasAtividades = [{ id: novo.id, numero: novo.numero, nome: novo.nome }];

    document.getElementById('novo_numero_atividade').value = '';
    document.getElementById('novo_nome_atividade').value = '';
    document.getElementById('formAdicionarAtividade').style.display = 'none';
    renderizarListaCatalogo();
    renderizarSelectAtividades();
}

// edição
let atividadeEditandoId = null;
function abrirModalEditarAtividade(id) {
    atividadeEditandoId = id;
    const a = catalogoAtividades.find(x => x.id === id) || todasAtividades.find(x => x.id === id);
    if (!a) return alert('Atividade não encontrada');
    document.getElementById('editar_numero_atividade').value = a.numero || '';
    document.getElementById('editar_nome_atividade').value = a.nome || '';
    document.getElementById('modalEditarAtividade').style.display = 'block';
}
function fecharModalEditarAtividade() { document.getElementById('modalEditarAtividade').style.display = 'none'; atividadeEditandoId = null; }
function salvarEdicaoAtividadeCatalogo() {
    const numero = document.getElementById('editar_numero_atividade').value.trim();
    const nome = document.getElementById('editar_nome_atividade').value.trim();
    if (!nome) return alert('Descrição obrigatória');

    // update in catalog if exists
    let updated = false;
    for (let i=0;i<catalogoAtividades.length;i++) {
        if (catalogoAtividades[i].id === atividadeEditandoId) {
            catalogoAtividades[i].numero = numero;
            catalogoAtividades[i].nome = nome;
            updated = true;
            break;
        }
    }
    // update in selected as well
    for (let i=0;i<todasAtividades.length;i++) {
        if (todasAtividades[i].id === atividadeEditandoId) {
            todasAtividades[i].numero = numero;
            todasAtividades[i].nome = nome;
            updated = true;
        }
    }

    if (!updated) {
        // if not found in catalog, add as novo
        catalogoAtividades.push({ id: atividadeEditandoId || ('novo_'+Date.now()), numero, nome });
    }

    fecharModalEditarAtividade();
    renderizarListaCatalogo();
    renderizarSelectAtividades();
}

// Quando o usuário muda a seleção no select principal
document.addEventListener('change', (e) => {
    if (e.target && e.target.id === 'atividadeSelect') {
        const id = e.target.value;
        if (!id) {
            todasAtividades = [];
            return;
        }
        selecionarAtividadePorId(id);
    }
    // quando usuário altera data manualmente, propagar para o objeto selecionado
    if (e.target && e.target.id === 'atividade_data') {
        if (todasAtividades.length > 0) {
            todasAtividades[0].data = e.target.value;
        }
    }
});

// ======================== UTILITÁRIOS ========================
function mostrarLoading(mostrar) {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = mostrar ? 'block' : 'none';
}

function alterarTipoPessoa() {
    const pessoaFisica = document.getElementById('pessoa_fisica').checked;
    const grupoTipoPessoaRj = document.getElementById('grupo_tipo_pessoa_rj');
    if (pessoaFisica) {
        grupoTipoPessoaRj.style.display = 'none';
        document.getElementById('tipo_pessoa_rj').value = '';
    } else {
        grupoTipoPessoaRj.style.display = 'block';
    }
}

function toggleDataBaixa() {
    const statusSelect = document.getElementById('status');
    const divDataBaixa = document.getElementById('div_data_baixa');
    const dataBaixaInput = document.getElementById('data_baixa');
    if (statusSelect.value === 'inativo') {
        divDataBaixa.style.display = 'block';
        dataBaixaInput.setAttribute('required', 'required');
    } else {
        divDataBaixa.style.display = 'none';
        dataBaixaInput.removeAttribute('required');
        dataBaixaInput.value = '';
    }
}

function forcarCaixaAlta() {
    document.querySelectorAll('input[type="text"]').forEach(input => {
        input.value = input.value.toUpperCase();
        input.addEventListener('input', () => { input.value = input.value.toUpperCase(); });
        input.addEventListener('blur', () => { input.value = input.value.toUpperCase(); });
    });
}

function processarDadosCaixaAlta(dados) {
    ['inscricao_municipal', 'razao_social', 'nome_fantasia', 'inscricao_estadual', 'endereco', 'complemento', 'bairro', 'cidade', 'estado'].forEach(c => {
        if (dados[c]) dados[c] = dados[c].toUpperCase();
    });
    return dados;
}

// ======================== EVENTOS ========================
document.addEventListener('DOMContentLoaded', () => {
    carregarContribuintes();
    alterarTipoPessoa();
    toggleDataBaixa();
    document.querySelectorAll('input[name="tipo_pessoa"]').forEach(r => r.addEventListener('change', alterarTipoPessoa));
    document.getElementById('status').addEventListener('change', toggleDataBaixa);
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) btnLogout.addEventListener('click', fazerLogout);
});

// ======================== LOGOUT ========================
function fazerLogout() {
        if (confirm('Tem certeza que deseja sair do sistema?')) {
            mostrarLoading(true);
            
            fetch(API_BASE_URL + 'auth/logout', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'include'
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error('Erro no logout: ' + res.status);
                }
                return res.json();
            })
            .then(json => {
                mostrarLoading(false);
                if (json.success) {
                    alert('Logout realizado com sucesso!');
                    // substitui a entrada de histórico para evitar voltar para páginas autenticadas
                    window.location.replace(LOGOUT_URL);
                } else {
                    alert('Erro: ' + json.message);
                }
            })
            .catch(err => {
                mostrarLoading(false);
                console.error('Erro no logout:', err);
                window.location.replace(LOGOUT_URL);
            });
        }
    }