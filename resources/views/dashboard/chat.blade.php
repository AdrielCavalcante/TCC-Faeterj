
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo')
    Chat com {{ $receiver->name }} @if($receiver->sector) - {{ $receiver->sector }} @endif
@endsection

@section('imagemTitulo')
    {{ $receiver->profile_photo_url }}
@endsection

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<section id="chat">
    <section class="col-lg-3 col-12">
        <article class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Listagem de anexos:</h5>
            </div>
            <div class="card-body">
                <div v-for="message in messages" :key="message.id">
                    <div class="d-flex flex-column mt-2 mb-2" v-if="message.file_path">
                        <h6 class="card-text">Arquivo @{{message.file_path.slice(-3).toUpperCase()}}</h6>
                        <a :href="`#${message.id}`">Ir para o Anexo</a>
                        <div class="box-button" :id="message.id">
                            <button class="border" @click="downloadFile(message.id, {'sender': message.sender_id === userId, 'receiver': message.sender_id !== userId})">Baixar Arquivo</button>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    </section>
    <section class="chat-box col-lg-9 col-12">
        <section class="Innerchat">
            <!-- Renderizar mensagens dinamicamente com Vue -->
            <article v-for="message in messages" :key="message.id" :class="{'sent': message.sender_id === userId, 'received': message.sender_id !== userId}">
                <small>@{{ new Date(message.created_at).toLocaleDateString() }} @{{ new Date(message.created_at).toLocaleTimeString() }}</small>
                <div class="box-chat" v-if="message.content">
                    <p>@{{ message.content }}</p>
                </div>
                <div class="box-button" :id="message.id" v-else>
                    <button class="border" v-if="message.file_path" @click="downloadFile(message.id, {'sender': message.sender_id === userId, 'receiver': message.sender_id !== userId})">Baixar Arquivo</button>
                </div>
            </article>
        </section>
        
        <form id="message-form" @submit.prevent="sendMessage" class="mt-3">
            @csrf        
            <div class="input-group">
                <textarea 
                v-model="messageContent"
                placeholder="Digite uma mensagem..."
                class="form-control"
                @input="clearFile"
                :disabled="selectedFile !== null"
                @keydown="handleKeyDown"
                rows="1"></textarea>
                <i @click="triggerFileInput" class="bi bi-paperclip"></i>
                <input class="d-none" type="file" ref="fileInput" @change="handleFileChange" />
            </div>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-send"></i>
            </button>
        </form>
    </section>
