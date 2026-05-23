# API Contract

## Telefone

Endpoints publicos e administrativos aceitam telefone com ou sem mascara:

```text
11999999999
(11) 99999-9999
(11) 9999-9999
```

No backend, os caracteres nao numericos sao removidos antes da validacao e persistencia.

Regras:

- Agendamento de quiosque: telefone obrigatorio com 10 ou 11 digitos.
- Visita tecnica: telefone obrigatorio com 10 ou 11 digitos.
- Contato: telefone opcional; quando informado, deve ter 10 ou 11 digitos.
- Guia administrativo: telefone opcional; o valor e normalizado para digitos.

## E-mail

E-mails sao normalizados para minusculas e devem ter:

- um unico `@`;
- dominio com ponto;
- TLD com pelo menos 2 letras;
- sem espacos ou caracteres de controle.

Exemplo valido:

```text
nome@dominio.com
usuario@prefeitura.sp.gov.br
```

Exemplos invalidos:

```text
teste@localhost
teste@dominio
teste@@dominio.com
```

## `GET /api/auth/check`

Contrato intencional:

- Usuario autenticado: `200 OK`

```json
{
  "logado": true,
  "nome": "Admin",
  "tipo": "admin"
}
```

- Usuario nao autenticado: `401 Unauthorized`

```json
{
  "logado": false
}
```

O frontend deve tratar `401` como estado normal de visitante anonimo, nao como falha da aplicacao.
