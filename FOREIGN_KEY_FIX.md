# 🔧 RESOLUÇÃO: Erro de Foreign Key - Prefeituras

## ❌ O Problema

Você recebeu o erro:
```
Cannot add or update a child row: a foreign key constraint fails 
(`futurize_stm`.`contribuintes`, CONSTRAINT `contribuintes_CON_PRE_Codigo_foreign` 
FOREIGN KEY (`CON_PRE_Codigo`) REFERENCES `prefeituras` (`PRE_Codigo`) 
ON DELETE CASCADE ON UPDATE CASCADE)
```

### 🔍 Causa

A tabela `contribuintes` possui uma restrição de chave estrangeira que exige que o valor em `CON_PRE_Codigo` exista na tabela `prefeituras`. Como a tabela `prefeituras` estava vazia, a inserção de contribuintes falhava.

---

## ✅ A Solução

### 1️⃣ Seeder Criado: `PrefeiturasSeeder.php`
```php
namespace App\Database\Seeds;

class PrefeiturasSeeder extends Seeder {
    public function run() {
        $data = [
            [
                'PRE_Codigo'    => 1,
                'PRE_Nome'      => 'Prefeitura Municipal de São Paulo',
                'PRE_Municipio' => 'São Paulo',
                'PRE_UF'        => 'SP',
            ],
            // ... mais prefeituras
        ];
        $this->db->table('prefeituras')->insertBatch($data);
    }
}
```

### 2️⃣ Prefeituras Inseridas

| PRE_Codigo | PRE_Nome | PRE_Municipio | PRE_UF |
|---|---|---|---|
| 1 | Prefeitura Municipal de São Paulo | São Paulo | SP |
| 2 | Prefeitura Municipal de Rio de Janeiro | Rio de Janeiro | RJ |
| 3 | Prefeitura Municipal de Belo Horizonte | Belo Horizonte | MG |
| 4 | Prefeitura Municipal de Brasília | Brasília | DF |
| 5 | Prefeitura Municipal de Salvador | Salvador | BA |

---

## 🚀 Como Usar

### Ao Inserir um Novo Contribuinte

Certifique-se de usar um `CON_PRE_Codigo` que existe em `prefeituras`:

```json
{
  "CON_PRE_Codigo": 1,  // ← Este deve existir em prefeituras
  "CON_Codigo": 1001,
  "CON_Nome": "Empresa Exemplo",
  "CON_Endereco": "Rua A, 123"
}
```

### Verificar Prefeituras Disponíveis

```sql
SELECT PRE_Codigo, PRE_Nome FROM prefeituras;
```

---

## 📋 Estructura da Relação

```
prefeituras (Tabela Principal)
├─ PRE_Codigo (PK, INT, UNSIGNED)
├─ PRE_Nome
├─ PRE_Municipio
└─ PRE_UF

        ↓ FK (CON_PRE_Codigo)

contribuintes (Tabela Secundária)
├─ CON_PRE_Codigo (FK)
├─ CON_Codigo (PK)
├─ CON_Nome
└─ ... (outros campos)
```

**Regra:** Não é possível inserir um contribuinte se o `CON_PRE_Codigo` não existir em `prefeituras`.

---

## 🛠️ Como Adicionar Novas Prefeituras

### Opção 1: Via SQL Direto
```sql
INSERT INTO prefeituras (PRE_Codigo, PRE_Nome, PRE_Municipio, PRE_UF)
VALUES (6, 'Prefeitura de Curitiba', 'Curitiba', 'PR');
```

### Opção 2: Criar um Seeder Adicional
```php
php spark make:seeder NovaPrefeiturasSeeder
```

---

## 🚨 Se Receber o Erro Novamente

### Checklist:

1. **Verificar se prefeituras tem dados**
   ```sql
   SELECT COUNT(*) FROM prefeituras;
   ```

2. **Verificar o valor que está tentando usar**
   ```sql
   SELECT DISTINCT CON_PRE_Codigo FROM contribuintes;
   ```

3. **Verificar se o código existe em prefeituras**
   ```sql
   SELECT * FROM prefeituras WHERE PRE_Codigo = 1;
   ```

4. **Se não encontrar, inserir**
   ```sql
   INSERT INTO prefeituras (PRE_Codigo, PRE_Nome, PRE_Municipio, PRE_UF)
   VALUES (1, 'Nome da Prefeitura', 'Município', 'UF');
   ```

---

## 📊 Status Atual

✅ **Prefeituras de Teste Inseridas:**
- 5 prefeituras criadas
- Códigos de 1 a 5
- Prontas para uso

✅ **Foreign Key Validada:**
- Agora é possível inserir contribuintes
- Use `CON_PRE_Codigo` de 1 a 5

---

## 💡 Dica

Se você está desenvolvendo, sempre garanta que:

1. **Tabelas pai (prefeituras) sejam preenchidas primeiro**
2. **Tabelas filhas (contribuintes) usem referências válidas**
3. **Seeders sejam executados em ordem correta**

Ordem recomendada:
```bash
php spark db:seed PrefeiturasSeeder
php spark db:seed AtividadesSeeder
php spark db:seed CnaesSeeder
php spark db:seed UsuariosSeeder
```

