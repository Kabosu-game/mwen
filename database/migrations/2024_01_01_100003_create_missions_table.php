<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Missions Travay Vèt (micro-emplois écologiques)
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('zone_id')->nullable()->constrained('zones')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->enum('type', [
                'collection',   // Collecte de déchets
                'cleaning',     // Nettoyage
                'sorting',      // Tri
                'awareness',    // Sensibilisation
                'other'
            ])->default('collection');
            $table->enum('status', [
                'draft',        // Brouillon
                'open',         // Ouverte aux candidatures
                'in_progress',  // En cours
                'completed',    // Terminée
                'cancelled'     // Annulée
            ])->default('draft');
            $table->integer('slots')->default(1);           // Nb de places disponibles
            $table->integer('slots_taken')->default(0);
            $table->decimal('payment', 10, 2)->default(0);  // Rémunération HTG
            $table->text('address');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->json('requirements')->nullable();        // Critères requis
            $table->json('equipment_provided')->nullable(); // Équipements fournis
            $table->timestamps();
            $table->softDeletes();
        });

        // Candidatures aux missions
        Schema::create('mission_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'completed',
                'cancelled'
            ])->default('pending');
            $table->text('motivation')->nullable();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('rating')->nullable();
            $table->text('review')->nullable();
            $table->boolean('payment_sent')->default(false);
            $table->timestamps();
            $table->unique(['mission_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mission_applications');
        Schema::dropIfExists('missions');
    }
};
