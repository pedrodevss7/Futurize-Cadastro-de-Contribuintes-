# 🧪 GUIA DE TESTE - SISTEMA DE AUTENTICAÇÃO

## 📋 Informações das Tabelas

### ✅ Tabela: usuarios
```
✓ id              (INT, PK, AUTO_INCREMENT)
✓ username        (VARCHAR 100, UNIQUE)
✓ email           (VARCHAR 150, UNIQUE)
✓ password        (VARCHAR 255, hashed)
✓ tipo            (ENUM: 'admin', 'servidor')
✓ ativo           (TINYINT, default: 1)
✓ email_verified  (TINYINT, default: 0)
✓ last_login_at   (DATETIME, nullable)
✓ created_at      (DATETIME)
✓ updated_at      (DATETIME)

Total de registros: 2
```

### ✅ Tabela: login_attempts
```
✓ id                (INT, PK, AUTO_INCREMENT)
✓ username_or_email (VARCHAR 150)
✓ ip                (VARCHAR 45)
✓ success           (TINYINT: 0=falha, 1=sucesso)
✓ created_at        (DATETIME)

Total de registros: 0 (novo)
```

### ✅ Tabela: auth_tokens
```
✓ id          (INT, PK, AUTO_INCREMENT)
✓ usuario_id  (INT, FK → usuarios.id, CASCADE)
✓ token       (VARCHAR 255, UNIQUE, hashed)
✓ type        (ENUM: 'remember', 'reset_password')
✓ expires_at  (DATETIME)
✓ created_at  (DATETIME)
✓ updated_at  (DATETIME)

Total de registros: 0 (novo)
```

---

## 🔐 Credenciais de Teste

### Admin
```
Username: admin
Email:    admin@futurize.com
Senha:    admin123
Tipo:     admin
Ativo:    Sim ✅
```

### Servidor
```
Username: servidor
Email:    servidor@futurize.com
Senha:    servidor123
Tipo:     servidor
Ativo:    Sim ✅
```

---

## 🧪 Testes Recomendados

### 1. Login com Username (Admin)
```
POST /auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123",
  "tipo": "admin"
}

Resultado esperado: 200 OK com sessão criada
```

### 2. Login com Email (Servidor)
```
POST /auth/login
Content-Type: application/json

{
  "username": "servidor@futurize.com",
  "password": "servidor123",
  "tipo": "servidor"
}

Resultado esperado: 200 OK com sessão criada
```

### 3. Login com Remember Me (30 dias)
```
POST /auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123",
  "remember": true
}

Resultado esperado: 200 OK + token salvo em auth_tokens com type='remember'
Verificar: SELECT * FROM auth_tokens WHERE tipo='remember'
```

### 4. Protecção Contra Força Bruta
```
POST /auth/login (5+ vezes com senha errada)

{
  "username": "admin",
  "password": "senhaerrada"
}

Resultado esperado:
- Primeiras 4: 401 Unauthorized
- 5ª tentativa: 429 Too Many Requests
- Mensagem: "Muitas tentativas. Aguarde 1 minuto..."

Verificar: SELECT COUNT(*) FROM login_attempts 
           WHERE username_or_email='admin' AND success=0 
           AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MINUTE)
```

### 5. Logout
```
GET /auth/logout

Resultado esperado: 200 OK + sessão destruída + redirect para home
Verificar: Session não contém user_id
```

### 6. Acesso sem Autenticação
```
GET /admin/dashboard (sem login)

Resultado esperado: Redirect para página de login OU 401 Unauthorized
```

---

## 📊 Verificações via SQL

### Ver todos os usuários
```sql
SELECT id, username, email, tipo, ativo FROM usuarios;
```

### Ver tentativas de login
```sql
SELECT * FROM login_attempts 
ORDER BY created_at DESC 
LIMIT 20;
```

### Ver tokens ativos
```sql
SELECT usuario_id, type, expires_at FROM auth_tokens 
WHERE expires_at > NOW() 
ORDER BY created_at DESC;
```

### Ver último login
```sql
SELECT username, email, tipo, last_login_at FROM usuarios 
ORDER BY last_login_at DESC;
```

### Limpar tentativas de login (se necessário)
```sql
DELETE FROM login_attempts 
WHERE created_at < DATE_SUB(NOW(), INTERVAL 1 DAY);
```

### Revogar todos os tokens de um usuário
```sql
DELETE FROM auth_tokens WHERE usuario_id = 1;
```

---

## 🔧 Troubleshooting

### Problema: Login não funciona
**Solução:**
1. Verificar se a tabela `usuarios` tem dados
   ```sql
   SELECT COUNT(*) FROM usuarios;
   ```
2. Verificar se as senhas estão corretas (são hashes bcrypt)
3. Verificar se o usuário está ativo (`ativo = 1`)

### Problema: Bloqueio permanente após 5 tentativas
**Solução:**
```sql
DELETE FROM login_attempts WHERE username_or_email='admin';
-- Ou aguardar 1 minuto
```

### Problema: Tokens expirados não são removidos
**Solução:** (Implementar limpeza automática)
```sql
DELETE FROM auth_tokens WHERE expires_at < NOW();
```

---

## 📝 Próximas Implementações

- [ ] Criar interface HTML para login
- [ ] Implementar reset de senha com email
- [ ] Adicionar verificação de email ao registrar
- [ ] Implementar TOTP (2FA) com Google Authenticator
- [ ] Adicionar audit log de acessos
- [ ] Implementar rate limiting por IP

---

## ✅ Checklist de Validação

- [x] Migration executada com sucesso
- [x] Tabelas criadas no banco
- [x] Seeder criado e executado
- [x] Usuários de teste inseridos
- [x] Models compatíveis
- [x] Controller pronto
- [ ] Testes de integração passando
- [ ] Frontend de login implementado
- [ ] Tratamento de erros completo
- [ ] Documentação atualizada

