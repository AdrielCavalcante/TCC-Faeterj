
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo', 'Painel de controle')

@section('content')

{{-- Mensagem de tutorial para baixar a chave --}}
@if($privateKey)
    <!-- Modal -->
    <div class="modal fade" data-bs-backdrop="static" data-bs-keyboard="false" id="TutorialModal" tabindex="-1" aria-labelledby="tutorialModal" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tutorialModalLabel">Baixar chave</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h1 class="mb-3 text-center text-danger">ATENÇÃO</h1>
                    <p class="mb-3 text-danger">Antes de baixar a chave, é <strong>RECOMENDADO</strong> que veja o PDF orientando, onde salvar a chave privada e sua importância.</p>
                    <div class="d-flex justify-content-center mb-2">
                        <a href="{{ asset('storage/pdf/Orientacao-chave-privada.pdf') }}" target="_blank">Ver PDF de orientação</a>
                    </div>
                    <div class="d-flex justify-content-center">
                        <button id="downloadBtn" data-bs-dismiss="modal">Baixar Chave Privada</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Verifica se a modal deve ser exibida
            const tutorialModal = new bootstrap.Modal(document.getElementById('TutorialModal'));
            tutorialModal.show(); // Exibe a modal

            const modalTutorialElemento = document.getElementById('TutorialModal');

            modalTutorialElemento.addEventListener('hidden.bs.modal', function () {
                window.location.href = window.location.pathname + '?removeKey';
            });

            document.getElementById('downloadBtn').addEventListener('click', function () {
                const content = String.raw`{{ $privateKey }}`;
                const filename = "privateKey.key";

                // Cria um Blob com o conteúdo
                const blob = new Blob([content], { type: 'text/plain' });

                // Cria uma URL para o Blob
                const url = URL.createObjectURL(blob);

                // Cria um link temporário
                const a = document.createElement('a');
                a.href = url;
                a.download = filename;

                // Adiciona o link ao documento e clica nele
                document.body.appendChild(a);
                a.click();

                // Remove o link e libera a URL do Blob
                document.body.removeChild(a);
                URL.revokeObjectURL(url);
            });
        });
    </script>
@endif

<!-- Lista de usuários conectados -->
<div class="contact-list">
    <h3>Lista de contatos</h3>
    <div class="buscaContato">
        <label for="userSearchInput">Buscar contato:</label>
        <input type="text" id="userSearchInput" placeholder="Buscar usuários por nome ou e-mail" class="form-control">
    </div>

    <div class="listagem">
        <div class="authProfile">
            <div class="profileImage">
                <img src="{{ auth()->user()->profile_photo_url }}" alt="foto do usuário: {{ auth()->user()->name }}">
            </div>
            <div class="d-flex gap-4 align-items-center">
                <strong class="profileName">{{ auth()->user()->name }}</strong>
                @if (auth()->user()->sector)        
                    <hr style="width: 20px; opacity: 1; margin: 0 -1rem; border-color: var(--cor-secundaria); transform: rotate(90deg);">
                    <span>{{ auth()->user()->sector }}</span>
                @endif
            </div>
        </div>
        @if ($usuarios->isNotEmpty())
            <div class="d-flex justify-content-center mt-4" id="naoAchou">
                <strong style="color: var(--black);">Nenhum contato encontrado</strong>
            </div>
            @foreach ($usuarios as $user)
                @if ($user->id !== $currentUserId && $user->roles[0]->name != 'admin')
                    <div class="chatUser" 
                    data-user-id="{{ $user->id }}" 
                    data-name="{{ $user->name }}" 
                    data-email="{{ $user->email }}">
                        <div class="profileImage">
                            <img src="{{ $user->profile_photo_url }}" alt="foto do usuário: {{ $user->name }}">
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <strong class="profileName">{{ $user->name }}</strong>
                            @if ($user->sector) 
                                <hr style="width: 20px; opacity: 1; border-color: var(--cor-secundaria); transform: rotate(90deg);">
                                <span style="color: var(--cor-secundaria)">{{ $user->sector }}</span>
                            @endif
                        </div>
                        <div class="ms-auto">
                        @if ($user->sentMessages->isNotEmpty() && $user->receivedMessages->isNotEmpty())
                            @if ($user->sentMessages->first()->receiver_id == $currentUserId)
                                @if (!$user->sentMessages->first()->read)
                                    <div class="mensagemNaoLida"></div>
                                @endif
                            @elseif ($user->receivedMessages->first()->sender_id == $currentUserId)
                                @if (!$user->receivedMessages->first()->read)
                                    <div class="mensagemNaoLida"></div>
                                @endif
                            @endif
                        @elseif ($user->sentMessages->isNotEmpty() && $user->receivedMessages->isEmpty())
                            @if ($user->sentMessages->first()->receiver_id == $currentUserId)
                                @if (!$user->sentMessages->first()->read)
                                    <div class="mensagemNaoLida"></div>
                                @endif
                            @endif
                        @elseif ($user->sentMessages->isEmpty() && $user->receivedMessages->isNotEmpty())
                            @if ($user->receivedMessages->first()->sender_id == $currentUserId)
                                @if (!$user->receivedMessages->first()->read)
                                    <div class="mensagemNaoLida"></div>
                                @endif
                            @endif
                        @endif
                        </div>
                    </div>
                @endif
            @endforeach
        @else
            <p class="mt-5 nenhum">Nenhuma conversa iniciada</p>
        @endif
    </div>
