# 📊 Base de Dados Final - DermaCare

## ✅ Consolidação Concluída

A base de dados DermaCare foi consolidada em **um único arquivo SQL** contendo toda a estrutura necessária para o sistema.

---

## 📁 Arquivo Final

**Localização:** `/database/dermacare.sql`

**Status:** ✅ Consolidado e otimizado

**Arquivos removidos:**
- ❌ `dermacare_db.sql` (excluído)
- ❌ `dermacare.sql` (antigo - excluído)

---

## 🗄️ Estrutura da Base de Dados

### **Banco de Dados:** `dermacare`
**Charset:** `utf8mb4`  
**Collation:** `utf8mb4_unicode_ci`

---

## 📋 Tabelas (11 tabelas principais)

### **1. usuarios** (Tabela Central)
Armazena todos os usuários/pacientes do sistema.

**Campos principais:**
- `id` - Chave primária (auto-incremento)
- `nome`, `apelido` - Nome completo
- `email` - Email único (usado para login)
- `senha_hash` - Senha criptografada (bcrypt)
- `telefone`, `telemovel` - Contatos
- `nif` - NIF único (9 dígitos)
- `data_nascimento` - Data de nascimento
- `genero` - Gênero
- `endereco`, `codigo_postal`, `cidade`, `pais` - Endereço completo
- `seguro`, `numero_seguro` - Informações do seguro de saúde
- `newsletter` - Se aceita receber newsletter
- `foto_perfil` - Caminho da foto
- `email_verificado` - Status de verificação
- `token_verificacao` - Token para verificar email
- `token_reset_senha` - Token para resetar senha
- `ativo` - Se a conta está ativa
- `data_criacao`, `ultima_atualizacao`, `ultimo_login` - Timestamps

**Índices:**
- `email` (UNIQUE)
- `nif` (UNIQUE)
- `nome`, `apelido`

**Relações:**
- **1:N** com `marcacoes`
- **1:N** com `documentos`
- **1:N** com `notificacoes`

---

### **2. medicos**
Cadastro de médicos dermatologistas.

**Campos principais:**
- `id` - Chave primária
- `nome` - Nome completo do médico
- `especialidade` - Especialização
- `crm` - CRM ou Número da Ordem (UNIQUE)
- `email`, `telefone` - Contatos
- `bio` - Biografia profissional
- `foto` - Foto do médico
- `anos_experiencia` - Anos de experiência
- `formacao` - Formação acadêmica
- `disponivel` - Se está disponível para consultas

**Dados pré-cadastrados:** 4 médicos
1. Dra. Ana Silva - Dermatologia Clínica (15 anos exp.)
2. Dr. Carlos Santos - Dermatologia Estética (12 anos exp.)
3. Dra. Maria Costa - Tricologia (10 anos exp.)
4. Dr. Pedro Oliveira - Dermatologia Pediátrica (8 anos exp.)

**Relações:**
- **1:N** com `horarios_disponiveis`
- **1:N** com `marcacoes`

---

### **3. horarios_disponiveis**
Horários de trabalho dos médicos.

**Campos:**
- `id` - Chave primária
- `medico_id` - FK para médicos
- `dia_semana` - ENUM (segunda, terca, quarta, quinta, sexta, sabado, domingo)
- `hora_inicio` - TIME
- `hora_fim` - TIME
- `disponivel` - Se o horário está ativo

**Relações:**
- **N:1** com `medicos` (CASCADE)

---

### **4. marcacoes** (Tabela de Consultas)
Agendamentos e consultas.

**Campos principais:**
- `id` - Chave primária
- `usuario_id` - FK para usuarios
- `medico_id` - FK para medicos
- `data_marcacao`, `hora_marcacao` - Data e hora
- `duracao_minutos` - Duração (padrão: 30min)
- `tipo_consulta` - Tipo de consulta
- `motivo` - Motivo da consulta
- `observacoes` - Observações do paciente
- `status` - ENUM (pendente, confirmada, cancelada, concluida, falta)
- `motivo_cancelamento` - Motivo se cancelada
- `valor` - Valor da consulta
- `forma_pagamento` - ENUM (dinheiro, cartao, mbway, transferencia, seguro)
- `pago` - Se foi pago
- `email_enviado` - Se email de confirmação foi enviado
- `lembrete_enviado` - Se lembrete foi enviado
- `data_criacao`, `data_atualizacao` - Timestamps
- `confirmada_em`, `cancelada_em` - Timestamps de status

**Índices:**
- `usuario_id`
- `medico_id`
- `data_marcacao`, `hora_marcacao`
- `status`

**Relações:**
- **N:1** com `usuarios` (CASCADE)
- **N:1** com `medicos` (CASCADE)
- **1:1** com `consultas_detalhes`

---

### **5. consultas_detalhes** (Prontuário)
Detalhes clínicos da consulta após atendimento.

