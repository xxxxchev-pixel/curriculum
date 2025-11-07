# 🔧 Como Importar a Base de Dados Corretamente

## ⚠️ Erro Comum

**Erro:** `#1044 - Acesso negado para o usuário 'root'@'localhost' ao banco de dados 'information_schema'`

**Causa:** O arquivo SQL anterior tentava criar o banco de dados diretamente, o que pode causar problemas de permissão.

---

## ✅ Solução: Importação em 3 Passos

### **Passo 1: Criar o Banco de Dados**

1. Acesse: `http://localhost/phpmyadmin`
2. Clique em **"Novo"** ou **"New"** na barra lateral esquerda
3. Digite o nome: `dermacare`
4. Selecione cotação: `utf8mb4_unicode_ci`
5. Clique **"Criar"** ou **"Create"**

### **Passo 2: Importar o Arquivo SQL**

1. No phpMyAdmin, clique no banco `dermacare` (que você acabou de criar)
2. Clique na aba **"Importar"** ou **"Import"**
3. Clique em **"Escolher arquivo"** ou **"Choose File"**
4. Navegue até: `c:\wamp64\www\PSI_M17_04_Solução Web\database\dermacare.sql`
5. **IMPORTANTE:** Role até o final da página
6. Marque a opção: ☑ **"Permitir interrupção de importação caso o script detecte que está próximo do tempo limite"**
7. Clique em **"Executar"** ou **"Go"**

### **Passo 3: Verificar**

Após a importação, você deve ver:
```
✅ Importação concluída com sucesso
✅ XX consultas executadas
```

No painel esquerdo, clique em `dermacare` e veja as tabelas:
- ✅ usuarios
- ✅ medicos  
- ✅ horarios_disponiveis
- ✅ marcacoes
- ✅ consultas_detalhes
- ✅ categorias_servicos
- ✅ servicos
- ✅ documentos
- ✅ notificacoes
- ✅ mensagens_contacto
- ✅ configuracoes

---

## 🚀 Alternativa: Script PHP Automático

Se preferir usar o script PHP:

1. Acesse: `http://localhost/PSI_M17_04_Solução Web/api/criar-tabelas.php`
2. O script criará tudo automaticamente
3. Você verá confirmações de cada tabela criada

---

## 🔍 Verificar se Funcionou

Execute no phpMyAdmin (aba SQL):

```sql
USE dermacare;
SHOW TABLES;
```

Deve mostrar 11 tabelas.

Para ver os médicos cadastrados:
```sql
SELECT * FROM medicos;
```

Deve mostrar 4 médicos.

Para ver os serviços:
```sql
SELECT * FROM servicos;
```

Deve mostrar 27 serviços.

---

## ❌ Se Ainda Der Erro

### **Erro: "Table already exists"**
**Solução:** Remova o banco antigo
```sql
DROP DATABASE IF EXISTS dermacare;
```
Depois crie novamente e importe.

### **Erro: "MySQL has gone away"**
**Solução:** Arquivo muito grande
1. Edite: `c:\wamp64\bin\mysql\mysqlX.X.XX\my.ini`
2. Encontre: `max_allowed_packet`
3. Altere para: `max_allowed_packet = 64M`
4. Reinicie WAMP
5. Tente novamente

### **Erro: "Unknown character set"**
**Solução:** 
1. Use o script PHP: `criar-tabelas.php`
2. Ele criará tudo automaticamente com charset correto

---

## ✅ Checklist Final

Após importação bem-sucedida:

- [ ] 11 tabelas criadas
- [ ] 4 médicos cadastrados  
- [ ] 6 categorias de serviços
- [ ] 27 serviços cadastrados
- [ ] 20 configurações do sistema
- [ ] 0 usuários (base limpa)
- [ ] 3 views criadas
- [ ] 2 stored procedures criadas
- [ ] 1 trigger criado

---

**Se tudo estiver OK, você está pronto para usar o sistema!** 🎉
