<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users');
            $table->foreignId('receiver_id')->constrained('users');
            $table->string('file_path')->nullable(); // Caminho para o arquivo, se houver
            $table->text('content')->nullable(); // Conteúdo de texto, se não for um arquivo
            $table->text('encryption_key_sender')->nullable(); // Chave de criptografia AES
            $table->text('encryption_key_receiver')->nullable(); // Chave de criptografia AES
            $table->boolean('encrypted')->default(true); // Se a mensagem está criptografada
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
