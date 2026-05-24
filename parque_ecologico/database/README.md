# Banco local

Dump principal:

```bash
parque_ecologico/database/ParqueEco_banco.sql
```

Configuracao esperada em `parque_ecologico/.env`:

```env
DB_HOST=127.0.0.1
DB_NAME=if0_41837589_parque
DB_USER=root
DB_PASS=
```

Importacao no XAMPP, depois de iniciar o MySQL:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -h127.0.0.1 -uroot -e "CREATE DATABASE IF NOT EXISTS if0_41837589_parque CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
/Applications/XAMPP/xamppfiles/bin/mysql -h127.0.0.1 -uroot if0_41837589_parque < parque_ecologico/database/ParqueEco_banco.sql
/Applications/XAMPP/xamppfiles/bin/mysql -h127.0.0.1 -uroot if0_41837589_parque < parque_ecologico/migrations/003_backend_hardening.sql
/Applications/XAMPP/xamppfiles/bin/mysql -h127.0.0.1 -uroot if0_41837589_parque < parque_ecologico/migrations/004_add_observacoes_visita_tecnica.sql
```

Para banco novo, o dump `ParqueEco_banco.sql` ja inclui a coluna `visita_tecnica.observacoes`.
