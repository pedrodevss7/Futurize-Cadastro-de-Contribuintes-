<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="X-CSRF-TOKEN" content="<?= csrf_token() ?>">
  <title>Cadastro de Contribuintes - STM</title>
  <link rel="stylesheet" href="http://localhost/Futurize.STM/public/css/dashboard.css?v=<?= time() ?>">
  <script src="http://localhost/Futurize.STM/public/js/dashboard.js?v=<?= time() ?>"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
</head>
<body>
<!-- Botão de Logout -->
<button id="btnLogout" class="btn btn-danger btn-sm" style="position: fixed; top: 15px; right: 15px; z-index: 1000;">
    <i class="fas fa-sign-out-alt"></i> Sair
</button>

  <div class="container">
    
    <!-- Header -->
    <header>
      <!-- Logo STM à esquerda -->
      <div class="logo-container">
        <img class="logo" src="http://localhost/Futurize.STM/public/images/STM_LOGO.png" alt="STM" onerror="handleImageError(this, 'placeholder1')">
        <div id="placeholder1" class="image-placeholder" style="display: none;">
          Imagem não encontrada<br>STM_LOGO.png
        </div>
      </div>
      
      <!-- Logo Futurize à direita -->
      <div>
        <img class="futurize-logo" src="http://localhost/Futurize.STM/public/images/Logo_FUTURIZE.png" alt="Futurize" onerror="handleImageError(this, 'placeholder3')">
        <div id="placeholder3" class="image-placeholder" style="display: none; height: 60px; width: 150px;">
          Logo Futurize
        </div>
      </div>
    </header>
    
    <!-- Notification Bar -->
    <div class="notification">
      <i class="fas fa-info-circle"></i> Sistema de Tributação Municipal - STM - Cadastro de Contribuintes
    </div>
    
    <!-- Main Content -->
    <div class="content">
      <!-- Sidebar -->
      <div class="sidebar">
        <h3><i class="fas fa-users"></i> CONTRIBUINTES</h3>
        <ul class="sidebar-menu">
          <li><a href="#" class="active"><i class="fas fa-angle-right"></i> Cadastrar</a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i> Consultar</a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i> Relatórios</a></li>
        </ul>
        
        <h3><i class="fas fa-file-invoice-dollar"></i> EXEMPLO 1</h3>
        <ul class="sidebar-menu">
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
        </ul>
        
        <h3><i class="fas fa-chart-bar"></i> EXEMPLO 2</h3>
        <ul class="sidebar-menu">
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i>Futurize - sistema de tributação </a></li>
        </ul>
        
        <h3><i class="fas fa-cog"></i> CONFIGURAÇões</h3>
        <ul class="sidebar-menu">
          <li><a href="#"><i class="fas fa-angle-right"></i> Parâmetros</a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i> Usuários</a></li>
          <li><a href="#"><i class="fas fa-angle-right"></i> Backup</a></li>
        </ul>
      </div>
      
      <!-- Main Area -->
      <div class="main">
        <h2 class="page-title">CADASTRO DE CONTRIBUINTE</h2>
        
        <!-- Botão para abrir modal -->
        <div style="margin-bottom: 20px;">
          <button class="btn btn-primary" onclick="abrirModalCadastro()">
            <i class="fas fa-plus"></i> Novo Contribuinte
          </button>
        </div>
        
        <!-- Lista de Contribuintes -->
        <div class="search-filters">
          <div class="search-box">
            <input type="text" id="inputPesquisa" placeholder="Pesquisar contribuinte...">
            <button onclick="pesquisarContribuinte()"><i class="fas fa-search"></i></button>
            <button id="btnVoltarLista" onclick="voltarLista()" style="display:none;"><i class="fas fa-undo"></i> Voltar</button>
          </div>
          
          <select class="filter-select" id="filterSelect" onchange="filtrarContribuintes()">
            <option value="all">Todos</option>
            <option value="fisica">Pessoa Física</option>
            <option value="juridica">Pessoa Jurídica</option>
            <option value="ativo">Ativos</option>
            <option value="inativo">Inativos</option>
          </select>
        </div>
        
        <div class="table-container">
          <table id="contribuintesTable">
            <thead>
              <tr>
                <th>Código</th>
                <th>Nome/Razão Social</th>
                <th>CPF/CNPJ</th>
                <th>Tipo</th>
                <th>Status</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Os dados serão preenchidos via JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
    
    <!-- Footer -->
    <footer>
      <p>©Futurize 2025 - Todos os Direitos Reservados </p>
    </footer>
  </div>

  <!-- Modal para Cadastro de Contribuintes -->
  <div id="modalContribuinte" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <span class="close" onclick="fecharModal()">&times;</span>
        <h3 class="modal-title" id="modalTitulo">CADASTRAR CONTRIBUINTE</h3>
      </div>
      <div class="modal-body">
        <form id="contribuinteForm">
          <div class="form-section">
            <h3 class="form-section-title">Tipo de Pessoa</h3>
            <div class="form-row">
              <div class="form-group">
                <div class="radio-group">
                  <div class="radio-option">
                    <input type="radio" id="pessoa_fisica" name="tipo_pessoa" value="fisica" checked>
                    <label for="pessoa_fisica">Física</label>
                  </div>
                  <div class="radio-option">
                    <input type="radio" id="pessoa_juridica" name="tipo_pessoa" value="juridica">
                    <label for="pessoa_juridica">Jurídica</label>
                  </div>
                </div>
              </div>
              
              <div class="form-group" id="grupo_tipo_pessoa_rj">
                <label for="tipo_pessoa_rj">Tipo Pessoa RJ</label>
                <select id="tipo_pessoa_rj" name="tipo_pessoa_rj" class="form-control">
                  <option value="">Selecione</option>
                  <option value="simples_nacional">Simples Nacional</option>
                  <option value="mei">MEI</option>
                  <option value="outros">Outros</option>
                </select>
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3 class="form-section-title">Status do Contribuinte</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" class="form-control" required onchange="toggleDataBaixa()">
                  <option value="ativo">Ativo</option>
                  <option value="inativo">Inativo</option>
                </select>
              </div>
              
              <div class="form-group" id="div_data_baixa" style="display:none;">
                <label for="data_baixa">Data de Baixa *</label>
                <input type="date" id="data_baixa" name="data_baixa" class="form-control" 
                       max="<?= date('Y-m-d') ?>" 
                       placeholder="Selecione a data de baixa">
                <small class="form-text text-muted">Obrigatório para status inativo</small>
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3 class="form-section-title">Dados Cadastrais</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="codigo">Código</label>
                <input type="text" id="codigo" name="codigo" readonly>
              </div>
              
              <div class="form-group">
                <label for="inscricao_municipal">Inscrição Municipal</label>
                <input type="text" id="inscricao_municipal" name="inscricao_municipal" class="campo-caixa-alta">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="cpf_cnpj">CPF/CNPJ *</label>
                <input type="text" id="cpf_cnpj" name="cpf_cnpj" required class="campo-caixa-alta">
              </div>
              
              <div class="form-group">
                <label for="razao_social">Nome/Razão Social *</label>
                <input type="text" id="razao_social" name="razao_social" required class="campo-caixa-alta">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="nome_fantasia">Nome Fantasia</label>
                <input type="text" id="nome_fantasia" name="nome_fantasia" class="campo-caixa-alta">
              </div>
              
              <div class="form-group">
                <label for="inscricao_estadual">Inscrição Estadual</label>
                <input type="text" id="inscricao_estadual" name="inscricao_estadual" class="campo-caixa-alta">
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3 class="form-section-title">Endereço</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="endereco">Endereço *</label>
                <input type="text" id="endereco" name="endereco" required class="campo-caixa-alta">
              </div>
              
              <div class="form-group">
                <label for="numero">Número *</label>
                <input type="text" id="numero" name="numero" required class="campo-caixa-alta">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="complemento">Complemento</label>
                <input type="text" id="complemento" name="complemento" class="campo-caixa-alta">
              </div>
              
              <div class="form-group">
                <label for="bairro">Bairro *</label>
                <input type="text" id="bairro" name="bairro" required class="campo-caixa-alta">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="cidade">Cidade *</label>
                <input type="text" id="cidade" name="cidade" required class="campo-caixa-alta">
              </div>
              
              <div class="form-group">
                <label for="estado">Estado</label>
                <select id="estado" name="estado" class="form-control">
                  <option value="">Selecione...</option>
                  <option value="AC">AC</option>
                  <option value="AL">AL</option>
                  <option value="AP">AP</option>
                  <option value="AM">AM</option>
                  <option value="BA">BA</option>
                  <option value="CE">CE</option>
                  <option value="DF">DF</option>
                  <option value="ES">ES</option>
                  <option value="GO">GO</option>
                  <option value="MA">MA</option>
                  <option value="MT">MT</option>
                  <option value="MS">MS</option>
                  <option value="MG">MG</option>
                  <option value="PA">PA</option>
                  <option value="PB">PB</option>
                  <option value="PR">PR</option>
                  <option value="PE">PE</option>
                  <option value="PI">PI</option>
                  <option value="RJ">RJ</option>
                  <option value="RN">RN</option>
                  <option value="RS">RS</option>
                  <option value="RO">RO</option>
                  <option value="RR">RR</option>
                  <option value="SC">SC</option>
                  <option value="SP">SP</option>
                  <option value="SE">SE</option>
                  <option value="TO">TO</option>
                </select>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep">
              </div>
            </div>
          </div>
          
          <div class="form-section">
            <h3 class="form-section-title">Contato</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="telefone1">Telefone 1</label>
                <input type="text" id="telefone1" name="telefone1">
              </div>
              
              <div class="form-group">
                <label for="telefone2">Telefone 2</label>
                <input type="text" id="telefone2" name="telefone2">
              </div>
            </div>
            
            <div class="form-row">
              <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email">
              </div>
              
              <div class="form-group">
                <label for="estado_civil">Estado Civil</label>
                <select id="estado_civil" name="estado_civil">
                  <option value="">Selecione...</option>
                  <option value="solteiro">Solteiro(a)</option>
                  <option value="casado">Casado(a)</option>
                  <option value="divorciado">Divorciado(a)</option>
                  <option value="viuvo">Viúvo(a)</option>
                </select>
              </div>
            </div>
          </div>
        
