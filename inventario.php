<?php
// Consulta e gerenciamento dos itens de inventário do personagem/campanha.
// Trata as ações enviadas pelo formulário do inventário.
if (isset($_POST['acao_inventario'])) {
    if ($_POST['acao_inventario'] == 'adicionar') {
        $nome_item = mysqli_real_escape_string($conexao, $_POST['nome_item']);
        $descricao = mysqli_real_escape_string($conexao, $_POST['descricao']);
        $quantidade = (int) $_POST['quantidade'];

        if (!empty($nome_item) && $quantidade > 0) {
            $sql_add = "INSERT INTO inventario (id_ficha, nome_item, descricao, quantidade)
                        VALUES ('$id_ficha', '$nome_item', '$descricao', '$quantidade')";
            mysqli_query($conexao, $sql_add);
        }
    } elseif ($_POST['acao_inventario'] == 'deletar') {
        $id_inv = (int) $_POST['id_inventario'];
        $sql_del = "DELETE FROM inventario
                    WHERE id_inventario = '$id_inv' AND id_ficha = '$id_ficha'";
        mysqli_query($conexao, $sql_del);
    }
}

// 3. BUSCA OS ITENS DESSA FICHA
$sql_itens = "SELECT * FROM inventario WHERE id_ficha = '$id_ficha' ORDER BY id_inventario DESC";
$res_itens = mysqli_query($conexao, $sql_itens);
?>
<style>
    /* Caixa principal do inventário. */
    .caixa-inventario {
        background: rgba(30, 30, 30, 0.6);
        color: #e0e0e0;
        padding: 20px;
        border-radius: 8px;
        font-family: inherit;
        box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    }

    .inventario-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 10px;
    }

    .inventario-header h3 { margin: 0; font-size: 1.2em; color: #fff; letter-spacing: 1px; }

    .btn-adicionar-inv {
        background: #cc0000; color: white; border: none;
        padding: 6px 14px; border-radius: 4px; font-weight: bold;
        cursor: pointer; transition: background 0.2s, transform 0.1s;
    }
    .btn-adicionar-inv:hover { background: #a80000; transform: scale(1.05); }

    /* Janela usada para procurar e adicionar itens. */
    .modal-overlay {
        display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background-color: rgba(0, 0, 0, 0.85); z-index: 1000;
        justify-content: center; align-items: center; backdrop-filter: blur(3px);
    }

    .modal-content {
        background-color: #1a1a1a; color: #fff; width: 90%; max-width: 700px;
        border-radius: 8px; padding: 25px; max-height: 85vh;
        display: flex; flex-direction: column; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .modal-header {
        display: flex; justify-content: space-between; align-items: center;
        border-bottom: 1px solid #333; padding-bottom: 10px; margin-bottom: 15px;
    }

    .modal-header h2 { margin: 0; font-size: 1.3em; }
    .close-btn { cursor: pointer; font-size: 1.5em; color: #888; transition: 0.3s; }
    .close-btn:hover { color: #fff; }

    .modal-search {
        background: #222; border: 1px solid #444; padding: 10px 15px;
        border-radius: 5px; color: #fff; width: 100%; margin-bottom: 15px; box-sizing: border-box;
        font-family: inherit;
    }
    .modal-search:focus { outline: none; border-color: #cc0000; }

    .modal-lista {
        overflow-y: auto; flex-grow: 1; display: flex; flex-direction: column; gap: 8px; padding-right: 5px;
    }
    .modal-lista::-webkit-scrollbar { width: 6px; }
    .modal-lista::-webkit-scrollbar-track { background: transparent; }
    .modal-lista::-webkit-scrollbar-thumb { background: #444; border-radius: 4px; }
    .modal-lista::-webkit-scrollbar-thumb:hover { background: #666; }

    /* Linhas de itens e detalhes expansíveis. */
    .item-container { 
        background: #242424; border-radius: 6px; 
        display: flex; flex-direction: column; transition: 0.2s;
    }
    .item-container:hover { background: #2a2a2a; }
    
    .item-row { padding: 12px 15px; display: flex; align-items: center; cursor: pointer; }
    
    .icon-expand { 
        color: #cc0000; font-size: 0.9em; margin-right: 15px; 
        transition: transform 0.3s ease; user-select: none; opacity: 0.7;
    }
    .item-container.open .icon-expand { transform: rotate(180deg); opacity: 1; }
    
    .item-info { flex-grow: 1; }
    .item-info h4 { margin: 0 0 4px 0; font-size: 1.05em; color: #fff; }
    
    .item-details { font-size: 0.8em; color: #999; display: flex; flex-wrap: wrap; gap: 12px; }
    .item-details span strong { color: #ccc; font-weight: 600; }

    /* Ações disponíveis em cada item. */
    .botoes-acao {
        display: flex; gap: 8px;
    }

    .btn-add-item {
        background: rgba(204, 0, 0, 0.2); color: #cc0000; border: 1px solid #cc0000; 
        width: 32px; height: 32px; border-radius: 5px; font-size: 1.2em; cursor: pointer; 
        display: flex; justify-content: center; align-items: center; transition: 0.2s;
    }
    .btn-add-item:hover { background: #cc0000; color: #fff; transform: scale(1.1); }
    
    .btn-remover-item {
        background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid #ef4444; 
        width: 32px; height: 32px; border-radius: 5px; font-size: 1.5em; cursor: pointer; 
        display: flex; justify-content: center; align-items: center; transition: 0.2s; line-height: 0;
    }
    .btn-remover-item:hover { background: #ef4444; color: #fff; transform: scale(1.1); }

    /* Botão para rolar o dano da arma. */
    .btn-rolar-inv {
        background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid #28a745; 
        width: 32px; height: 32px; border-radius: 5px; font-size: 1.2em; cursor: pointer; 
        display: flex; justify-content: center; align-items: center; transition: 0.2s;
    }
    .btn-rolar-inv:hover { background: #28a745; color: #fff; transform: scale(1.1); }

    .item-description {
        display: none; padding: 0 15px 15px 40px; font-size: 0.85em; color: #bbb;
        margin-top: 5px; line-height: 1.5;
    }
    .item-container.open .item-description { display: block; }
    
    .inventario-vazio {
        text-align: center; padding: 30px 20px; color: #888; background: rgba(0,0,0,0.2);
        border-radius: 6px; border: 1px dashed #444; font-size: 0.9em;
    }
</style>

<!-- Área onde os itens adicionados à ficha aparecem. -->
<div class="caixa-inventario">
    <div class="inventario-header">
        <h3>INVENTÁRIO</h3>
        <button type="button" class="btn-adicionar-inv" onclick="abrirModalItens()">+ Adicionar</button>
    </div>
    
    <div id="lista-inventario-equipado" style="display: flex; flex-direction: column; gap: 8px;">
        <div id="msg-inventario-vazio" class="inventario-vazio">
            Nenhum item no inventário.
        </div>
    </div>
</div>

<!-- Modal com a lista de itens disponíveis. -->
<div id="modalItens" class="modal-overlay">
    <div class="modal-content">
        
        <div class="modal-header">
            <h2>Adicionar item</h2>
            <span class="close-btn" onclick="fecharModalItens()">&times;</span>
        </div>

        <input type="text" class="modal-search" placeholder="Buscar item...">

        <div class="modal-lista">
            <?php
            // Dados das armas exibidas no modal.
            $armas = [
                ['nome' => 'Acha', 'dano' => '1d12', 'crit' => 'x3', 'tipo' => 'Corte', 'esp' => '2', 'desc' => 'Um machado grande e pesado, usado no corte de árvores largas.'],
                ['nome' => 'Arco', 'dano' => '1d6', 'crit' => 'x3', 'alcance' => 'Médio', 'tipo' => 'Perfuração', 'esp' => '2', 'mun' => 'Flechas', 'desc' => 'Um arco e flecha comum, próprio para tiro ao alvo.'],
                ['nome' => 'Arco Composto', 'dano' => '1d10', 'crit' => 'x3', 'alcance' => 'Médio', 'tipo' => 'Perfuração', 'esp' => '2', 'mun' => 'Flechas', 'desc' => 'Este arco moderno usa materiais de alta tensão. Permite aplicar Força ao dano.'],
                ['nome' => 'Balestra', 'dano' => '1d12', 'crit' => '19', 'alcance' => 'Médio', 'tipo' => 'Perfuração', 'esp' => '2', 'mun' => 'Flechas', 'desc' => 'Uma besta pesada, capaz de disparos poderosos.'],
                ['nome' => 'Bastão', 'dano' => '1d6/1d8', 'crit' => 'x2', 'tipo' => 'Impacto', 'esp' => '1', 'desc' => 'Um cilindro de madeira maciça.'],
                ['nome' => 'Bazuca', 'dano' => '10d8', 'crit' => 'x2', 'alcance' => 'Médio', 'tipo' => 'Impacto', 'esp' => '2', 'mun' => 'Foguete', 'desc' => 'Lança-foguetes concebido como arma anti-tanques.'],
                ['nome' => 'Besta', 'dano' => '1d8', 'crit' => '19', 'alcance' => 'Médio', 'tipo' => 'Perfuração', 'esp' => '2', 'mun' => 'Flechas', 'desc' => 'Arma da antiguidade, exige ação de movimento para recarregar.'],
                ['nome' => 'Cajado', 'dano' => '1d6/1d6', 'crit' => 'x2', 'tipo' => 'Impacto', 'esp' => '2', 'desc' => 'Um cabo de madeira ou barra de ferro longo. É uma arma ágil.'],
                ['nome' => 'Coronhada', 'dano' => '1d4/1d6', 'crit' => 'x2', 'tipo' => 'Impacto', 'esp' => '0', 'desc' => 'Usar uma arma de fogo como arma corpo a corpo.'],
                ['nome' => 'Corrente', 'dano' => '1d8', 'crit' => 'x2', 'tipo' => 'Impacto', 'esp' => '1', 'desc' => 'Um pedaço de corrente grossa, fornece +2 em testes para desarmar.'],
                ['nome' => 'Espada', 'dano' => '1d8/1d10', 'crit' => '19', 'tipo' => 'Corte', 'esp' => '1', 'desc' => 'Uma arma medieval, como uma espada longa ou cimitarra.'],
                ['nome' => 'Espingarda', 'dano' => '4d6', 'crit' => 'x3', 'alcance' => 'Curto', 'tipo' => 'Balístico', 'esp' => '2', 'mun' => 'Cartuchos', 'desc' => 'Arma de fogo longa e com cano liso.'],
                ['nome' => 'Faca', 'dano' => '1d4', 'crit' => '19', 'alcance' => 'Curto', 'tipo' => 'Corte', 'esp' => '1', 'desc' => 'Uma lâmina longa e afiada. Pode ser arremessada.']
            ];

            // Exibe os dados da arma e prepara o objeto usado pelo JavaScript.
            foreach ($armas as $arma) {
                $armaJson = htmlspecialchars(json_encode($arma), ENT_QUOTES, 'UTF-8');
                echo '<div class="item-container" onclick="toggleDescricao(this)">';
                echo '    <div class="item-row">';
                echo '        <span class="icon-expand" aria-hidden="true">▼</span>';
                echo '        <div class="item-info">';
                echo '            <h4>' . htmlspecialchars($arma['nome']) . '</h4>';
                echo '            <div class="item-details">';
                echo '                <span><strong>Dano:</strong> ' . htmlspecialchars($arma['dano']) . '</span>';
                echo '                <span><strong>Crítico:</strong> ' . htmlspecialchars($arma['crit']) . '</span>';
                if (isset($arma['alcance'])) {
                    echo '<span><strong>Alcance:</strong> ' . htmlspecialchars($arma['alcance']) . '</span>';
                }
                echo '                <span><strong>Tipo:</strong> ' . htmlspecialchars($arma['tipo']) . '</span>';
                echo '                <span><strong>Espaços:</strong> ' . htmlspecialchars($arma['esp']) . '</span>';
                if (isset($arma['mun'])) {
                    echo '<span><strong>Munição:</strong> ' . htmlspecialchars($arma['mun']) . '</span>';
                }
                echo '            </div>';
                echo '        </div>';
                echo '        <div class="botoes-acao">';
                echo '            <button type="button" class="btn-rolar-inv" title="Rolar Dano" onclick=\'rolarDano("'
                    . htmlspecialchars($arma['nome']) . '", "' . htmlspecialchars($arma['dano']) . '", event)\'>🎲</button>';
                echo '            <button type="button" class="btn-add-item" title="Adicionar ao inventário" onclick=\'adicionarItem(' . $armaJson . '); event.stopPropagation();\'>+</button>';
                echo '        </div>';
                echo '    </div>';
                if (isset($arma['desc'])) {
                    echo '    <div class="item-description">' . htmlspecialchars($arma['desc']) . '</div>';
                }
                echo '</div>';
            }
            ?>
        </div>
    </div>
</div>

<script>
    // Abre e fecha a janela de seleção de itens.
    function abrirModalItens() {
        document.getElementById('modalItens').style.display = 'flex';
    }

    function fecharModalItens() {
        document.getElementById('modalItens').style.display = 'none';
    }

    // Fecha o modal quando o clique acontece fora do conteúdo.
    window.addEventListener('click', function(event) {
        var modal = document.getElementById('modalItens');
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Mostra ou esconde a descrição do item selecionado.
    function toggleDescricao(elemento) {
        elemento.classList.toggle('open');
    }

    // Cria um item na lista da ficha e esconde o aviso de inventário vazio.
    function adicionarItem(item) {
        document.getElementById('msg-inventario-vazio').style.display = 'none';
        const listaEquipado = document.getElementById('lista-inventario-equipado');
        const detalhesExtras = [
            item.alcance && `<span><strong>Alcance:</strong> ${item.alcance}</span>`,
            item.mun && `<span><strong>Munição:</strong> ${item.mun}</span>`
        ].filter(Boolean).join('');

        const divItem = document.createElement('div');
        divItem.className = 'item-container';
        divItem.onclick = function() { toggleDescricao(this); };

        divItem.innerHTML = `
            <div class="item-row">
                <span class="icon-expand" aria-hidden="true">▼</span>
                <div class="item-info">
                    <h4>${item.nome}</h4>
                    <div class="item-details">
                        <span><strong>Dano:</strong> ${item.dano}</span>
                        <span><strong>Crítico:</strong> ${item.crit}</span>
                        ${detalhesExtras}
                        <span><strong>Tipo:</strong> ${item.tipo}</span>
                        <span><strong>Espaços:</strong> ${item.esp}</span>
                    </div>
                </div>
                <div class="botoes-acao">
                    <button type="button" class="btn-rolar-inv" title="Rolar Dano" onclick="rolarDano('${item.nome}', '${item.dano}', event)">🎲</button>
                    <button type="button" class="btn-remover-item" title="Remover" onclick="removerItem(this, event)">-</button>
                </div>
            </div>
            <div class="item-description">${item.desc || ''}</div>
        `;
        listaEquipado.appendChild(divItem);
        fecharModalItens();
    }

    // Remove o item clicado e restaura o aviso se a lista ficar vazia.
    function removerItem(botao, event) {
        event.stopPropagation();
        const itemContainer = botao.closest('.item-container');
        itemContainer.remove();

        const listaEquipado = document.getElementById('lista-inventario-equipado');
        if (listaEquipado.children.length === 1) {
            document.getElementById('msg-inventario-vazio').style.display = 'block';
        }
    }

    // Rola os dados informados na arma e mostra o resultado no painel da ficha.
    function rolarDano(nomeArma, danoString, event) {
        event.stopPropagation();

        // Quando há dois valores separados por "/", usa o primeiro tipo de dano.
        const danoBase = danoString.split('/')[0].trim();
        const regex = /(\d+)d(\d+)(?:\s*([+-])\s*(\d+))?/i;
        const match = danoBase.match(regex);

        if (match) {
            const qtd = parseInt(match[1], 10);
            const faces = parseInt(match[2], 10);
            const sinal = match[3];
            const mod = parseInt(match[4], 10);
            let total = 0;
            const rolagens = [];

            for (let i = 0; i < qtd; i++) {
                const resultado = Math.floor(Math.random() * faces) + 1;
                rolagens.push(resultado);
                total += resultado;
            }

            let textoRolagens = `[${rolagens.join(', ')}]`;
            if (sinal && !isNaN(mod)) {
                if (sinal === '+') total += mod;
                else if (sinal === '-') total -= mod;
                textoRolagens += ` ${sinal} ${mod}`;
            }

            const painel = document.getElementById('painel-rolagem');
            if (painel) {
                document.getElementById('toast-titulo').innerText = "Dano: " + nomeArma;
                document.getElementById('toast-dado').innerText = total;
                document.getElementById('toast-detalhes').innerText = `Rolagem: ${textoRolagens}`;
                document.getElementById('toast-total').innerText = total;
                painel.classList.add('mostrar');

                if (window.tempoEsconderRolagem) {
                    clearTimeout(window.tempoEsconderRolagem);
                }
                window.tempoEsconderRolagem = setTimeout(function() {
                    painel.classList.remove('mostrar');
                }, 4000);
            } else {
                alert(`⚔️ Dano de ${nomeArma}:\nRolagem: ${textoRolagens}\nTotal de Dano: ${total}`);
            }
        } else {
            alert("O formato deste dado não pôde ser lido corretamente: " + danoString);
        }
    }
</script>