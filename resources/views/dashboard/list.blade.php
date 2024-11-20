
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo', 'Lista de usuários')

@section('content')

<!-- Lista de usuários -->
<div class="contact-list">
    <h3>Busque um usuário para conversar aqui</h3>
    <div class="buscaContato">
        <label for="userSearchInput">Buscar usuário:</label>
        <input type="text" id="userSearchInput" placeholder="Buscar usuários por nome ou e-mail" class="form-control">
    </div>

    <div class="listagem">
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
                </div>
            @endif
        @endforeach
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
