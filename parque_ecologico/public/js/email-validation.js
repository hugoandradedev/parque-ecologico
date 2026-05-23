/**
 * email-validation.js
 *
 * Validação de e-mail centralizada para o frontend.
 * Espelha as regras do backend (validation.php) para fornecer
 * feedback imediato sem depender de round-trip ao servidor.
 *
 * A validação DNS só ocorre no backend — aqui verificamos
 * formato, domínios bloqueados e domínios descartáveis.
 *
 * Uso:
 *   EmailValidation.validate(email)       → string de erro | null
 *   EmailValidation.setup(inputElement)   → adiciona feedback em tempo real
 */

window.EmailValidation = (function () {

    /* ── Listas centralizadas ─────────────────────────────────────────────
     * Mantenha sincronizadas com app/helpers/validation.php
     * ──────────────────────────────────────────────────────────────────── */

    const BLOCKED = new Set([
        // RFC 2606 / reservados
        'example.com', 'example.org', 'example.net', 'example.br',
        'invalid', 'localhost', 'test',
        // Genéricos de teste
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
        // Ofensivos
        'comisuabunda.com', 'comisuabunda.com.br',
        'merda.com', 'merda.com.br',
        'pqp.com', 'pqp.com.br',
        'foda-se.com', 'fodase.com', 'vai-se-foder.com',
        'porcaria.com', 'lixo.com', 'lixo.com.br',
        'spam.com', 'spammer.com', 'spammy.com',
    ]);

    const DISPOSABLE = new Set([
        // Mailinator
        'mailinator.com', 'mailinator2.com', 'mailinater.com',
        'safetymail.info', 'mailinspect.com',
        // Guerrilla Mail
        'guerrillamail.com', 'guerrillamail.net', 'guerrillamail.org',
        'guerrillamail.biz', 'guerrillamail.de', 'guerrillamail.info',
        'grr.la', 'spam4.me', 'sharklasers.com', 'guerrillamailblock.com',
        // 10 Minute Mail
        '10minutemail.com', '10minutemail.net', '10minutemail.org',
        '10minutemail.co.za', '10minutemail.us', '10minutemail.de',
        // Temp Mail
        'tempmail.com', 'tempmail.net', 'tempmail.org',
        'temp-mail.org', 'temp-mail.ru', 'temp-mail.io',
        'tempinbox.com', 'temporaryemail.net', 'tempr.email',
        'dispostable.com', 'disposemail.com',
        'throwam.com', 'throwam.net', 'throwablemail.com',
        // Yopmail
        'yopmail.com', 'yopmail.fr', 'yopmail.net',
        'cool.fr.nf', 'jetable.fr.nf', 'nospam.ze.tc', 'nomail.xl.cx',
        'mega.zik.dj', 'speed.1s.fr', 'courriel.fr.nf',
        'moncourrier.fr.nf', 'monemail.fr.nf', 'monmail.fr.nf',
        // TrashMail
        'trashmail.com', 'trashmail.me', 'trashmail.net', 'trashmail.org',
        'trashmail.at', 'trashmail.io', 'trashmailer.com',
        // Maildrop / Mailnull
        'maildrop.cc', 'mailnull.com', 'mailzilla.com', 'mailzilla.org',
        'mailexpire.com', 'mailguard.me', 'mailme.ir', 'mailme.lv',
        'mailme24.com', 'mailmoat.com', 'mailnew.com', 'mailtemp.info',
        'mailtome.de', 'mailtothis.com',
        // SpamGourmet
        'spamgourmet.com', 'spamgourmet.net', 'spamgourmet.org',
        // Outros
        'fakeinbox.com', 'spambox.us', 'spamcero.com', 'spamfree24.org',
        'spamgob.com', 'spamspot.com', 'spamstack.net', 'spambin.com',
        'spam.la', 'spaml.de',
        'spambob.com', 'spambob.net', 'spambob.org',
        'spambog.com', 'spambog.de', 'spambog.ru',
        'deadaddress.com', 'easytrashmail.com', 'filzmail.com',
        'forgetmail.com', 'haltospam.com', 'hatespam.org', 'hidemail.de',
        'inboxbear.com', 'jetable.com', 'jetable.fr', 'jetable.net', 'jetable.org',
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
    ]);

    /* ── Regex de formato ────────────────────────────────────────────────── */
    // Alinhada com o FILTER_VALIDATE_EMAIL do PHP (simplificado)
    const FORMAT_RE = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

    /* ── Função principal ─────────────────────────────────────────────────
     * Retorna string de erro ou null se válido.
     * Nota: validação DNS não é feita aqui (apenas no backend).
     * ──────────────────────────────────────────────────────────────────── */
    function validate(raw) {
        const email = (raw || '').trim().toLowerCase();

        if (!email) {
            return 'O e-mail é obrigatório.';
        }

        if (email.length > 254) {
            return 'O endereço de e-mail é muito longo (máximo 254 caracteres).';
        }

        if (!FORMAT_RE.test(email)) {
            return 'Formato de e-mail inválido. Use o padrão nome@dominio.com.';
        }

        const atIdx = email.lastIndexOf('@');
        const local  = email.slice(0, atIdx);
        const domain = email.slice(atIdx + 1);

        if (local.length > 64) {
            return 'A parte antes do "@" do e-mail é inválida.';
        }

        if (!domain.includes('.') || domain.includes('..')) {
            return 'O domínio do e-mail é inválido.';
        }

        const tld = domain.split('.').pop();
        if (!/^[a-z]{2,}$/i.test(tld)) {
            return 'A extensão do e-mail (ex.: .com, .br) é inválida.';
        }

        if (BLOCKED.has(domain)) {
            return 'Este domínio de e-mail não é aceito. Utilize um endereço real '
                 + '(ex.: Gmail, Outlook, e-mail corporativo ou institucional).';
        }

        if (DISPOSABLE.has(domain)) {
            return 'E-mails temporários ou descartáveis não são aceitos. '
                 + 'Por favor, informe seu endereço de e-mail real.';
        }

        return null; // válido ✓
    }

    /* ── Feedback visual em tempo real ───────────────────────────────────
     * Anexa validação ao evento blur/input de um <input type="email">.
     * Cria ou reutiliza um <span class="email-error-msg"> após o campo.
     * ──────────────────────────────────────────────────────────────────── */
    function getOrCreateMsg(input) {
        let msg = input.parentElement.querySelector('.email-error-msg');
        if (!msg) {
            msg = document.createElement('span');
            msg.className = 'email-error-msg';
            msg.setAttribute('role', 'alert');
            msg.setAttribute('aria-live', 'polite');
            input.insertAdjacentElement('afterend', msg);
        }
        return msg;
    }

    function applyState(input, error) {
        const msg = getOrCreateMsg(input);
        if (error) {
            msg.textContent = error;
            msg.style.display = 'block';
            input.setAttribute('aria-invalid', 'true');
            input.style.borderColor = 'var(--danger, #dc2626)';
        } else {
            msg.textContent = '';
            msg.style.display = 'none';
            input.removeAttribute('aria-invalid');
            input.style.borderColor = '';
        }
    }

    function setup(input) {
        if (!input) return;

        // Valida ao sair do campo
        input.addEventListener('blur', function () {
            if (this.value.trim() === '') {
                applyState(this, null); // não reclamar antes de interagir
                return;
            }
            applyState(this, validate(this.value));
        });

        // Limpa o erro enquanto o usuário digita (após primeiro blur)
        input.addEventListener('input', function () {
            const msg = input.parentElement.querySelector('.email-error-msg');
            if (msg && msg.textContent) {
                // só revalida se já havia erro visível
                applyState(this, validate(this.value));
            }
        });
    }

    /* ── CSS embutido para as mensagens de erro ──────────────────────────── */
    (function injectStyles() {
        if (document.getElementById('ev-styles')) return;
        const style = document.createElement('style');
        style.id = 'ev-styles';
        style.textContent = `
            .email-error-msg {
                display: none;
                color: var(--danger, #dc2626);
                font-size: 0.88rem;
                margin-top: 5px;
                font-weight: 600;
            }
        `;
        document.head.appendChild(style);
    })();

    return { validate, setup };

})();
