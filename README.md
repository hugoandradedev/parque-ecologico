# Parque Ecológico de Itaquaquecetuba

Site institucional e sistema de gestão desenvolvido em parceria com a Prefeitura de Itaquaquecetuba para aproximar a população do meio ambiente, facilitar o acesso aos serviços do Parque Ecológico Mário do Canto e fortalecer ações de educação ambiental na cidade.

Este projeto nasceu com uma proposta simples e poderosa: transformar a presença digital do parque em uma ferramenta pública de informação, organização, preservação e cidadania. A plataforma reúne conteúdo institucional, agendamento de espaços, visitas técnicas, comunicação com a administração, recursos educativos e um painel administrativo completo para apoiar a gestão do parque.

## Visão do projeto

O Parque Ecológico é um espaço de convivência, lazer, memória ambiental e aprendizado. O site foi pensado para apresentar essa importância de forma clara, acessível e moderna, permitindo que moradores, escolas, visitantes e equipes administrativas encontrem rapidamente o que precisam.

Mais do que uma página bonita, o projeto funciona como uma ponte entre a Prefeitura de Itaquaquecetuba, o parque e a comunidade. Ele organiza informações essenciais, reduz processos manuais e reforça a mensagem de que preservar o meio ambiente também passa por tecnologia, planejamento e boa comunicação.

## Parceria institucional

O sistema foi desenvolvido como uma iniciativa em parceria com a Prefeitura de Itaquaquecetuba, com foco em criar uma solução digital voltada ao meio ambiente e ao atendimento público.

A proposta valoriza o Parque Ecológico Mário do Canto como patrimônio ambiental da cidade e oferece uma experiência online alinhada com responsabilidade social, educação ecológica e serviço público eficiente.

## O que o site entrega

- Página inicial com apresentação do parque, galeria de imagens, localização, horários e chamadas para as principais ações.
- Área "Sobre" com missão, história, compromisso ambiental e informações sobre o papel do parque na cidade.
- Reserva gratuita de quiosques, com formulário organizado, validações e envio de solicitação para análise administrativa.
- Agendamento de visitas técnicas para instituições, escolas e grupos, com seleção de guia, controle de visitantes e observações adicionais.
- Formulário de contato para comunicação direta entre visitantes e administração.
- Quiz ecológico para testar conhecimentos sobre meio ambiente, sustentabilidade e preservação.
- Caça-palavras ecológico para tornar o aprendizado ambiental mais leve, interativo e divertido.
- Painel administrativo para gerenciar reservas, visitas, guias, datas bloqueadas e mensagens recebidas.
- Sistema de autenticação para proteger áreas administrativas.
- API interna em PHP para processar dados, validar informações e organizar os fluxos do sistema.

## Por que esse projeto importa

Um parque público não é apenas um lugar para visitar. Ele é um espaço de educação, saúde, cultura, lazer e pertencimento. Quando a tecnologia é bem aplicada, ela ajuda a população a usar melhor esse espaço e ajuda a administração a cuidar melhor dele.

Este projeto mostra exatamente isso: uma solução real, feita para uma necessidade real, com impacto direto na rotina de quem visita e de quem administra o parque.

Ele melhora a comunicação com o cidadão, centraliza solicitações, reduz ruído operacional, divulga informações ambientais e coloca o Parque Ecológico de Itaquaquecetuba em uma presença digital mais forte, organizada e convincente.

## Diferenciais

- Foco em serviço público, com linguagem clara e funcionalidades práticas.
- Experiência acessível para visitantes que querem se informar, reservar espaços ou participar de atividades.
- Gestão administrativa integrada, evitando que reservas, visitas, mensagens e guias fiquem espalhados em controles manuais.
- Validações de telefone, e-mail, datas, horários e quantidade de visitantes.
- Controle de conflitos para evitar agendamentos duplicados em horários incompatíveis.
- Bloqueio de datas indisponíveis, incluindo suporte a datas comemorativas.
- Conteúdo educativo para reforçar consciência ambiental.
- Estrutura organizada em MVC simples, facilitando manutenção e evolução.
- Interface pública e painel administrativo dentro da mesma aplicação.

## A mente por trás da solução

Este projeto tem aquela assinatura de quem não apenas "fez um site", mas pensou no fluxo inteiro: visitante, escola, administração, meio ambiente, dados, segurança, usabilidade e impacto público.

É o tipo de entrega que mostra domínio técnico e visão de produto. Aquele trabalho de mestre mesmo: pega uma demanda que poderia virar só uma página institucional comum e transforma em uma plataforma completa, com propósito, gestão, experiência e utilidade real para a cidade.

## Funcionalidades públicas

### Página inicial

A home apresenta o Parque Ecológico Mário do Canto, destaca a experiência de lazer e preservação, exibe galeria de imagens, informa horário de funcionamento, mostra localização com mapa incorporado e direciona o usuário para reserva de quiosques, visitas técnicas, página sobre, quiz e jogo educativo.

### Reserva de quiosques

