# 🔐 CREDENCIAIS DE ACESSO - SISTEMA DE AUTENTICAÇÃO

## ✅ Senhas Utilizadas

As senhas foram definidas no arquivo `app/Database/Seeds/UsuariosSeeder.php` e são:

### 👨‍💼 Usuário Admin
```
Username:  admin
Email:     admin@futurize.com
Senha:     admin123  ← ESTA É A SENHA
Tipo:      admin
Status:    Ativo ✅
```

### 👨‍💻 Usuário Servidor
```
Username:  servidor
Email:     servidor@futurize.com
Senha:     servidor123  ← ESTA É A SENHA
Tipo:      servidor
Status:    Ativo ✅
```

---

## 🧪 Como Testar o Login

### Opção 1: Via Postman/Insomnia
```http
POST http://localhost/Futurize.STM/public/index.php/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "admin123"
}
```

**Resposta esperada:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso!"
}
```

### Opção 2: Também funciona com email
```http
POST http://localhost/Futurize.STM/public/index.php/auth/login
Content-Type: application/json

{
  "username": "admin@futurize.com",
  "password": "admin123"
}
```

---

## ❌ Se receber "usuário e senha inválidos"

### Checklist de Troubleshooting

1. **Verificar se o usuário existe no banco**
   ```sql
   SELECT id, username, email, ativo FROM usuarios;
   ```

2. **Verificar se o usuário está ativo**
   ```sql
   SELECT username, ativo FROM usuarios WHERE username='admin';
   ```
   Deve retornar `ativo = 1`

3. **Verificar a senha correta**
   - Admin: `admin123` (sem espaços)
   - Servidor: `servidor123` (sem espaços)

4. **Verificar o endpoint correto**
   - URL: `http://localhost/Futurize.STM/public/index.php/auth/login`
   - Método: `POST`
   - Content-Type: `application/json`

5. **Se ainda não funcionar, resetar os usuários**
   ```sql
   DELETE FROM usuarios;
   ```
   Depois execute:
   ```bash
   php spark db:seed UsuariosSeeder
   ```

---

## 🔍 Verificar Dados no Banco

### Ver todos os usuários
```sql
SELECT id, username, email, tipo, ativo, created_at FROM usuarios;
```

### Ver tentativas de login falhadas
```sql
SELECT * FROM login_attempts 
WHERE success = 0 
ORDER BY created_at DESC 
LIMIT 10;
```

### Ver logs de autenticação
```sql
SELECT usuario_id, action, ip, created_at FROM auth_logs 
ORDER BY created_at DESC 
LIMIT 20;
```

---

## 📊 Informações Técnicas

### Hashing de Senha
- **Algoritmo:** bcrypt (PASSWORD_DEFAULT)
- **Custo:** 12
- **Formato:** `$2y$12$...`

### Proteção Contra Força Bruta
- **Limite:** 5 tentativas por minuto
- **Bloqueio:** 1 minuto após limite
- **Armazenado em:** tabela `login_attempts`

### Logs de Autenticação
- Todas as tentativas de login são registradas
- Armazenadas em: tabela `auth_logs`
- Inclui: usuário, ação, IP, user-agent, timestamp

---

## 🚨 Erros Comuns

| Erro | Causa | Solução |
|------|-------|--------|
| "usuário e senha inválidos" | Username ou senha incorreta | Verificar credenciais acima |
| "Muitas tentativas" | 5+ tentativas com senha errada | Aguardar 1 minuto |
| "Usuário não encontrado" | Usuário deletado do banco | Executar seeder novamente |
| "Usuário inativo" | `ativo = 0` | UPDATE usuarios SET ativo=1 WHERE id=1; |

---

## 📞 Suporte

Se continuar com problemas:

1. Limpar a tabela de login_attempts:
   ```sql
   DELETE FROM login_attempts;
   ```

2. Resetar os usuários:
   ```bash
   php spark db:seed --all
   ```

3. Verificar logs do sistema:
   ```
   writable/logs/log-YYYY-MM-DD.log
   ```

4. Testar diretamente no banco:
   ```bash
   php spark db:table usuarios
   ```

---

**Última atualização:** 11/11/2025 15:44  
**Status:** ✅ Autenticação funcionando

