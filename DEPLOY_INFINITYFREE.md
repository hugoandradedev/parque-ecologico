# Deploy no InfinityFree

Este guia assume que o colaborador vai baixar o repositorio do GitHub e publicar a pasta `parque_ecologico` no InfinityFree.

## 1. Baixar o projeto

```bash
git clone <URL_DO_REPOSITORIO>
cd parque
```

## 2. Enviar arquivos para o hosting

No File Manager ou FTP do InfinityFree:

1. Abra a pasta `htdocs`.
2. Envie a pasta `parque_ecologico` inteira para dentro de `htdocs`.
3. O caminho esperado ficara assim:

```text
htdocs/parque_ecologico/index.php
htdocs/parque_ecologico/app/
htdocs/parque_ecologico/public/
```

Assim, a URL fica:

```text
https://SEU_DOMINIO/parque_ecologico/
```

## 3. Criar banco MySQL

No painel do InfinityFree:

1. Acesse **MySQL Databases**.
2. Crie o banco do projeto.
3. Anote:
   - MySQL Hostname
   - Database Name
   - Username
   - Password

## 4. Importar banco

No phpMyAdmin do InfinityFree:

1. Selecione o banco criado.
2. Clique na aba **Importar**.
3. Em **Escolher arquivo**, selecione o arquivo:

```text
parque_ecologico/database/ParqueEco_banco.sql
```

4. Clique em **Executar**.
5. Aguarde aparecer a mensagem de importacao concluida.

Para uma instalacao nova usando o dump atualizado, normalmente nao e necessario aplicar migrations antigas manualmente.

Se o banco ja existia antes desta versao, aplique as migrations pendentes em ordem:

```text
parque_ecologico/migrations/003_backend_hardening.sql
parque_ecologico/migrations/004_add_observacoes_visita_tecnica.sql
```

Observacao: se uma migration acusar indice/coluna ja existente, revise antes de repetir. O dump atual ja contem a coluna `observacoes` em `visita_tecnica`.

Para visitas tecnicas, confirme tambem que a tabela `visita_tecnica` esta usando `InnoDB`. O sistema grava visitas dentro de transacoes para evitar conflito de guia/horario; se essa tabela estiver como `MyISAM`, o envio pode falhar com erro interno. O dump atualizado ja cria essa tabela em `InnoDB`. Em banco antigo, execute no phpMyAdmin:

```sql
ALTER TABLE visita_tecnica ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
```

Depois da importacao, cadastre ou ative pelo menos um guia tecnico no painel administrativo. A pagina `/parque_ecologico/visita` busca guias ativos ao abrir, e o formulario depende dessa lista.

### Passo a passo para corrigir visita tecnica

Use este passo a passo quando o site ja esta no InfinityFree, mas o formulario de visita tecnica nao salva.

#### A. Subir os arquivos alterados

```text
parque_ecologico/public/js/visita.js
parque_ecologico/app/views/pages/visita.html
parque_ecologico/database/fix_visita_tecnica_infinityfree.sql
DEPLOY_INFINITYFREE.md
```

No File Manager do InfinityFree:

1. Abra a pasta `htdocs`.
2. Abra a pasta `parque_ecologico`.
3. Substitua este arquivo:

```text
htdocs/parque_ecologico/public/js/visita.js
```

4. Substitua este arquivo:

```text
htdocs/parque_ecologico/app/views/pages/visita.html
```

5. Envie este arquivo para a pasta `database`:

```text
htdocs/parque_ecologico/database/fix_visita_tecnica_infinityfree.sql
```

Se estiver usando o pacote `.zip` de correcao, envie o `.zip` para `htdocs`, extraia mantendo a estrutura de pastas e confirme que os caminhos acima ficaram iguais.

#### B. Executar o SQL no phpMyAdmin

No painel do InfinityFree:

1. Acesse **MySQL Databases**.
2. Clique em **Admin** para abrir o phpMyAdmin do banco usado pelo projeto.
3. No menu lateral, clique no banco configurado no arquivo `.env`.
4. Clique na aba **Importar**.
5. Clique em **Escolher arquivo**.
6. Selecione o arquivo:

```text
parque_ecologico/database/fix_visita_tecnica_infinityfree.sql
```

7. Clique em **Executar**.
8. Aguarde o phpMyAdmin finalizar.

Esse script:

