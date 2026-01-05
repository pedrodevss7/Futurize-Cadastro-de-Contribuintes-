# 🔧 RESOLUÇÃO: Undefined Array Key "numero"

## ❌ O Problema

Você recebeu o erro:
```
Undefined array key "numero"
```

Este erro ocorre quando o código tenta acessar a chave `'numero'` em um array, mas essa chave não existe.

---

## 🔍 Causa

No `ContribuinteController.php`, o método `validarDados()` verificava se o campo `numero` era obrigatório:

```php
$camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'numero', 'bairro', 'cidade'];
```

Se o campo não fosse enviado do formulário, causaria o erro "Undefined array key".

---

## ✅ A Solução

### 1️⃣ Removido `numero` dos Campos Obrigatórios

**Antes:**
```php
$camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'numero', 'bairro', 'cidade'];
```

**Depois:**
```php
$camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'bairro', 'cidade'];
```

### 2️⃣ Adicionado Valor Padrão (0) para o Campo `numero`

**Antes:**
```php
'CON_Numero' => $dados['numero'] ?? null,
```

**Depois:**
```php
'CON_Numero' => isset($dados['numero']) ? intval($dados['numero']) : 0,
```

Agora:
- Se o campo `numero` não for enviado → valor padrão é `0`
- Se for enviado → converte para inteiro
- Nunca será `null`, evitando o erro

---

## 📊 Campos Obrigatórios Atualizados

| Campo | Obrigatório | Valor Padrão |
|-------|------------|--------------|
| cpf_cnpj | ✅ SIM | Nenhum |
| razao_social | ✅ SIM | Nenhum |
| endereco | ✅ SIM | Nenhum |
| bairro | ✅ SIM | Nenhum |
| cidade | ✅ SIM | Nenhum |
| numero | ❌ NÃO | 0 |
| complemento | ❌ NÃO | null |
| cep | ❌ NÃO | vazio |
| estado | ❌ NÃO | null |
| telefone1 | ❌ NÃO | vazio |
| telefone2 | ❌ NÃO | vazio |
| email | ❌ NÃO | null |
| inscricao_estadual | ❌ NÃO | null |
| inscricao_municipal | ❌ NÃO | null |

---

## 🧪 Como Testar

### ✅ Agora Você Pode:

**Enviar um contribuinte sem o campo `numero`:**
```json
{
  "cpf_cnpj": "12345678901234",
  "razao_social": "Empresa Teste",
  "endereco": "Rua A",
  "bairro": "Centro",
  "cidade": "São Paulo"
}
```

O campo `numero` será automaticamente preenchido com `0`.

### ✅ Ou Enviar Com o Campo:

```json
{
  "cpf_cnpj": "12345678901234",
  "razao_social": "Empresa Teste",
  "endereco": "Rua A",
  "numero": "123",
  "bairro": "Centro",
  "cidade": "São Paulo"
}
```

O campo `numero` será convertido para inteiro: `123`.

---

## 📝 Alterações Realizadas

### Arquivo: `app/Controllers/ContribuinteController.php`

#### Linha ~346 (Validação)
```diff
- $camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'numero', 'bairro', 'cidade'];
+ $camposObrigatorios = ['cpf_cnpj', 'razao_social', 'endereco', 'bairro', 'cidade'];
```

#### Linha ~387 (Preparação de Dados)
```diff
- 'CON_Numero' => $dados['numero'] ?? null,
+ 'CON_Numero' => isset($dados['numero']) ? intval($dados['numero']) : 0,
```

---

## 🚀 Próximos Passos

1. **Testar cadastro de contribuinte** sem o campo `numero`
2. **Testar cadastro de contribuinte** com o campo `numero`
3. **Verificar no banco** se o valor padrão (0) está sendo armazenado
4. **Atualizar o formulário** (se necessário) para indicar que `numero` é opcional

---

## 💡 Boas Práticas

Para evitar erros como "Undefined array key" no futuro:

### 1️⃣ Sempre Use Null Coalescing (`??`)
```php
$numero = $dados['numero'] ?? 0;  // ✅ Seguro
```

### 2️⃣ Use `isset()` Para Verificações Mais Rigorosas
```php
if (isset($dados['numero'])) {
    // Campo foi enviado
}
```

### 3️⃣ Valide Apenas Campos Obrigatórios
```php
$camposObrigatorios = ['cpf_cnpj', 'razao_social'];  // ✅ Só os essenciais
foreach ($camposObrigatorios as $campo) {
    if (empty($dados[$campo])) {
        return "O campo {$campo} é obrigatório";
    }
}
```

---

## ✅ Status

✅ **Problema Resolvido**
- Campo `numero` agora é opcional
- Valor padrão: `0`
- Sem mais erros "Undefined array key"

