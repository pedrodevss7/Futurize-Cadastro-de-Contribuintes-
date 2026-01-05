# 🚀 QUICK START - Testes Rápidos do Sistema

## ✅ O Que Foi Corrigido

1. ✅ **Autenticação** - Tabelas e usuários criados
2. ✅ **Prefeituras** - Dados de teste inseridos
3. ✅ **Validação de Campos** - Campo "numero" corrigido
4. ✅ **Logs de Autenticação** - Tabela criada para auditoria

---

## 🧪 Testes Recomendados (5 minutos)

### Teste 1: Verificar Banco de Dados
```bash
cd c:\laragon\www\futurize.STM

# Ver usuários
php spark db:table usuarios

# Ver prefeituras
php spark db:table prefeituras

# Ver migrations
php spark migrate:status
```

**Resultado Esperado:**
- ✅ 2 usuários (admin, servidor)
- ✅ 5 prefeituras (SP, RJ, MG, DF, BA)
- ✅ 3 migrations executadas

---

### Teste 2: Testar Login (via Postman/Insomnia)

**URL:**
```
POST http://localhost/Futurize.STM/public/index.php/auth/login
```

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Resultado Esperado:**
```json
{
  "success": true,
  "message": "Login realizado com sucesso!"
}
```

---

### Teste 3: Testar Logout

**URL:**
```
GET http://localhost/Futurize.STM/public/index.php/auth/logout
```

**Resultado Esperado:**
```
Sessão destruída, usuário desconectado
```

---

### Teste 4: Cadastrar Novo Contribuinte

**URL:**
```
POST http://localhost/Futurize.STM/public/index.php/api/contribuintes/cadastrar
```

**Headers:**
```
Content-Type: application/json
X-Requested-With: XMLHttpRequest
```

**Body:**
```json
{
  "CON_PRE_Codigo": 1,
  "cpf_cnpj": "12345678901234",
  "razao_social": "Empresa Teste LTDA",
  "endereco": "Avenida Principal",
  "bairro": "Centro",
  "cidade": "São Paulo",
  "tipo_pessoa": "juridica"
}
```

**Resultado Esperado:**
```json
{
  "success": true,
  "message": "Contribuinte criado com sucesso",
  "data": {
    "CON_PRE_Codigo": 1,
    "CON_Codigo": 1001,
    ...
  }
}
```

---

### Teste 5: Listar Contribuintes

**URL:**
```
GET http://localhost/Futurize.STM/public/index.php/api/contribuintes/listar
```

**Resultado Esperado:**
```json
{
  "success": true,
  "data": [
    {
      "CON_codigo": 1001,
      "CON_razao_social": "Empresa Teste LTDA",
      ...
    }
  ]
}
```

---

## 🔑 Credenciais Padrão

```
Admin:
  Username: admin
  Senha:    admin123

Servidor:
  Username: servidor
  Senha:    servidor123
```

---

## 📝 Checklist de Validação

- [ ] Banco de dados com dados de teste
- [ ] Login funcionando com admin
- [ ] Logout funcionando
- [ ] Cadastro de contribuinte sem campo "numero"
- [ ] Cadastro de contribuinte com campo "numero"
- [ ] Listagem de contribuintes retornando dados
- [ ] Edição de contribuinte funcionando
- [ ] Exclusão de contribuinte funcionando

---

## 🐛 Se Encontrar Problemas

### Erro: "Undefined array key"
✅ **Corrigido** - Campo "numero" agora é opcional

### Erro: "Foreign key constraint fails"
✅ **Corrigido** - Prefeituras de teste inseridas

### Erro: "Table 'auth_logs' doesn't exist"
✅ **Corrigido** - Tabela criada via migration

### Erro: "usuário e senha inválidos"
- Verificar credenciais: `admin/admin123`
- Verificar se usuário está ativo no banco
- Usar email como alternativa: `admin@futurize.com`

---

## 📊 Dados de Teste Disponíveis

### Usuários
| Username | Email | Senha | Tipo |
|----------|-------|-------|------|
| admin | admin@futurize.com | admin123 | admin |
| servidor | servidor@futurize.com | servidor123 | servidor |

### Prefeituras
| Código | Nome | Município | UF |
|--------|------|-----------|-----|
| 1 | Prefeitura Municipal de São Paulo | São Paulo | SP |
| 2 | Prefeitura Municipal de Rio de Janeiro | Rio de Janeiro | RJ |
| 3 | Prefeitura Municipal de Belo Horizonte | Belo Horizonte | MG |
| 4 | Prefeitura Municipal de Brasília | Brasília | DF |
| 5 | Prefeitura Municipal de Salvador | Salvador | BA |

---

## 🎯 Próximos Passos

1. **Executar testes acima** (5-10 minutos)
2. **Validar respostas** (conforme esperado)
3. **Reportar problemas** (se houver)
4. **Implementar frontend** (se não existir)
5. **Fazer deploy** (quando pronto)

---

## 📞 Suporte Rápido

### Resetar Sistema
```bash
php spark migrate:rollback
php spark migrate
php spark db:seed --all
```

### Ver Logs de Erro
```
writable/logs/log-YYYY-MM-DD.log
```

### Verificar Migrations
```bash
php spark migrate:status
```

### Limpar Cache
```bash
php spark cache:clear
```

---

**✅ Sistema pronto para testes!**

Comece pelo Teste 1 e vá progredindo até o Teste 5.

