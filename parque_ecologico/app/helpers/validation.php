<?php

/* ═══════════════════════════════════════════════════════════════════════════
 * LISTAS CENTRALIZADAS DE DOMÍNIOS BLOQUEADOS
 *
 * Para adicionar um novo domínio basta incluí-lo na lista correspondente.
 * Todos os valores devem estar em minúsculas e sem espaços.
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Domínios explicitamente bloqueados: genéricos de teste, ofensivos,
 * inaceitáveis para um sistema público municipal.
 */
const EMAIL_DOMINIOS_BLOQUEADOS = [
    // ── RFC 2606 / IANA reservados ────────────────────────────────────────
    'example.com', 'example.org', 'example.net', 'example.br',
    'invalid', 'localhost', 'test',
    // ── Genéricos de teste / placeholder ─────────────────────────────────
    'test.com', 'test.org', 'test.net', 'test.br',
    'teste.com', 'teste.org', 'teste.net', 'teste.com.br',
    'fake.com', 'fake.org', 'fake.net',
    'fakeemail.com', 'fakemail.com',
    'noreply.com', 'no-reply.com',
    'nomail.com', 'noemail.com',
    'email.com', 'dominio.com', 'dominio.com.br',
    'meudominio.com', 'meudominio.com.br',
    'meusite.com', 'meusite.com.br',
    'nao-existe.com', 'nao-existe.com.br',
    'qualquercoisa.com', 'qualquer.com',
    'aaa.com', 'abc.com', 'asdf.com', 'qwerty.com', 'zzz.com', 'xxx.com',
    // ── Ofensivos / inaceitáveis ──────────────────────────────────────────
    'comisuabunda.com', 'comisuabunda.com.br',
    'merda.com', 'merda.com.br',
    'pqp.com', 'pqp.com.br',
    'foda-se.com', 'fodase.com', 'vai-se-foder.com',
    'porcaria.com', 'lixo.com', 'lixo.com.br',
    'spam.com', 'spammer.com', 'spammy.com',
];

/**
 * Domínios de e-mails temporários / descartáveis conhecidos.
 */
const EMAIL_DOMINIOS_DESCARTAVEIS = [
    // ── Mailinator ────────────────────────────────────────────────────────
    'mailinator.com', 'mailinator2.com', 'mailinater.com',
    'safetymail.info', 'mailinspect.com',
    // ── Guerrilla Mail ────────────────────────────────────────────────────
    'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
    'guerrillamail.biz', 'guerrillamail.de', 'guerrillamail.info',
    'grr.la', 'spam4.me', 'sharklasers.com', 'guerrillamailblock.com',
    // ── 10 Minute Mail ────────────────────────────────────────────────────
    '10minutemail.com', '10minutemail.net', '10minutemail.org',
    '10minutemail.co.za', '10minutemail.us', '10minutemail.de',
    // ── Temp Mail ─────────────────────────────────────────────────────────
    'tempmail.com', 'tempmail.net', 'tempmail.org',
    'temp-mail.org', 'temp-mail.ru', 'temp-mail.io',
    'tempinbox.com', 'temporaryemail.net', 'tempr.email',
    'dispostable.com', 'disposemail.com',
    'throwam.com', 'throwam.net', 'throwablemail.com',
    // ── Yopmail ───────────────────────────────────────────────────────────
    'yopmail.com', 'yopmail.fr', 'yopmail.net',
    'cool.fr.nf', 'jetable.fr.nf', 'nospam.ze.tc', 'nomail.xl.cx',
    'mega.zik.dj', 'speed.1s.fr', 'courriel.fr.nf',
    'moncourrier.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf',
    // ── TrashMail ─────────────────────────────────────────────────────────
    'trashmail.com', 'trashmail.me', 'trashmail.net', 'trashmail.org',
    'trashmail.at', 'trashmail.io', 'trashmailer.com',
    // ── Maildrop / Mailnull e similares ──────────────────────────────────
    'maildrop.cc', 'mailnull.com', 'mailzilla.com', 'mailzilla.org',
    'mailexpire.com', 'mailfreeonline.com', 'mailguard.me',
    'mailme.ir', 'mailme.lv', 'mailme24.com', 'mailmoat.com',
    'mailnew.com', 'mailsiphon.com', 'mailslite.com',
    'mailtemp.info', 'mailtome.de', 'mailtothis.com',
    // ── SpamGourmet ───────────────────────────────────────────────────────
    'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org',
    // ── Outros provedores descartáveis ────────────────────────────────────
    'fakeinbox.com', 'spambox.us', 'spamcero.com', 'spamfree24.org',
    'spamgob.com', 'spamspot.com', 'spamstack.net', 'spamtrail.com',
    'spambin.com', 'spam.la', 'spaml.de',
    'spambob.com', 'spambob.net', 'spambob.org',
    'spambog.com', 'spambog.de', 'spambog.ru',
    'deadaddress.com', 'easytrashmail.com', 'filzmail.com',
    'forgetmail.com', 'haltospam.com', 'hatespam.org',
    'hidemail.de', 'inboxbear.com',
    'jetable.com', 'jetable.fr', 'jetable.net', 'jetable.org',
    'kurzepost.de', 'letthemeatspam.com', 'lortemail.dk',
    'meltmail.com', 'mintemail.com', 'mytrashmail.com',
    'netmails.com', 'netmails.net', 'nobulk.com', 'nomail.pw',
    'nospamfor.us', 'nospammail.net', 'nowmymail.com',
    'objectmail.com', 'odaymail.com', 'ownmail.net',
    'pookmail.com', 'proxymail.eu', 'quickinbox.com',
    'rcpt.at', 'rmqkr.net', 'rootfest.net', 'safetypost.de',
    'sandelf.de', 'saynotospams.com', 'skeefmail.com',
    'slopsbox.com', 'smellfear.com', 'smokemail.net', 'snkmail.com',
    'sofort-mail.de', 'sogetthis.com', 'spamfree.eu',
];