<div class="form-section">
    <h3 class="form-section-title">Atividades do Contribuinte</h3>

  <!-- Select simples de atividade + botão para gerenciar/adicionar -->
  <div class="form-group">
    <label>Atividade:</label>
    <div style="display:flex; gap:8px; align-items:flex-start;">
      <div style="flex:1;">
        <select id="atividadeSelect" class="form-control" style="width:100%;">
          <!-- opções carregadas por JS -->
        </select>
      </div>
      <div style="width:150px; display:flex; flex-direction:column; gap:8px;">
        <button type="button" class="btn btn-secondary" onclick="abrirModalAtividades()">
          <i class="fas fa-plus"></i> Adicionar
        </button>
        <button type="button" class="btn btn-outline-secondary" onclick="carregarCatalogoAtividades()">
          <i class="fas fa-sync"></i> Atualizar
        </button>
      </div>
    </div>
    <small class="form-text text-muted">Selecione uma atividade já cadastrada ou clique em Adicionar para criar uma nova.</small>
  </div>

  <div class="form-group">
    <label>Data da Atividade:</label>
    <input type="date" id="atividade_data" name="atividade_data" class="form-control">
    <small class="form-text text-muted">Data associada à atividade selecionada.</small>
  </div>
</div>

<!-- Modal de Gerenciamento de Atividades (CRUD cliente) -->
<div id="modalAtividades" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:800px;">
    <div class="modal-header">
      <span class="close" onclick="fecharModalAtividades()">&times;</span>
      <h3 class="modal-title">Gerenciar Atividades</h3>
    </div>
    <div class="modal-body">
      <div style="display:flex; gap:12px; margin-bottom:12px;">
        <input type="text" id="buscaAtividades" placeholder="Buscar atividades..." class="form-control" oninput="filtrarCatalogoAtividades()">
        <button type="button" class="btn btn-success" onclick="mostrarFormAdicionarAtividade()"><i class="fas fa-plus"></i> Nova</button>
      </div>

      <div id="formAdicionarAtividade" style="display:none; margin-bottom:12px;">
        <div class="form-row" style="gap:8px; align-items:center;">
          <input type="text" id="novo_numero_atividade" placeholder="Número" class="form-control" style="width:120px;">
          <input type="text" id="novo_nome_atividade" placeholder="Descrição" class="form-control">
          <button type="button" class="btn btn-primary" onclick="adicionarAtividadeCatalogo()">Salvar</button>
          <button type="button" class="btn btn-secondary" onclick="ocultarFormAdicionarAtividade()">Cancelar</button>
        </div>
      </div>

      <div id="listaCatalogoAtividades" style="max-height:360px; overflow:auto;">
        <!-- Lista gerada por JS -->
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="fecharModalAtividades()">Fechar</button>
    </div>
  </div>
