<?php

function normalizarEmail($email) {
    return mb_strtolower(trim((string) $email));
}

function emailValido($email) {
    $email = normalizarEmail($email);

    if ($email === '' || strlen($email) > 254) {
        return false;
    }

    if (preg_match('/[\x00-\x20\x7F]/', $email)) {
        return false;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    [$local, $domain] = explode('@', $email, 2);

    if ($local === '' || strlen($local) > 64 || $domain === '' || strlen($domain) > 253) {
        return false;
    }

    if (!str_contains($domain, '.')) {
        return false;
    }

    if (str_contains($domain, '..')) {
        return false;
    }

    $labels = explode('.', $domain);
    foreach ($labels as $label) {
        if ($label === '' || strlen($label) > 63) {
            return false;
        }

        if (!preg_match('/^[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/', $label)) {
            return false;
        }
    }

    $tld = end($labels);
    return preg_match('/^[a-z]{2,}$/', $tld) === 1;
}

?>
