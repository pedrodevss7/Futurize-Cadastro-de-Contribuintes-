<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta name="csrf-token" content="<?= csrf_hash() ?>">
    <meta name="csrf-token-name" content="<?= csrf_token() ?>">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Tributação Municipal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="http://localhost/Futurize.STM/public/css/home.css">
    <script src="http://localhost/Futurize.STM/public/js/home.js"></script>
  </head>

<body>
  <div class="container">
    
    <!-- Header Reduzido -->
    <header>
      <!-- Logo STM à esquerda -->
      <div class="logo-container">
        <!-- CORREÇÃO: URL ABSOLUTA CORRETA -->
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
      <i class="fas fa-info-circle"></i> Sistema de Tributação Municipal - STM
    </div>
    
    <!-- Main Content -->
    <div class="content">
      <!-- Sidebar -->
      <div class="sidebar">
        <h3><i class="fas fa-id-card"></i> MENU PRINCIPAL</h3>
        <ul class="sidebar-menu">
          <li><b href="#"><i class="fas fa-angle-right"></i> Pagina Inicial</b></li>
          <li><b href="#"><i class="fas fa-angle-right"></i> Solicitar Acesso</b></li>
        </ul>
        
        <h3><i class="fas fa-users"></i> ACESSOS</h3>
        <ul class="sidebar-menu">
          <li><b href="#"><i class="fas fa-angle-right"></i>Canal do(a) Servidor(a) </b></li>
          <li><b href="#"><i class="fas fa-angle-right"></i>Canal do(a) Administrador(a)</b></li>
        </ul>
        
        <h3><i class="fas fa-life-ring"></i> AJUDA</h3>
        <ul class="sidebar-menu">
          <li><b href="#"><i class="fas fa-angle-right"></i> Fale Conosco</b></li>
        </ul>
      </div>
      
      <!-- Main Area -->
      <div class="main">
        
        <!-- Container para os dois boxes empilhados -->
        <div class="boxes-container">
          <div class="box">
            <img src="https://cdn-icons-png.flaticon.com/512/3135/3135715.png" alt="Servidor">          
            <div class="box-content">
              <h4>CANAL DO SERVIDOR(A)</h4>
              <p>Acesso exclusivo para servidores.</p>
              <button class="btn" onclick="openModal('servidor')"><i class="fas fa-sign-in-alt"></i> Acesse Aqui</button>
            </div>
          </div>

          <div class="box">
            <img src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="Administrador">          
            <div class="box-content">
              <h4>CANAL DO ADMINISTRADOR</h4>
              <p>Acesso exclusivo para administradores.</p>
              <button class="btn" onclick="openModal('admin')"><i class="fas fa-sign-in-alt"></i> Acesse Aqui</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <footer>
      <p>© 2025 - Todos os Direitos Reservados</p>
    </footer>
  </div>
    
  <!-- Modal de Login para Administrador -->
  <div id="adminModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('adminModal')">&times;</span>
      <div class="modal-header">
        <h3><i class="fas fa-user-shield"></i> Login Administrador</h3>
      </div>
      <div id="adminMessage"></div>
      <form id="adminForm">
        <div class="form-group">
          <label for="adminUsuario">Usuário:</label>
          <input type="text" id="adminUsuario" name="username" required>
        </div>
        <div class="form-group">
          <label for="adminSenha">Senha:</label>
          <input type="password" id="adminSenha" name="password" required>
        </div>
        <input type="hidden" name="tipo" value="admin">
        <button type="submit" class="modal-btn" id="adminSubmitBtn">
          <span class="loading" id="adminLoading" style="display: none;"></span>
          <span id="adminBtnText">Entrar</span>
        </button>
      </form>
      <div class="modal-footer">
        <a href="#">Esqueci minha senha</a>
      </div>
    </div>
  </div>

  <!-- Modal de Login para Servidor -->
  <div id="servidorModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeModal('servidorModal')">&times;</span>
      <div class="modal-header">
        <h3><i class="fas fa-user-tie"></i> Login Servidor</h3>
      </div>
      <div id="servidorMessage"></div>
      <form id="servidorForm">
        <div class="form-group">
          <label for="servidorUsuario">Usuário:</label>
          <input type="text" id="servidorUsuario" name="username" required>
        </div>
        <div class="form-group">
          <label for="servidorSenha">Senha:</label>
          <input type="password" id="servidorSenha" name="password" required>
        </div>
        <input type="hidden" name="tipo" value="servidor">
        <button type="submit" class="modal-btn" id="servidorSubmitBtn">
          <span class="loading" id="servidorLoading" style="display: none;"></span>
          <span id="servidorBtnText">Entrar</span>
        </button>
      </form>
      <div class="modal-footer">
        <a href="#">Esqueci minha senha</a>
      </div>
    </div>
  </div>

</body>
</html>