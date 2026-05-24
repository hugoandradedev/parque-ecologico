// Gerenciamento de Bloqueios de Datas

let bloqueiosPanel = document.getElementById('bloqueios-panel');
let btnBloqueios = document.getElementById('btn-bloqueios');
let bloqueioForm = document.getElementById('bloqueio-form');
let bloqueiosList = document.getElementById('bloqueios-list');
let bloqueioMsg = document.getElementById('bloqueio-msg');
let btnCarregarDatas = document.getElementById('btn-carregar-datas');
let btnBloquearTodos = document.getElementById('btn-bloquear-todos');
let anoComemorativos = document.getElementById('ano-comemorativas');
let datasComerativasList = document.getElementById('datas-comemorativas-list');

function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function getAnoAtual() {
    return new Date().getFullYear();
}

function configurarLimitesBloqueio() {
    const hoje = new Date();
    const hojeFormatado = formatLocalDate(hoje);
    const dataInput = document.getElementById('bloqueio_data');
    const anoAtual = getAnoAtual();
    const anoLimite = anoAtual + 5;

    if (dataInput) {
        dataInput.min = hojeFormatado;
    }

    if (anoComemorativos) {
        anoComemorativos.min = String(anoAtual);
        anoComemorativos.max = String(anoLimite);
        if (!anoComemorativos.value || Number(anoComemorativos.value) < anoAtual) {
            anoComemorativos.value = String(anoAtual);
        }
    }
}

function anoComemorativoValido() {
    const ano = Number(anoComemorativos?.value);
    const anoAtual = getAnoAtual();
    const anoLimite = anoAtual + 5;

    return Number.isInteger(ano) && ano >= anoAtual && ano <= anoLimite;
}

function mensagemAnoInvalido() {
    const anoAtual = getAnoAtual();
    return `Informe um ano entre ${anoAtual} e ${anoAtual + 5}.`;
}

function escapeHtml(text) {
    if (typeof text !== 'string') return '';
    return text.replace(/[&<>"']/g, (match) => {
        switch (match) {
            case '&': return '&amp;';
            case '<': return '&lt;';
            case '>': return '&gt;';
            case '"': return '&quot;';
            case "'": return '&#39;';
            default: return match;
        }
    });
}

function attachBloqueioRemoverHandlers() {
    document.querySelectorAll('.btn-remover-bloqueio').forEach(button => {
        button.addEventListener('click', async () => {
            const id = button.dataset.id;
            if (!confirm('Desbloquear esta data?')) return;

            try {
                const token = document.getElementById('csrfToken')?.value;
                const response = await fetch(`/parque_ecologico/api/bloqueios/excluir/${encodeURIComponent(id)}`, {
                    method: 'DELETE',
                    credentials: 'include',
                    headers: token ? { 'X-CSRF-Token': token } : {}
                });

                const result = await response.json();

                if (response.ok) {
                    carregarBloqueios();
                } else {
                    alert('Erro: ' + (result.erro || 'Não foi possível remover'));
                }
            } catch (err) {
                alert('Erro na requisição');
                console.error(err);
            }
        });
    });
}

// Carregar bloqueios (chamado pelo admin.js quando a aba é activada)
function carregarBloqueios() {
fetch('/parque_ecologico/api/bloqueios/listar', {
            credentials: 'include'
        })
        .then(res => res.json())
        .then(bloqueios => {
            if (bloqueios.length === 0) {
                bloqueiosList.innerHTML = '<p style="color: #999;">Nenhuma data bloqueada.</p>';
                return;
            }

            bloqueiosList.innerHTML = bloqueios.map(b => `
                <div class="bloqueio-item">
                    <div class="bloqueio-info">
                        <strong>${new Date(b.data_bloqueada).toLocaleDateString('pt-BR')}</strong>
                        <span>${escapeHtml(b.motivo)}</span>
                    </div>
                    <button type="button" class="btn btn-danger btn-small btn-remover-bloqueio" data-id="${escapeHtml(String(b.id))}">
                        Remover
                    </button>
                </div>
            `).join('');
            attachBloqueioRemoverHandlers();
        })
        .catch(err => {
            console.error('Erro ao carregar bloqueios:', err);
            bloqueiosList.innerHTML = '<p style="color: red;">Erro ao carregar bloqueios</p>';
        });
}

