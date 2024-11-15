
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo', 'Painel de controle - Administrador')

@section('content')
@if(session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger" role="alert">
        <ul class="m-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<section class="row">
    <article class="col-lg-3 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Métricas do sistema</h5>
            </div>
            <div class="card-body">
                <h6 class="card-text">Quantidade de mensagens:</h6>
                <span>{{ $mensagens->count() }}</span>
                @php
                    $totalStorageUsed = number_format($usuarios->sum('storage_used') / (1024 * 1024 * 1024), 2); 
                @endphp
                <h6 class="card-text">Armazenamento usado:</h6>
                <span>{{ $totalStorageUsed }} GB</span>
            </div>
        </div>
    </article>
    <article class="col-lg-9 col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Usuários cadastrados</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Usuário</th>
                            <th scope="col">Email</th>
                            <th scope="col">Setor</th>
                            <th scope="col">Armazenamento</th>
                            <th scope="col">#</th>
                            <th scope="col">#</th>
                            <th scope="col">#</th>
                        </tr>
                    </thead>
                    <tbody>
                        <script src="https://unpkg.com/progressbar.js@1.1.0/dist/progressbar.min.js"></script>
                        @foreach($usuarios as $usuario)
                            @if($currentUserId != $usuario->id)
                                <tr>
                                    <th scope="row">{{ $usuario->id }}</th>
                                    <td>{{ $usuario->name }}</td>
                                    <td>{{ $usuario->email }}</td>
                                    <td>{{ $usuario->sector }}</td>
                                    @php
                                        $storageUsed = $usuario->storage_used = number_format($usuario->storage_used / (1024 * 1024), 2);
                                    @endphp
                                    <td>
                                        <div class="d-flex justify-content-between">
                                            <span>
                                                {{ $storageUsed }} MB
                                            </span>
                                            <div id="circle{{ $usuario->id }}" style="width: 25px; height: 25px;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-primary px-2" title="Editar usuário" data-bs-toggle="modal" data-bs-target="#editUserModal{{ $usuario->id }}">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-danger px-2" title="Remover usuário" data-bs-toggle="modal" data-bs-target="#removeModal{{ $usuario->id }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <button class="btn btn-secondary px-2" title="Remover arquivos" data-bs-toggle="modal" data-bs-target="#excluirArquivos{{ $usuario->id }}">
                                            <i class="bi bi-folder-minus"></i>
                                        </button>
                                    </td>
                                </tr>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        // Use o ID único para cada círculo (para cada usuário)
                                        const container = document.getElementById('circle{{ $usuario->id }}');
                                        const storageUsed = {{ $storageUsed }};  // Usando a variável do Blade
                                        const totalStorage = 200;  // Total de armazenamento (200MB por usuário)
                                        const percentage = storageUsed / totalStorage; // Percentual de uso de armazenamento

                                        // Inicializando o ProgressBar para o círculo
                                        let circle = new ProgressBar.Circle(container, {
                                            color: '#6875f5',
                                            strokeWidth: 20,
                                            duration: 1800,
                                            trailColor: '#DDDDDD',
                                            from: { color: '#AAA' },
                                            to: { color: '#6875f5' },
                                        });

                                        // Animando o círculo com o percentual de uso
                                        circle.animate(percentage);  // Define o progresso com base no percentual
                                    });
                                </script>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </article>
</section>

@foreach($usuarios as $usuario)
    @if($currentUserId != $usuario->id)
    <div class="modal fade" id="editUserModal{{ $usuario->id }}" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Editar usuário: {{ $usuario->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm" action="{{ route('user.update', $usuario->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="userId" value="{{ $usuario->id }}">
                        <div class="mb-3">
                            <label for="userName" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="userName" name="nome" value="{{ $usuario->name }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" value="{{ $usuario->email }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="userSector" class="form-label">Setor</label>
                            <input type="text" class="form-control" id="userSector" name="setor" placeholder="Financeiro" value="{{ $usuario->sector }}">
                        </div>
                        <div class="mb-3">
                            <label for="userPassword" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="userPassword" name="senha" placeholder="Nova senha">
                        </div>

                        <div class="w-100 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Atualizar dados</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="removeModal{{ $usuario->id }}" tabindex="-1" aria-labelledby="removeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="removeModalLabel">Remover usuário: {{ $usuario->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRemoveForm" action="{{ route('user.remover', $usuario->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <h5 class="mb-2">Tem certeza que deseja remover o usuário?</h5>
                        <p class="mb-3">Todas conversas com o usuário serão removidas</p>
                        <div class="w-100 d-flex justify-content-center">
                            <button type="submit" class="btn btn-danger">Remover</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="excluirArquivos{{ $usuario->id }}" tabindex="-1" aria-labelledby="excluirArquivosLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="excluirArquivosLabel">Remover Anexos de {{ $usuario->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRemoveForm" action="{{ route('user.removerArquivos', $usuario->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <h5 class="mb-2">Tem certeza que deseja os arquivos do usuário?</h5>
                        <p class="mb-3">Não serão ofertados backup, para os arquivos</p>
                        <div class="w-100 d-flex justify-content-center">
                            <button type="submit" class="btn btn-danger">Remover</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection