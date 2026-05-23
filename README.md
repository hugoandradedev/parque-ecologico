# Parque Ecologico Itaquaquecetuba

Sistema municipal para reservas de quiosques, agendamento de visitas tecnicas, contato publico, autenticacao e painel administrativo.

## Requisitos

- PHP 8.2 ou compativel
- MySQL/MariaDB
- Apache com rewrite habilitado em producao

## Estrutura principal

- `parque_ecologico/`: aplicacao PHP
- `parque_ecologico/public/`: CSS, JS e imagens
- `parque_ecologico/app/`: controllers, models, core, middlewares e helpers
- `parque_ecologico/database/ParqueEco_banco.sql`: dump base do banco
- `parque_ecologico/migrations/`: migrations incrementais

## Configuracao local

1. Copie o arquivo de ambiente:

```bash
cp parque_ecologico/.env.example parque_ecologico/.env
```

2. Ajuste `parque_ecologico/.env` com as credenciais do MySQL local.

3. Crie e importe o banco:

```bash
/Applications/XAMPP/xamppfiles/bin/php -r '$pdo=new PDO("mysql:host=127.0.0.1;charset=utf8mb4","root",""); $pdo->exec("CREATE DATABASE IF NOT EXISTS if0_41837589_parque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");'
/Applications/XAMPP/xamppfiles/bin/php -r '$pdo=new PDO("mysql:host=127.0.0.1;dbname=if0_41837589_parque;charset=utf8mb4","root",""); $pdo->exec(file_get_contents("parque_ecologico/database/ParqueEco_banco.sql"));'
/Applications/XAMPP/xamppfiles/bin/php -r '$pdo=new PDO("mysql:host=127.0.0.1;dbname=if0_41837589_parque;charset=utf8mb4","root",""); $pdo->exec(file_get_contents("parque_ecologico/migrations/003_backend_hardening.sql"));'
```

4. Suba o servidor local:

```bash
cd /caminho/para/parque
/Applications/XAMPP/xamppfiles/bin/php -S localhost:8000
```

5. Abra:

```text
http://localhost:8000/parque_ecologico/
```

## Login administrativo

O dump possui um usuario admin. Use as credenciais combinadas pelo responsavel do projeto. Se necessario, atualize a senha diretamente no banco com `password_hash` do PHP.

## Deploy

Veja [DEPLOY_INFINITYFREE.md](DEPLOY_INFINITYFREE.md).

## Contrato da API

Veja [API_CONTRACT.md](API_CONTRACT.md) para regras de telefone, e-mail e comportamento de `/api/auth/check`.