**Campos:**
- `id` - Chave primária
- `marcacao_id` - FK UNIQUE para marcacoes
- `queixa_principal` - Queixa do paciente
- `historia_doenca` - História clínica
- `exame_fisico` - Resultado do exame físico
- `diagnostico` - Diagnóstico médico
- `tratamento_prescrito` - Prescrição
- `observacoes_medicas` - Observações do médico
- `proxima_consulta` - Data de retorno

**Relações:**
- **1:1** com `marcacoes` (CASCADE)

---

### **6. categorias_servicos**
Categorização dos serviços oferecidos.

**Campos:**
- `id` - Chave primária
- `nome` - Nome da categoria (UNIQUE)
- `descricao` - Descrição
- `icone` - Ícone Bootstrap
- `cor` - Cor para interface
- `ordem` - Ordem de exibição
- `ativo` - Se está ativa

**Categorias pré-cadastradas:** 6
1. Dermatologia Clínica
2. Estética Facial
3. Tratamentos a Laser
4. Prevenção
5. Estética Corporal
6. Tricologia

**Relações:**
- **1:N** com `servicos`

---

### **7. servicos**
Serviços/tratamentos oferecidos pela clínica.

**Campos:**
- `id` - Chave primária
- `categoria_id` - FK para categorias_servicos
- `nome` - Nome do serviço
- `descricao` - Descrição curta
- `descricao_detalhada` - Descrição completa
- `duracao_minutos` - Duração padrão
- `preco` - Preço
- `preco_minimo` - Preço mínimo (se houver variação)
- `imagem` - Imagem do serviço
- `ativo` - Se está disponível
- `destaque` - Se aparece em destaque

**Serviços pré-cadastrados:** 27 serviços
- Consultas dermatológicas
- Tratamentos de acne, dermatite, psoríase, rosácea
- Procedimentos estéticos (botox, preenchimento, peeling, etc.)
- Tratamentos a laser (rejuvenescimento, depilação, manchas, etc.)
- Prevenção (mapeamento de sinais, check-up)
- Tratamentos corporais (criolipólise, celulite, etc.)
- Tricologia (tratamento capilar)

**Relações:**
- **N:1** com `categorias_servicos` (SET NULL)

---

### **8. documentos**
Documentos e arquivos dos pacientes.

**Campos:**
- `id` - Chave primária
- `usuario_id` - FK para usuarios
- `marcacao_id` - FK para marcacoes (opcional)
- `tipo_documento` - ENUM (receita, exame, relatorio, atestado, outro)
- `titulo` - Título do documento
- `descricao` - Descrição
- `ficheiro` - Caminho do arquivo
- `tamanho_kb` - Tamanho
- `mime_type` - Tipo MIME

**Relações:**
- **N:1** com `usuarios` (CASCADE)
- **N:1** com `marcacoes` (SET NULL)

---

### **9. notificacoes**
Sistema de notificações para usuários.

**Campos:**
- `id` - Chave primária
- `usuario_id` - FK para usuarios
- `tipo` - ENUM (email, sms, push, sistema)
- `titulo` - Título da notificação
- `mensagem` - Conteúdo
- `lida` - Se foi lida
- `link` - Link relacionado
- `enviada` - Se foi enviada
- `enviada_em` - Quando foi enviada

**Relações:**
- **N:1** com `usuarios` (CASCADE)

---

### **10. mensagens_contacto**
Formulário de contato do site.

**Campos:**
- `id` - Chave primária
- `nome`, `email`, `telefone` - Dados do remetente
- `assunto` - Assunto da mensagem
- `mensagem` - Conteúdo
- `respondida` - Se foi respondida
- `resposta` - Resposta enviada
- `respondida_em` - Quando foi respondida
- `ip_address` - IP de origem

---

### **11. configuracoes**
Configurações do sistema.

**Campos:**
- `id` - Chave primária
- `chave` - Nome da configuração (UNIQUE)
- `valor` - Valor
- `tipo` - ENUM (string, integer, boolean, json)
- `descricao` - Descrição

**Configurações pré-cadastradas:** 20 itens
- Informações da clínica (nome, email, telefone, endereço)
- Horários de funcionamento
- Parâmetros de marcação
- Configurações SMTP
- Redes sociais

---

## 🔗 Relacionamentos

```
usuarios (1) ←→ (N) marcacoes
medicos (1) ←→ (N) marcacoes
marcacoes (1) ←→ (1) consultas_detalhes
categorias_servicos (1) ←→ (N) servicos
usuarios (1) ←→ (N) documentos
usuarios (1) ←→ (N) notificacoes
medicos (1) ←→ (N) horarios_disponiveis
```

---

## 📊 Views (Consultas Predefinidas)

### **1. vw_marcacoes_completas**
Marcações com todos os detalhes de paciente e médico.

**Colunas:**
- Dados da marcação
- Nome, email e telefones do paciente
- Nome e especialidade do médico
- Status de pagamento e emails