/* ═══════════════════════════════════════════════════════════════════════════
 * FUNÇÕES DE VALIDAÇÃO
 * ═══════════════════════════════════════════════════════════════════════════ */

/**
 * Normaliza um endereço de e-mail: minúsculas + trim.
 */
function normalizarEmail(string $email): string
{
    return mb_strtolower(trim($email));
}

/**
 * Verifica se o domínio possui registros DNS que indiquem capacidade de
 * receber e-mails (MX preferido; A/AAAA como fallback).
 *
 * Em caso de falha na consulta (ambiente sem DNS) retorna true para não
 * bloquear envios legítimos por problemas de infraestrutura.
 */
function dominioExisteDNS(string $domain): bool
{
    try {
        if (checkdnsrr($domain, 'MX'))   return true;
        if (checkdnsrr($domain, 'A'))    return true;
        if (checkdnsrr($domain, 'AAAA')) return true;
        return false;
    } catch (Throwable $e) {
        error_log("Email DNS check failed for '{$domain}': " . $e->getMessage());
        return true; // graceful degradation
    }
}

/**
 * Valida um endereço de e-mail de forma completa e retorna uma mensagem de
 * erro em português, ou null quando o e-mail é válido.
 *
 * Esta é a função central de validação — use-a em todos os controllers.
 *
 * @param  string      $email    Endereço de e-mail (normalizado internamente)
 * @param  bool        $checkDNS Se true verifica registros DNS do domínio
 * @return string|null           Mensagem de erro ou null se válido
 */
function obterErroEmail(string $email, bool $checkDNS = true): ?string
{
    $email = normalizarEmail($email);

    // 1. Vazio ────────────────────────────────────────────────────────────
    if ($email === '') {
        return 'O e-mail é obrigatório.';
    }

    // 2. Comprimento máximo (RFC 5321) ────────────────────────────────────
    if (strlen($email) > 254) {
        return 'O endereço de e-mail é muito longo (máximo 254 caracteres).';
    }

    // 3. Caracteres de controle ───────────────────────────────────────────
    if (preg_match('/[\x00-\x20\x7F]/', $email)) {
        return 'O e-mail contém caracteres inválidos.';
    }

    // 4. Formato básico ───────────────────────────────────────────────────
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Formato de e-mail inválido. Use o padrão nome@dominio.com.';
    }

    [$local, $domain] = explode('@', $email, 2);

    // 5. Local-part ───────────────────────────────────────────────────────
    if ($local === '' || strlen($local) > 64) {
        return 'A parte antes do "@" do e-mail é inválida.';
    }

    // 6. Domínio ──────────────────────────────────────────────────────────
    if ($domain === '' || strlen($domain) > 253) {
        return 'O domínio do e-mail é inválido.';
    }

    if (!str_contains($domain, '.') || str_contains($domain, '..')) {
        return 'O domínio do e-mail é inválido.';
    }

    $labels = explode('.', $domain);
    foreach ($labels as $label) {
        if ($label === '' || strlen($label) > 63) {
            return 'O domínio do e-mail é inválido.';
        }
        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i', $label)) {
            return 'O domínio do e-mail contém caracteres inválidos.';
        }
    }

    $tld = end($labels);
    if (!preg_match('/^[a-z]{2,}$/i', $tld)) {
        return 'A extensão do e-mail (ex.: .com, .br) é inválida.';
    }

    // 7. Domínios bloqueados ───────────────────────────────────────────────
    if (in_array($domain, EMAIL_DOMINIOS_BLOQUEADOS, true)) {
        return 'Este domínio de e-mail não é aceito. Utilize um endereço '
             . 'real (ex.: Gmail, Outlook, e-mail corporativo ou institucional).';
    }

    // 8. E-mails temporários / descartáveis ───────────────────────────────
    if (in_array($domain, EMAIL_DOMINIOS_DESCARTAVEIS, true)) {
        return 'E-mails temporários ou descartáveis não são aceitos. '
             . 'Por favor, informe seu endereço de e-mail real para que possamos entrar em contato.';
    }

    // 9. Verificação DNS ──────────────────────────────────────────────────
    if ($checkDNS && !dominioExisteDNS($domain)) {
        $safe = htmlspecialchars($domain, ENT_QUOTES, 'UTF-8');
        return "O domínio \"{$safe}\" não foi encontrado. Verifique se o e-mail está correto.";
    }

    return null; // ✓ válido
}

/**
 * Retorna true se o e-mail for válido, false caso contrário.
 * Mantém compatibilidade com todos os controllers já existentes.
 *
 * Para obter a mensagem de erro descritiva use obterErroEmail().
 */
function emailValido(string $email, bool $checkDNS = true): bool
{
    return obterErroEmail($email, $checkDNS) === null;
}