</section>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() { 
    const { createApp, ref, onMounted, watchEffect } = Vue;

    // Inicializar o Pusher com as variáveis do .env (MUDAR ISSO PARA O BACKEND)
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        encrypted: true,
    });

    const app = createApp({
        setup() {
            const messageContent = ref('');
            const messages = ref([]);
            const userId = {{ Auth::id() }};
            const receiverId = {{ $receiver->id }};
            const selectedFile = ref(null);
            const fileInput = ref(null);
            const loading = ref(false);

            let sendTime = 0;

            const handleFileChange = (event) => {
                // Captura o arquivo selecionado
                selectedFile.value = event.target.files[0];
                // Limpa o campo de mensagem se um arquivo foi selecionado
                if (selectedFile.value) {
                    messageContent.value = selectedFile.value.name; 
                }
            };

            const clearFile = () => {
                // Limpa o arquivo selecionado se o campo de texto for editado
                if (messageContent.value) {
                    selectedFile.value = null; 
                    fileInput.value = '';
                }
            };

            const fetchMessages = () => {
                const keyVar = sessionStorage.getItem('key');

                fetch(`{{ url('chatMessage') }}/${receiverId}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        key: keyVar
                    })
                })
                .then(response => response.json())
                .then(data => {
                    messages.value = data;
                })
                .catch(error => {
                    window.location.href = '/dashboard?error=invalid_key';
                }); 
            };

            const sendMessage = () => {
                loading.value = true;
                if (selectedFile.value) {
                    const formData = new FormData();
                    formData.append('file', selectedFile.value);
                    formData.append('receiver_id', receiverId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    formData.append('cipher', 1);

                    sendTime = performance.now();

                    fetch("{{ route('send.message') }}", {
                        method: 'POST',
                        body: formData,
                    })
                    .then(response => {
                        loading.value = false;
                        return response.json().then(data => {
                            return {
                                status: response.status,
                                data: data
                            };
                        });
                    })
                    .then(({ status, data }) => {
                        if (status === 201) {
                            // Aqui você pode adicionar lógica para exibir a mensagem enviada
                            messages.value.push({
                                content: selectedFile.value.name, // Ou qualquer outro conteúdo que deseja mostrar
                                sender_id: userId,
                                created_at: new Date().toISOString() // Data atual
                            });
                            selectedFile.value = null;
                            fileInput.value.value = '';
                            messageContent.value = '';
                            scrollToBottom();
                        } else if (status === 401) {
                            alert('Erro ao enviar mensagem com o arquivo: ' + data.message);
                        } else if (status === 413) {
                            alert('Erro ao enviar mensagem com o arquivo: Arquivo muito grande, limite é 40MB');
                        } else if (status === 422) {
                            alert('Erro ao enviar mensagem com o arquivo: Arquivo inválido');
                        } else {
                            alert('Erro ao enviar mensagem com o arquivo: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao enviar mensagem com o arquivo');
                    });
                } else if (messageContent.value) { // Se não houver arquivo, mas houver conteúdo
                    const formData = new FormData();
                    formData.append('content', messageContent.value);
                    formData.append('receiver_id', receiverId);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                    formData.append('cipher', 1);

                    sendTime = performance.now();

                    fetch("{{ route('send.message') }}", {
                        method: 'POST',
                        body: formData
                    })
                    .then(response =>  {
                        loading.value = false;
                        return response.json().then(data => {
                            return {
                                status: response.status,
                                data: data
                            };
                        });
                    })
                    .then(({ status, data }) => {
                        if (status === 201) {
                            messages.value.push({
                                content: messageContent.value,
                                sender_id: userId,
                                created_at: new Date().toISOString() // Data atual
                            });
                            messageContent.value = ''; // Limpa o campo de mensagem
                            scrollToBottom();
                        } else if (status === 401) {
                            alert('Erro ao enviar mensagem: ' + data.message);
                        } else if (status === 422) {
                            alert('Erro ao enviar mensagem inválida');
                        } else {
                            alert('Erro ao enviar mensagem: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Erro ao enviar mensagem');
                    });
                } else {
                    alert('Por favor, insira uma mensagem ou selecione um arquivo para enviar.');
                }
            };

            const downloadFile = (messageId, owner) => {
                if(owner.sender) {
                    owner = 'sender';
                } else if(owner.receiver) {
                    owner = 'receiver';
                } else {
                    owner = 'unknown';
                }

                const keyVar = sessionStorage.getItem('key'); // Obtém a chave da sessão

                const url = `/download-file/${messageId}?key=${encodeURIComponent(keyVar)}&owner=${encodeURIComponent(owner)}`;
                window.open(url, '_blank');
            }

            const triggerFileInput = () => {
                fileInput.value.click();  // Simula o clique no input de arquivo
            };

            const handleKeyDown = (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();  // Evita a quebra de linha
                    sendMessage();  // Chama a função para enviar a mensagem
                }
            };

            const scrollToBottom = () => {
                const chatContainer = document.querySelector('.Innerchat');

                if (chatContainer) {
                    setTimeout(() => {
                        chatContainer.scrollTop = Number.MAX_SAFE_INTEGER;
                    }, 750);
                }
            };

            // Preencher mensagens ao carregar
            onMounted(() => {
                fetchMessages();

                // Observa o estado de conexão
                pusher.connection.bind('state_change', (states) => {
                    // Verifica se a conexão foi perdida (state = disconnected)
                    if (states.current === 'disconnected') {
                        pusher.connect();
                    }
                });
                
                // Inicializa a conexão
                pusher.connect();
               
                scrollToBottom();
                
                // Garantir que o ID menor vem primeiro
                const chatChannelId = userId < receiverId 
                    ? userId + '.' + receiverId 
                    : receiverId + '.' + userId;

                const channel = pusher.subscribe('chat.' + chatChannelId);

                channel.bind('MessageSent', function(data) {
                    const receiveTime = performance.now();
                    const latency = receiveTime - sendTime; 

                    console.log('Nova mensagem recebida:', data);
                    console.log(`Tempo de latência: ${latency.toFixed(2)} ms`);

                    if(data.sender_id !== userId) {
                        messages.value.push({
                            content: data.content,
                            sender_id: data.sender_id,
                            created_at: new Date(data.created_at).toISOString()
                        });
                        scrollToBottom();
                    }

                });
            });

            return {
                messageContent,
                messages,
                userId,
                receiverId,
                loading,
                sendMessage,
                handleFileChange,
                selectedFile,
                clearFile,
                fileInput,
                downloadFile,
                triggerFileInput,
                handleKeyDown
            };
        },
    }).mount('#chat');
});
</script>


@endsection
