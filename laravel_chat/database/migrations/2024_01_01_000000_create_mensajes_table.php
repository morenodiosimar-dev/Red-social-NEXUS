<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('mensajes')) {
            Schema::create('mensajes', function (Blueprint $table) {
                $table->id();
                $table->string('sala')->index(); // ID de sala "user1-user2"
                $table->unsignedBigInteger('usuario'); // ID del remitente
                $table->text('mensaje');
                $table->timestamp('fecha')->useCurrent();
                $table->boolean('leido')->default(false);

                // Foreign key constraint (assuming 'usuarios' table exists and has 'id')
                // Note: If 'usuarios' table is MyISAM or has different collation, this might fail.
                // Safest to just index it for now or check if we can add constraint.
                // Given legacy nature, let's stick to index to avoid FK errors if types mismatch.
                $table->index('usuario');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mensajes');
    }
};
