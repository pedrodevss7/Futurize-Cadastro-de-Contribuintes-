/**
 * ===============================================================================
 * 🔐 RESUMO: SISTEMA DE AUTENTICAÇÃO - MIGRATIONS CRIADAS
 * ===============================================================================
 * 
 * Data: 11/11/2025
 * Aplicação: Futurize STM
 * Status: ✅ CONCLUÍDO
 * 
 * ===============================================================================
 * 📋 MIGRAÇÃO CRIADA
 * ===============================================================================
 * 
 * Arquivo: app/Database/Migrations/2025-11-11-000000_CreateAuthenticationTables.php
 * 
 * Três tabelas foram criadas com sucesso:
 * 
 * ─────────────────────────────────────────────────────────────────────────────
 * 1️⃣  TABELA: usuarios
 * ─────────────────────────────────────────────────────────────────────────────
 * 
 * Campos:
 *   • id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   • username (VARCHAR 100, UNIQUE)
 *   • email (VARCHAR 150, UNIQUE)
 *   • password (VARCHAR 255) - hash bcrypt/argon2
 *   • tipo (ENUM: 'admin', 'servidor')
 *   • ativo (TINYINT, default: 1)
 *   • email_verified (TINYINT, default: 0)
 *   • last_login_at (DATETIME, nullable)
 *   • created_at (DATETIME)
 *   • updated_at (DATETIME)
 * 
 * Índices:
 *   • PRIMARY KEY: id
 *   • UNIQUE: username, email
 *   • INDEX: ativo
 * 
 * ─────────────────────────────────────────────────────────────────────────────
 * 2️⃣  TABELA: login_attempts
 * ─────────────────────────────────────────────────────────────────────────────
 * 
 * Campos:
 *   • id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   • username_or_email (VARCHAR 150)
 *   • ip (VARCHAR 45) - suporta IPv4 e IPv6
 *   • success (TINYINT: 1=sucesso, 0=falha)
 *   • created_at (DATETIME)
 * 
 * Índices:
 *   • PRIMARY KEY: id
 *   • INDEX: username_or_email, ip, success, created_at
 * 
 * Propósito: Registrar tentativas de login para implementar proteção contra
 *            força bruta (limite de 5 tentativas em 1 minuto)
 * 
 * ─────────────────────────────────────────────────────────────────────────────
 * 3️⃣  TABELA: auth_tokens
 * ─────────────────────────────────────────────────────────────────────────────
 * 
 * Campos:
 *   • id (INT, PRIMARY KEY, AUTO_INCREMENT)
 *   • usuario_id (INT, FOREIGN KEY → usuarios.id)
 *   • token (VARCHAR 255, UNIQUE) - hash do token
 *   • type (ENUM: 'remember', 'reset_password')
 *   • expires_at (DATETIME)
 *   • created_at (DATETIME)
 *   • updated_at (DATETIME)
 * 
 * Índices:
 *   • PRIMARY KEY: id
 *   • FOREIGN KEY: usuario_id (CASCADE DELETE)
 *   • INDEX: usuario_id, type, expires_at
 * 
 * Propósito: Armazenar tokens para:
 *   - 'remember': Login persistente (válido por 30 dias)
 *   - 'reset_password': Recuperação de senha (válido por 30 minutos)
 * 
 * ===============================================================================
 * 🌱 SEEDER CRIADO
 * ===============================================================================
 * 
 * Arquivo: app/Database/Seeds/UsuariosSeeder.php
 * 
 * Usuários de teste inseridos:
 * 
 *   1. Admin
 *      Username: admin
 *      Email:    admin@futurize.com
 *      Senha:    admin123
 *      Tipo:     admin
 * 
 *   2. Servidor
 *      Username: servidor
 *      Email:    servidor@futurize.com
 *      Senha:    servidor123
 *      Tipo:     servidor
 * 
 * ===============================================================================
 * 📁 MODELS EXISTENTES
 * ===============================================================================
 * 
 * Os models já existem e estão compatíveis com a migration:
 * 
 * ✅ UsuarioModel (app/Models/UsuarioModel.php)
 *    - Tabela: usuarios
 *    - PrimaryKey: id
 *    - Métodos: findByLogin(), activate(), updateLastLogin()
 * 
 * ✅ LoginAttemptModel (app/Models/LoginAttemptModel.php)
 *    - Tabela: login_attempts
 *    - Métodos: record(), recentFailures()
 * 
 * ✅ AuthTokenModel (app/Models/AuthTokenModel.php)
 *    - Tabela: auth_tokens
 *    - Métodos: createToken(), validateToken(), revokeByUser()
 * 
 * ===============================================================================
 * 🎯 CONTROLLER IMPLEMENTADO
 * ===============================================================================
 * 
 * AuthController (app/Controllers/AuthController.php)
 * 
 * Endpoints:
 *   POST   /auth/login                - Login com validação e proteção contra força bruta
 *   GET    /auth/logout               - Logout e limpeza de sessão
 *   POST   /auth/request-reset        - Solicitar reset de senha
 *   POST   /auth/reset-password       - Resetar senha com token
 *   GET    /auth/refresh-csrf         - Atualizar token CSRF
 * 
 * Recursos:
 *   ✅ Proteção contra força bruta (5 tentativas em 1 minuto)
 *   ✅ Login persistente (Remember Me - 30 dias)
 *   ✅ Recuperação de senha com token (30 minutos)
 *   ✅ Logs de tentativas de login
 *   ✅ Suporte para login por username ou email
 *   ✅ Diferenciação de tipos (admin/servidor)
 * 
 * ===============================================================================
 * 🚀 COMANDOS UTILIZADOS
 * ===============================================================================
 * 
 * # Executar migrations
 * php spark migrate
 * 
 * # Executar seeder
 * php spark db:seed UsuariosSeeder
 * 
 * # Ver status das migrations
 * php spark migrate:status
 * 
 * # Reverter última migration
 * php spark migrate:rollback
 * 
 * ===============================================================================
 * ✅ CHECKLIST DE VERIFICAÇÃO
 * ===============================================================================
 * 
 * [✅] Migração criada e executada com sucesso
 * [✅] Todas as 3 tabelas criadas no banco de dados
 * [✅] Foreign keys definidas corretamente
 * [✅] Índices adicionados para performance
 * [✅] Seeder criado e executado
 * [✅] Usuários de teste inseridos
 * [✅] Models compatíveis com migração
 * [✅] Controller pronto para uso
 * 
 * ===============================================================================
 * 📝 PRÓXIMOS PASSOS (OPCIONAL)
 * ===============================================================================
 * 
 * 1. Testar autenticação via frontend
 *    - Acessar login page
 *    - Usar credenciais: admin/admin123 ou servidor/servidor123
 * 
 * 2. Validar proteção contra força bruta
 *    - Fazer 5+ tentativas com senha errada
 *    - Verificar se é bloqueado por 1 minuto
 * 
 * 3. Testar reset de senha
 *    - Implementar interface de reset de senha
 *    - Validar tokens com expiração
 * 
 * 4. Implementar verificação de email (optional)
 *    - Enviar link de confirmação ao registrar
 *    - Marcar email_verified = 1 após confirmação
 * 
 * 5. Adicionar mais usuários
 *    - Via formulário de cadastro no admin
 *    - Via seeder adicional para ambiente de testes
 * 
 * ===============================================================================
 * 🔗 RELACIONAMENTO COM CONTRIBUINTES (FUTURO)
 * ===============================================================================
 * 
 * Quando necessário vincular usuários a contribuintes:
 * 
 * ALTER TABLE contribuintes ADD COLUMN usuario_id INT UNSIGNED NULL;
 * ALTER TABLE contribuintes 
 *   ADD FOREIGN KEY (usuario_id) 
 *   REFERENCES usuarios(id) 
 *   ON DELETE SET NULL 
 *   ON UPDATE CASCADE;
 * 
 * Isso permitirá:
 *   - Cada contribuinte ter um usuário associado
 *   - Contribuintes de diferentes tipos (admin/servidor)
 *   - Auditoria de criação/edição por usuário
 * 
 * ===============================================================================
 */
