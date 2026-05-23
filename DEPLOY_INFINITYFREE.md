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
2. Importe:

```text
parque_ecologico/database/ParqueEco_banco.sql
```

3. Depois importe as migrations, se ainda nao estiverem refletidas no banco:

```text
parque_ecologico/migrations/003_backend_hardening.sql
```

Observacao: se a migration acusar indice/coluna ja existente, revise antes de repetir. O dump atual ja contem varias chaves e tabelas principais.

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
- Mesmo guia nao aceita horario sobreposto.
- E-mails invalidos sao rejeitados.
- Painel admin lista reservas, visitas, guias, bloqueios e mensagens.

## 8. Problemas comuns

- **CSS quebrado**: confirme que a pasta foi enviada como `htdocs/parque_ecologico`, nao apenas o conteudo interno.
- **Erro de banco**: revise `DB_HOST`, `DB_NAME`, `DB_USER` e `DB_PASS` no `.env`.
- **404 em rotas**: confirme que `.htaccess` foi enviado e que o hosting respeita rewrite.
- **Erro ao importar migration**: provavelmente parte da migration ja existe. Compare a estrutura no phpMyAdmin antes de reaplicar.
