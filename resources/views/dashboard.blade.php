
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo', 'Dashboard')

@section('content')

{{-- Mensagem de tutorial para baixar a chave --}}
@if($privateKey)
        <!-- Modal -->
        <div class="modal fade" id="TutorialModal" tabindex="-1" aria-labelledby="tutorialModal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="tutorialModalLabel">Modal title</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{ $privateKey }}
                    <button id="downloadBtn">Baixar Chave Privada</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary">Save changes</button>
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

    <!-- Lista de usuários -->
    <div class="user-list"><!-- Botão para abrir modal -->
        <h3>Usuários Cadastrados</h3>
        <div style="display: flex; flex-direction: column; gap: 12px;">
        @foreach ($usuarios as $user)
            @if ($user->id !== $currentUserId) <!-- Verifica se o ID do usuário não é igual ao do usuário atual -->
                <button data-user-id="{{ $user->id }}" style="width: fit-content; border: 1px solid white;" type="submit">Chat com {{ $user->name }}</button>
            
                <!-- Modal Bootstrap -->
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
            @endif
        @endforeach
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const privateKeyModal = new bootstrap.Modal(document.getElementById('privateKeyModal'));

        // Verifica se o parâmetro error=invalid_key está presente na URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('error') && urlParams.get('error') === 'invalid_key') {
            alert('Chave privada inválida, tente novamente');
            privateKeyModal.show();
        }

        const buttons = document.querySelectorAll('button[data-user-id]');

        buttons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-user-id');
                document.getElementById('userIdInput').value = userId; // Define o ID do usuário no campo oculto
            
                // Verifica se a chave privada já está definida no sessionStorage
                const storedKey = sessionStorage.getItem('key');

                if (storedKey) {
                    privateKeyModal.hide();
                    // Redireciona para a página de chat com o ID do usuário
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