### **2. vw_agenda_medicos**
Agenda diária dos médicos.

**Colunas:**
- Data, hora início e fim
- Médico e especialidade
- Paciente e telefone
- Status da marcação

### **3. vw_estatisticas**
Estatísticas gerais do sistema.

**Métricas:**
- Total de usuários ativos
- Total de médicos disponíveis
- Marcações por status
- Marcações de hoje
- Faturamento do mês

---

## 🔧 Stored Procedures

### **1. sp_verificar_disponibilidade**
Verifica se um horário está disponível.

**Parâmetros:**
- `p_medico_id` - ID do médico
- `p_data` - Data da consulta
- `p_hora` - Hora da consulta
- `p_duracao` - Duração em minutos

**Retorna:** Número de conflitos (0 = disponível)

### **2. sp_proximas_marcacoes_usuario**
Busca próximas marcações de um usuário.

**Parâmetros:**
- `p_usuario_id` - ID do usuário

**Retorna:** Lista das próximas 10 marcações

---

## ⚡ Triggers

### **1. tr_marcacao_email_enviado**
Atualiza status da marcação para "confirmada" quando email é enviado.

---

## 📈 Índices para Performance

**Índices criados:**
- `idx_marcacoes_data_status` - Busca por data e status
- `idx_marcacoes_usuario_data` - Histórico do usuário
- `idx_usuarios_ativo` - Usuários ativos
- `idx_medicos_disponivel` - Médicos disponíveis

---

## 🚀 Como Usar

### **Opção 1: Importar via phpMyAdmin**
```
1. Acesse: http://localhost/phpmyadmin
2. Clique em "Importar"
3. Selecione: database/dermacare.sql
4. Clique "Executar"
```

### **Opção 2: Usar script PHP**
```
Já existe: /api/criar-tabelas.php
Execute: http://localhost/.../api/criar-tabelas.php
```

### **Opção 3: Terminal MySQL**
```bash
mysql -u root -p < "c:\wamp64\www\PSI_M17_04_Solução Web\database\dermacare.sql"
```

---

## 📊 Dados Iniciais Incluídos

**✅ Médicos:** 4 dermatologistas com horários
**✅ Categorias:** 6 categorias de serviços
**✅ Serviços:** 27 serviços/tratamentos
**✅ Configurações:** 20 configurações do sistema
**✅ Horários:** Horários de trabalho de cada médico

**❌ Usuários:** NENHUM (base limpa)
- Sistema começa sem usuários predefinidos
- Cada registro cria novo usuário real
- Dados salvos permanentemente

---

## 🔒 Segurança

**Implementado:**
- ✅ Senhas criptografadas (bcrypt)
- ✅ Foreign Keys com CASCADE/SET NULL
- ✅ Campos UNIQUE (email, nif, crm)
- ✅ Índices otimizados
- ✅ Triggers automáticos
- ✅ Views para facilitar consultas
- ✅ Stored Procedures para lógica de negócio

---

## 📝 Notas Importantes

1. **Charset UTF-8:** Suporta caracteres portugueses (ã, õ, ç, etc.)
2. **InnoDB Engine:** Suporta transações e foreign keys
3. **Timestamps:** Atualização automática de datas
4. **Cascata:** Exclusão em cascata mantém integridade
5. **Valores Padrão:** Campos têm valores padrão sensatos

---

## 🔄 Atualizações Futuras

Para adicionar novas funcionalidades:

```sql
-- Exemplo: Adicionar campo
ALTER TABLE usuarios ADD COLUMN cpf VARCHAR(11);

-- Exemplo: Nova tabela
CREATE TABLE avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marcacao_id INT NOT NULL,
    nota INT,
    comentario TEXT,
    FOREIGN KEY (marcacao_id) REFERENCES marcacoes(id)
);
```

---

## 📞 Manutenção

### **Backup Regular:**
```bash
mysqldump -u root -p dermacare > backup_dermacare_YYYYMMDD.sql
```

### **Verificar Integridade:**
```sql
CHECK TABLE usuarios, medicos, marcacoes;
```

### **Otimizar Tabelas:**
```sql
OPTIMIZE TABLE usuarios, medicos, marcacoes;
```

### **Ver Estatísticas:**
```sql
SELECT * FROM vw_estatisticas;
```

---

## ✅ Checklist de Verificação

Após importar, verificar:

- [ ] Banco `dermacare` foi criado
- [ ] 11 tabelas foram criadas
- [ ] 4 médicos estão cadastrados
- [ ] 27 serviços estão disponíveis
- [ ] 6 categorias existem
- [ ] 0 usuários (base limpa)
- [ ] 3 views foram criadas
- [ ] 2 stored procedures funcionam
- [ ] 1 trigger está ativo
- [ ] Índices foram criados

---

**Base de dados consolidada e pronta para uso! 🎉**
