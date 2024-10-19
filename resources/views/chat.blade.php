
@extends('layout.main')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('titulo', 'Chat')

@section('content')
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>

<div id="chat">
    <h3>Mensagens</h3>
    <!-- Renderizar mensagens dinamicamente com Vue -->
    <div v-for="message in messages" :key="message.id" :class="{'sent': message.sender_id === userId, 'received': message.sender_id !== userId}">
        <small>@{{ new Date(message.created_at).toLocaleDateString() }} @{{ new Date(message.created_at).toLocaleTimeString() }}</small>
        <p>@{{ message.content }}</p>
    </div>
    
    <form id="message-form" @submit.prevent="sendMessage" class="mt-3">
        @csrf        
        <div class="input-group">
            <textarea v-model="messageContent" placeholder="Digite uma mensagem..." class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-success mt-2">Enviar</button>
    </form>
</div>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const { createApp, ref, onMounted } = Vue;

    // Inicializar o Pusher com as variáveis do .env (MUDAR ISSO PARA O BACKEND)
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
        encrypted: true
    });

    const app = createApp({
        setup() {
            const messageContent = ref('');
            const messages = ref([]);
            const userId = {{ Auth::id() }};
            const receiverId = {{ $receiver->id }};
            let sendTime = 0;

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
                .then(response => response.json())
                .then(data => {
                    if (data.status === 201) {
                        // Adiciona a mensagem localmente sem precisar buscar as mensagens
                        messages.value.push({
                            content: messageContent.value,
                            sender_id: userId,
                            created_at: new Date().toISOString() // Data atual
                        });
                        messageContent.value = ''; // Limpa o campo de mensagem
                    } else {
                        alert('Erro ao enviar mensagem');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Erro ao enviar mensagem');
                });
            };

            // Preencher mensagens ao carregar
            onMounted(() => {
                fetchMessages();

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
                    }

                });
            });


            return {
                messageContent,
                messages,
                userId,
                receiverId,
                sendMessage,
            };
        },
    }).mount('#chat');
});
</script>


@endsection