Permite que visitantes solicitem o uso gratuito dos quiosques do parque. O formulário coleta dados do responsável, telefone, e-mail, data, horário, quiosque desejado, quantidade de visitantes e aceite dos termos.

A solicitação passa por validação e depende de confirmação da administração.

### Visitas técnicas

Voltada principalmente para instituições, escolas e grupos organizados. A página permite informar dados da instituição, responsável, data da visita, quantidade de visitantes, objetivo da visita, guia técnico e observações adicionais.

Cada visita técnica possui limite de visitantes e controle de disponibilidade de guia.

### Contato

Canal para visitantes enviarem mensagens, dúvidas, sugestões ou solicitações. As mensagens ficam disponíveis no painel administrativo para acompanhamento.

### Quiz ecológico

Recurso educativo que incentiva o aprendizado sobre sustentabilidade, preservação ambiental e atitudes conscientes.

### Caça-palavras ecológico

Jogo interativo com termos ligados à natureza e preservação. Uma forma simples e criativa de reforçar vocabulário ambiental e engajar usuários.

## Painel administrativo

O painel administrativo centraliza a operação do sistema. Nele, a equipe pode:

- Visualizar reservas de quiosques.
- Aprovar, rejeitar ou excluir solicitações.
- Visualizar visitas técnicas.
- Gerenciar guias técnicos.
- Cadastrar, editar, ativar e remover guias.
- Bloquear datas indisponíveis para agendamento.
- Importar datas comemorativas brasileiras para bloqueio.
- Visualizar e gerenciar mensagens de contato.
- Filtrar registros por status, tipo, data, quiosque, responsável e guia.
- Ordenar registros por data.

Essa área foi construída para dar autonomia à administração e tornar o atendimento mais organizado, rápido e rastreável.

## Arquitetura

O projeto utiliza PHP com uma estrutura própria inspirada em MVC:

- `controllers`: recebem requisições, aplicam regras e respondem às rotas.
- `models`: concentram acesso e persistência de dados.
- `views`: armazenam páginas HTML renderizadas pela aplicação.
- `core`: contém roteamento, sessão e renderização.
- `helpers`: funções auxiliares para validação, CSRF e rate limit.
- `public`: arquivos CSS, JavaScript e imagens.
- `database`: dump base do banco de dados.
- `migrations`: ajustes incrementais de estrutura.

## Tecnologias

- PHP 8.2
- MySQL/MariaDB
- HTML5
- CSS3
- JavaScript
- PDO
- Apache em produção
- Servidor embutido do PHP para desenvolvimento local

## Estrutura principal

```text
parque_ecologico/
├── app/
│   ├── controllers/
│   ├── core/
│   ├── helpers/
│   ├── middlewares/
│   ├── models/
│   └── views/
├── config/
├── database/
├── migrations/
├── public/
│   ├── css/
│   ├── images/
│   └── js/
├── admin.php
├── index.php
└── populate_bloqueios.php
```

## Requisitos

- PHP 8.2 ou compatível
- MySQL/MariaDB
- Apache com rewrite habilitado em produção
- XAMPP ou ambiente equivalente para desenvolvimento local

## Configuração local

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
/Applications/XAMPP/xamppfiles/bin/php -r '$pdo=new PDO("mysql:host=127.0.0.1;dbname=if0_41837589_parque;charset=utf8mb4","root",""); $pdo->exec(file_get_contents("parque_ecologico/migrations/004_add_observacoes_visita_tecnica.sql"));'
```

Para uma instalação nova, o dump principal já contém a estrutura atual. As migrations servem principalmente para bancos que já existiam antes das correções.

4. Suba o servidor local:

```bash
cd /caminho/para/parque
/Applications/XAMPP/xamppfiles/bin/php -S localhost:8000
```

5. Abra no navegador:

```text
http://localhost:8000/parque_ecologico/
```

## Login administrativo

O dump possui um usuário admin. Use as credenciais combinadas pelo responsável do projeto. Se necessário, atualize a senha diretamente no banco com `password_hash` do PHP.

## Segurança e qualidade

O projeto possui validações importantes para reduzir erros de uso e proteger os fluxos principais:

- Normalização e validação de telefone.
- Validação de e-mail.
- Controle de sessão para área administrativa.
- Proteção CSRF em ações sensíveis.
- Rate limit em formulários públicos.
- Uso de PDO para acesso ao banco.
- Tratamento de conflitos em reservas e visitas.
- Separação entre páginas públicas, API e painel administrativo.

## Deploy

Veja [DEPLOY_INFINITYFREE.md](DEPLOY_INFINITYFREE.md) para orientações de publicação.

## Contrato da API

Veja [API_CONTRACT.md](API_CONTRACT.md) para regras de telefone, e-mail e comportamento de `/api/auth/check`.

## Resultado

O resultado é uma plataforma completa para o Parque Ecológico de Itaquaquecetuba: bonita para o cidadão, útil para a Prefeitura, educativa para a comunidade e organizada para quem administra.

Um projeto com propósito ambiental, aplicação prática e execução de respeito.