</div>

<!-- Modal para inserir chave privada -->
<div class="modal fade" id="privateKeyModal" tabindex="-1" aria-labelledby="privateKeyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="privateKeyModalLabel">Inserir Chave Privada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="privateKeyForm">
                    <div class="mb-3">
                        <input type="hidden" id="userIdInput" value="">
                        <label for="privateKeyInput" class="form-label">Chave Privada</label>
                        <input type="file" class="form-control" id="privateKeyInput" accept=".pem,.key" required>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Chave Privada</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const privateKeyModal = new bootstrap.Modal(document.getElementById('privateKeyModal'));

        @if($privateKey)
            const privateKey = {{$privateKey}}
            sessionStorage.setItem('key', privateKey);
        @endif

        // Verifica se o parâmetro error=invalid_key está presente na URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error') && urlParams.get('error') === 'invalid_key') {
            alert('Chave privada inválida, tente novamente');
            privateKeyModal.show();
        }

        const buttons = document.querySelectorAll('.chatUser');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                console.log('Clicou no botão');
                const userId = this.getAttribute('data-user-id');
                document.getElementById('userIdInput').value = userId;

                // Verifica se a chave privada já está definida no sessionStorage
                const storedKey = sessionStorage.getItem('key');

                if (storedKey) {
                    privateKeyModal.hide();
                    window.location.href = `/chat/${userId}`;
                } else {
                    privateKeyModal.show();
                }
            });
        });

        document.getElementById('privateKeyForm').addEventListener('submit', function(event) {
            event.preventDefault(); // Evita o envio padrão do formulário

            const fileInput = document.getElementById('privateKeyInput');
            const file = fileInput.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const privateKeyContent = e.target.result;
                    sessionStorage.setItem('key', privateKeyContent); // Armazena a chave no sessionStorage

                    // Pega o ID do usuário diretamente do campo oculto
                    const userId = document.getElementById('userIdInput').value; 

                    if(userId) {
                        // Redireciona para a página de chat com o ID do usuário
                        window.location.href = `/chat/${userId}`;
                    } else {
                        privateKeyModal.hide();
                        alert('Chave privada salva com sucesso');
                    }

                };
                reader.readAsText(file); // Lê o arquivo como texto
            }
        });
    });
</script>
@endsection
