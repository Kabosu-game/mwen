<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collection_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('citizen_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('collector_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->enum('status', [
                'pending',      // En attente
                'assigned',     // Assigné à un collecteur
                'in_progress',  // En cours
                'completed',    // Terminé
                'cancelled',    // Annulé
                'rejected'      // Rejeté
            ])->default('pending');
            $table->enum('waste_type', [
                'household',    // Déchets ménagers
                'organic',      // Déchets organiques
                'recyclable',   // Recyclables
                'hazardous',    // Déchets dangereux
                'construction', // Débris construction
                'other'
            ])->default('household');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'free'])->default('free');
            $table->text('cancellation_reason')->nullable();
            $table->integer('rating')->nullable(); // 1-5
            $table->text('review')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_requests');
    }
};
