<div>
    <button data-bs-toggle="modal" data-bs-target="#modalGenerateKeys" class="btn btn-primary me-2">
        Gerar Novas Chaves
    </button>

    <div class="modal fade" id="modalGenerateKeys" tabindex="-1" aria-labelledby="modalGenerateKeysLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="modalGenerateKeysLabel">Gerar Novas Chaves</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start">
                <h3 class="text-center" style="color: #dc3545;">IMPORTANTE!</h3>
                <strong>Ao gerar um novo par de chaves, todas suas interações serão excluídas!</strong>
                <p>Gere novas chaves, apenas se:</p>
                <ul style="list-style: disc;">
                    <li>perdeu sua chave</li>
                    <li>Acredita que sua chave foi comprometida</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" wire:click="generateKeys" class="btn btn-danger">Gerar chaves</button>
            </div>
            </div>
        </div>
    </div>
</div>