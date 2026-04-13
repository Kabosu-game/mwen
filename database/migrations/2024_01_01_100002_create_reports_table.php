<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Signalements environnementaux citoyens
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->enum('type', [
                'illegal_dump',     // Dépôt sauvage
                'blocked_canal',    // Canal bouché
                'risk_zone',        // Zone à risque
                'flooding',         // Inondation
                'public_health',    // Risque sanitaire
                'other'
            ]);
            $table->enum('status', [
                'pending',      // En attente
                'reviewed',     // Examiné
                'in_progress',  // En cours de traitement
                'resolved',     // Résolu
                'closed',       // Fermé
                'rejected'      // Rejeté
            ])->default('pending');
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('title');
            $table->text('description');
            $table->text('address');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->json('photos')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