- converte `visita_tecnica` para `InnoDB`;
- cria as colunas `guia_id` e `observacoes` se estiverem faltando;
- garante pelo menos um guia tecnico ativo para o formulario;
- mostra consultas de conferencia no final.

#### C. Conferir o resultado do SQL

No final da execucao, confirme que:

```text
guias_ativos > 0
visita_tecnica possui a coluna guia_id
visita_tecnica possui a coluna observacoes
visita_tecnica esta com Engine = InnoDB
```

Se `guias_ativos` aparecer como `0`, entre no painel administrativo do site e cadastre um guia tecnico ativo.

#### D. Testar no site

1. Abra:

```text
https://SEU_DOMINIO/parque_ecologico/visita
```

2. Preencha todos os campos obrigatorios.
3. Escolha uma data em dia util com pelo menos 7 dias de antecedencia.
4. Escolha um horario entre `09:00` e `13:00`.
5. Selecione um guia tecnico.
6. Marque o aceite dos termos.
7. Clique em **Solicitar Visita Tecnica**.

Se o envio ainda falhar, abra as ferramentas do navegador, confira a aba **Network/Rede** e veja a resposta da rota:

```text
/parque_ecologico/api/visita/enviar
```

As mensagens mais comuns sao:

- `Guia indisponivel`: nao existe guia ativo no banco.
- `Visitas devem ser agendadas com no minimo 7 dia de antecedencia`: a data escolhida esta muito proxima.
- `Data indisponivel`: a data esta bloqueada.
- `Erro ao salvar visita tecnica`: confira se o SQL de correcao foi executado no banco certo.

#### E. Quando usar cada arquivo SQL

Use esta regra:

```text
Banco novo:
1. Importe parque_ecologico/database/ParqueEco_banco.sql
2. Importe parque_ecologico/database/fix_visita_tecnica_infinityfree.sql

Banco ja existente:
1. Nao importe ParqueEco_banco.sql de novo
2. Importe apenas parque_ecologico/database/fix_visita_tecnica_infinityfree.sql
```

Nao importe o `ParqueEco_banco.sql` em banco que ja possui dados reais, porque isso pode causar conflito ou sobrescrever a estrutura existente.

## 5. Configurar `.env`

No servidor, crie o arquivo:

```text
htdocs/parque_ecologico/.env
```

Use o modelo:

```env
DB_HOST=sqlXXX.infinityfree.com
DB_NAME=if0_XXXXXXXX_parque
DB_USER=if0_XXXXXXXX
DB_PASS=SENHA_DO_BANCO
APP_URL=https://SEU_DOMINIO/parque_ecologico
```

Nao commite `.env` no GitHub. Ele contem credenciais.

## 6. Conferir protecoes

O arquivo `parque_ecologico/.htaccess` protege `.env`, `.sql` e `.md` contra acesso publico e direciona rotas para `index.php`.

Teste no navegador:

```text
https://SEU_DOMINIO/parque_ecologico/
https://SEU_DOMINIO/parque_ecologico/agendamento
https://SEU_DOMINIO/parque_ecologico/visita
https://SEU_DOMINIO/parque_ecologico/login
```

## 7. Checklist de validacao

- Home carrega com CSS e imagens.
- Login admin funciona.
- Reserva de quiosque grava no banco.
- Mesmo quiosque nao aceita horario sobreposto.
- Quiosques diferentes aceitam o mesmo horario.
- Visita tecnica grava no banco.
- Observacoes da visita tecnica aparecem no painel administrativo.
- Mesmo guia nao aceita horario sobreposto.
- E-mails invalidos sao rejeitados.
- Painel admin lista reservas, visitas, guias, bloqueios e mensagens.

## 8. Problemas comuns

- **CSS quebrado**: confirme que a pasta foi enviada como `htdocs/parque_ecologico`, nao apenas o conteudo interno.
- **Erro de banco**: revise `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` no `.env`.
- **Visita tecnica nao abre**: confirme que o banco do `.env` existe, foi importado e que a tabela `guias` pode ser consultada.
- **Visita tecnica nao envia**: confirme que existe guia ativo e que `visita_tecnica` esta em `InnoDB`.
- **404 em rotas**: confirme que `.htaccess` foi enviado e que o hosting respeita rewrite.
- **Erro ao importar migration**: provavelmente parte da migration ja existe. Compare a estrutura no phpMyAdmin antes de reaplicar.
