const BASE_URL = "/parque_ecologico";

const loginForm = document.getElementById("login-form");
const msg = document.getElementById("login-msg");

loginForm?.addEventListener("submit", async (e) => {
    e.preventDefault();

    const botao = loginForm.querySelector("button[type='submit']");

    const data = {
        usuario: document.getElementById("login_usuario").value,
        senha: document.getElementById("login_senha").value
    };

    botao.disabled = true;
    botao.textContent = "Entrando...";
    msg.innerText = "";

    try {
        const response = await fetch(`${BASE_URL}/api/auth/login`, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            credentials: "include",
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            msg.innerText = result.mensagem || "Login realizado com sucesso.";

            setTimeout(() => {
                const userTipo = String(result.tipo || '').toLowerCase().trim();
                if (userTipo === "admin") {
                    window.location.href = `${BASE_URL}/admin`;
                } else {
                    window.location.href = `${BASE_URL}/agendamento`;
                }
            }, 700);

            return;
        }

        msg.innerText = result.erro || "Usuário ou senha incorretos.";

    } catch {
        msg.innerText = "Erro de conexão. Tente novamente.";

    } finally {
        botao.disabled = false;
        botao.textContent = "Entrar";
    }
});

// Registration removed: users are managed directly in the database by admins.