</div>

<!-- Modal de Edição de Atividade -->
<div id="modalEditarAtividade" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:560px;">
    <div class="modal-header">
      <span class="close" onclick="fecharModalEditarAtividade()">&times;</span>
      <h3 class="modal-title">Editar Atividade</h3>
    </div>
    <div class="modal-body">
      <div class="form-row" style="gap:8px;">
        <input type="text" id="editar_numero_atividade" placeholder="Número" class="form-control" style="width:120px;">
        <input type="text" id="editar_nome_atividade" placeholder="Descrição" class="form-control">
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="fecharModalEditarAtividade()">Cancelar</button>
      <button type="button" class="btn btn-primary" onclick="salvarEdicaoAtividadeCatalogo()">Salvar</button>
    </div>
  </div>
</div>

<div class="form-section">
    <h3 class="form-section-title">CNAEs do Contribuinte</h3>
    
    <!-- Botão para abrir seletor de CNAEs -->
    <div class="form-group">
        <button type="button" class="btn btn-secondary" onclick="abrirModalSeletorCNAEs()">
            <i class="fas fa-list"></i> Adicionar CNAEs
        </button>
        <small class="form-text text-muted">Clique para selecionar CNAEs do banco de dados.</small>
    </div>

    <!-- Tabela de CNAEs selecionados -->
    <div class="form-group">
        <label>CNAEs Selecionados:</label>
        <div class="table-container-fixed table-cnaes-fixed">
            <table id="cnaesTable" class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th width="18%">Número</th>
                        <th width="50%">Descrição</th>
                        <th width="18%">Principal</th>
                        <th width="14%">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- CNAEs selecionados serão carregados aqui -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mini-Modal de Seleção de CNAEs -->
<div id="modalSeletorCNAEs" class="modal" style="display:none;">
  <div class="modal-content" style="max-width:700px;">
    <div class="modal-header">
      <span class="close" onclick="fecharModalSeletorCNAEs()">&times;</span>
      <h3 class="modal-title">Selecionar CNAEs</h3>
    </div>
    <div class="modal-body">
      <div style="margin-bottom:12px;">
        <input type="text" id="buscaCNAEs" placeholder="Buscar CNAEs..." class="form-control" oninput="filtrarCNAEsCatalogo()">
      </div>
      <div id="listaCNAEsCatalogo" style="max-height:360px; overflow:auto;">
        <!-- Lista de CNAEs gerada por JS -->
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="fecharModalSeletorCNAEs()">Fechar</button>
    </div>
  </div>
</div>
  
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" onclick="fecharModal()">Cancelar</button>
        <button type="button" class="btn btn-success" onclick="salvarContribuinte()">Salvar</button>
      </div>
    </div>
  </div>

  <!-- Loading Spinner -->
  <div id="loadingSpinner" class="spinner" style="display: none;"></div>

  

</body>
</html>