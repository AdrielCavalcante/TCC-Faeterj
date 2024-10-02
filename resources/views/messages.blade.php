<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <style>
        #chat {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 10px;
        }
        .message {
            margin-bottom: 10px;
        }
        .message p {
            margin: 0;
            padding: 5px;
            border-radius: 5px;
        }
        .message.sent p {
            background-color: #e0f7fa;
            text-align: right;
        }
        .message.received p {
            background-color: #f1f8e9;
            text-align: left;
        }
    </style>
</head>
<body>
<div id="app">
    <div id="chat">
        @foreach ($messages as $message)
            <div class="message {{ $message->sender_id == $userId ? 'sent' : 'received' }}">
                <p>{{ $message->content }}</p>
            </div>
        @endforeach
    </div>
    <form id="message-form" method="POST" action="{{ route('send.message') }}">
        @csrf
        <input type="hidden" name="receiver_id" value="{{ $receiverId }}">
        <textarea name="content" placeholder="Type a message..."></textarea>
        <button type="submit">Send</button>
    </form>
</div>

<script src="{{ mix('js/app.js') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('message-form');

        if (form) {
            form.addEventListener('submit', function(event) {
                event.preventDefault();

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 201) {
                        window.location.reload();
                    } else {
                        alert('Error sending message');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error sending message');
                });
            });
        } else {
            console.error('Formulário não encontrado');
        }
    });
</script>

</body>
</html>