// Adicionar novo bloqueio
bloqueioForm?.addEventListener('submit', async (e) => {
    e.preventDefault();

    const formData = new FormData(bloqueioForm);
    const dataBloqueada = formData.get('data_bloqueada');
    const hoje = formatLocalDate(new Date());

    if (!dataBloqueada || dataBloqueada < hoje) {
        bloqueioMsg.textContent = '✗ Não é permitido bloquear datas passadas';
        bloqueioMsg.style.color = 'red';
        return;
    }

    const data = {
        data_bloqueada: dataBloqueada,
        motivo: formData.get('motivo')
    };

    try {
        const token = document.getElementById('csrfToken')?.value;
        const response = await fetch('/parque_ecologico/api/bloqueios/criar', {
            method: 'POST',
            credentials: 'include',
            headers: Object.assign(
                { 'Content-Type': 'application/json' },
                token ? { 'X-CSRF-Token': token } : {}
            ),
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            bloqueioMsg.textContent = '✓ Data bloqueada com sucesso';
            bloqueioMsg.style.color = 'green';
            bloqueioForm.reset();
            setTimeout(() => {
                bloqueioMsg.textContent = '';
                carregarBloqueios();
            }, 1500);
        } else {
            bloqueioMsg.textContent = '✗ ' + (result.erro || 'Erro ao bloquear data');
            bloqueioMsg.style.color = 'red';
        }
    } catch (err) {
        bloqueioMsg.textContent = '✗ Erro na requisição';
        bloqueioMsg.style.color = 'red';
        console.error(err);
    }
});

// Carregar datas comemorativas
btnCarregarDatas?.addEventListener('click', async () => {
    const ano = anoComemorativos.value;

    if (!anoComemorativoValido()) {
        datasComerativasList.innerHTML = `<p style="color: red;">${escapeHtml(mensagemAnoInvalido())}</p>`;
        btnBloquearTodos.style.display = 'none';
        return;
    }

    try {
        const response = await fetch(`/parque_ecologico/api/bloqueios/datas-comemorativas?ano=${ano}`);
        const result = await response.json();

        if (!response.ok) {
            datasComerativasList.innerHTML = '<p style="color: red;">Erro ao carregar datas</p>';
            return;
        }

        const datas = result.datas;
        
        if (datas.length === 0) {
            datasComerativasList.innerHTML = '<p style="color: #999;">Nenhuma data comemorativa</p>';
            return;
        }

        // Mostrar as datas
        datasComerativasList.innerHTML = `
            <div class="datas-info">
                <p><strong>Total de datas comemorativas:</strong> ${escapeHtml(String(result.total))}</p>
                <p><strong>Já bloqueadas:</strong> ${escapeHtml(String(result.ja_bloqueados))}</p>
            </div>
            <div class="datas-table">
                ${datas.map(d => `
                    <div class="data-row ${d.ja_bloqueado ? 'bloqueada' : ''}">
                        <span class="data-date">${new Date(d.data).toLocaleDateString('pt-BR')}</span>
                        <span class="data-nome">${escapeHtml(d.nome)}</span>
                        ${d.ja_bloqueado ? '<span class="badge-bloqueado">✓ Bloqueada</span>' : ''}
                    </div>
                `).join('')}
            </div>
        `;

        // Mostrar botão para bloquear todos se houver não bloqueados
        if (result.ja_bloqueados < result.total) {
            btnBloquearTodos.style.display = 'inline-block';
        } else {
            btnBloquearTodos.style.display = 'none';
        }

    } catch (err) {
        datasComerativasList.innerHTML = '<p style="color: red;">Erro ao carregar datas</p>';
        console.error(err);
    }
});

// Bloquear todas as datas comemorativas de uma vez
btnBloquearTodos?.addEventListener('click', async () => {
    const ano = anoComemorativos.value;

    if (!anoComemorativoValido()) {
        alert(mensagemAnoInvalido());
        return;
    }

    if (!confirm(`Bloquear todas as datas comemorativas de ${ano}?`)) return;

    try {
        const token = document.getElementById('csrfToken')?.value;
        const formData = new FormData();
        formData.append('ano', ano);

        const response = await fetch('/parque_ecologico/api/bloqueios/bloquear-todos', {
            method: 'POST',
            credentials: 'include',
            headers: token ? { 'X-CSRF-Token': token } : {},
            body: formData
        });

        const result = await response.json();

        if (response.ok) {
            alert(result.mensagem);
            carregarBloqueios();
            // Recarregar datas comemorativas
            btnCarregarDatas.click();
        } else {
            alert('Erro: ' + (result.erro || 'Não foi possível bloquear datas'));
        }
    } catch (err) {
        alert('Erro na requisição');
        console.error(err);
    }
});

configurarLimitesBloqueio();
