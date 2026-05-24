// Set minimum selectable date to today + wire email real-time validation
document.addEventListener("DOMContentLoaded", () => {
    const dateInput = document.getElementById("data_visita");
    if (dateInput) {
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 7);
        dateInput.min = minDate.toISOString().split("T")[0];
    }

    if (window.EmailValidation) {
        EmailValidation.setup(document.getElementById("email"));
    }
});

// Phone auto-format: (XX) XXXXX-XXXX
document.querySelectorAll('input[type="tel"]').forEach(input => {
    input.addEventListener('input', function () {
        let value = this.value.replace(/[^0-9]/g, '');
        if (value.length > 11) value = value.substring(0, 11);
        if (value.length > 0) {
            if (value.length <= 2) {
                value = '(' + value;
            } else if (value.length <= 6) {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2);
            } else {
                value = '(' + value.substring(0, 2) + ') ' + value.substring(2, 7) + '-' + value.substring(7);
            }
        }
        this.value = value;
    });
});

function limparFormulario(form) {
    form.reset();
}

document.getElementById("visita-form")?.addEventListener("submit", async function (e) {
    e.preventDefault();

    const form = e.target;
    const resultado = document.getElementById("resultado");
    const botao = form.querySelector("button[type='submit']");

    if (!form.checkValidity()) {
        resultado.innerText = "Por favor, preencha todos os campos obrigatórios.";
        resultado.className = "form-message erro";
        form.reportValidity();
        return;
    }

    // Validação de e-mail no cliente antes de enviar
    if (window.EmailValidation) {
        const emailInput = form.querySelector('[name="email"]');
        const emailErro = EmailValidation.validate(emailInput?.value || '');
        if (emailErro) {
            resultado.innerText = emailErro;
            resultado.className = "form-message erro";
            emailInput?.focus();
            return;
        }
    }

    botao.disabled = true;
    botao.textContent = "Enviando...";

    const formData = new FormData(form);

    const data = {
        nome_instituicao: formData.get("nome_instituicao"),
        nome_diretor: formData.get("nome_diretor"),
        nome_responsavel: formData.get("nome_responsavel"),
        telefone: formData.get("telefone"),
        email: formData.get("email"),
        data_visita: formData.get("data_visita"),
        guia_id: parseInt(formData.get("guia_id"), 10),
        faixa_etaria: formData.get("faixa_etaria"),
        qtd_visitantes: parseInt(formData.get("qtd_visitantes"), 10),
        objetivo: formData.get("objetivo"),
        observacoes: formData.get("observacoes"),
        horario_entrada: formData.get("horario_entrada"),
        horario_saida: formData.get("horario_saida"),
        aceite_termos: formData.get("aceite_termos") ? 1 : 0
    };

    try {
        const response = await fetch("/parque_ecologico/api/visita/enviar", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        resultado.innerText =
            result.erro ||
            result.mensagem ||
            "Solicitação processada.";

        resultado.className =
            response.ok ? "form-message sucesso" : "form-message erro";

        if (response.ok) {
            limparFormulario(form);
        }

    } catch {
        resultado.innerText = "Erro ao enviar. Verifique sua conexão e tente novamente.";
        resultado.className = "form-message erro";

    } finally {
        botao.disabled = false;
        botao.textContent = "Solicitar Visita Técnica";
    }
});
