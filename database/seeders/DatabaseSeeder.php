<?php

namespace Database\Seeders;

use App\Models\CollectionRequest;
use App\Models\Mission;
use App\Models\MissionApplication;
use App\Models\Report;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Super Admin ────────────────────────────────────────────────────
        $admin = User::create([
            'name' => 'Administrateur Mwen Pwòp',
            'phone' => '+50999000001',
            'email' => 'admin@mwenpwop.ht',
            'password' => Hash::make('Admin@2025'),
            'role' => 'super_admin',
            'status' => 'active',
            'commune' => 'Port-au-Prince',
            'department' => 'Ouest',
        ]);

        // ── Zones ──────────────────────────────────────────────────────────
        $zones = [
            ['name' => 'Centre-Ville PAP', 'commune' => 'Port-au-Prince', 'department' => 'Ouest'],
            ['name' => 'Pétionville', 'commune' => 'Pétionville', 'department' => 'Ouest'],
            ['name' => 'Carrefour', 'commune' => 'Carrefour', 'department' => 'Ouest'],
            ['name' => 'Delmas', 'commune' => 'Delmas', 'department' => 'Ouest'],
            ['name' => 'Cité Soleil', 'commune' => 'Cité Soleil', 'department' => 'Ouest'],
            ['name' => 'Cap-Haïtien Centre', 'commune' => 'Cap-Haïtien', 'department' => 'Nord'],
            ['name' => 'Gonaïves', 'commune' => 'Gonaïves', 'department' => 'Artibonite'],
            ['name' => 'Les Cayes', 'commune' => 'Les Cayes', 'department' => 'Sud'],
        ];

        foreach ($zones as $z) {
            Zone::create(array_merge($z, ['is_active' => true]));
        }

        // ── Subscription Plans ─────────────────────────────────────────────
        $plans = [
            [
                'name' => 'Basique',
                'slug' => 'basic',
                'description' => 'Pour les particuliers',
                'price_monthly' => 500,
                'price_yearly' => 5000,
                'collections_per_month' => 4,
                'features' => ['4 collectes par mois', 'Signalements illimités', 'Support standard'],
                'sort_order' => 1,
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'description' => 'Pour les familles et petits commerces',
                'price_monthly' => 1500,
                'price_yearly' => 15000,
                'collections_per_month' => 12,
                'features' => ['12 collectes par mois', 'Priorité haute', 'Signalements illimités', 'Support prioritaire'],
                'sort_order' => 2,
            ],
            [
                'name' => 'Entreprise',
                'slug' => 'enterprise',
                'description' => 'Pour les entreprises et commerces',
                'price_monthly' => 5000,
                'price_yearly' => 50000,
                'collections_per_month' => -1,
                'features' => ['Collectes illimitées', 'Priorité urgente', 'Agent dédié', 'Rapport mensuel', 'Support 24/7'],
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $p) {
            SubscriptionPlan::create($p);
        }

        // ── Sample Collectors ──────────────────────────────────────────────
        $zone1 = Zone::first();
        $collector1 = User::create([
            'name' => 'Jean-Baptiste Collecteur',
            'phone' => '+50999000002',
            'email' => 'collector1@mwenpwop.ht',
            'password' => Hash::make('password'),
            'role' => 'collector',
            'status' => 'active',
            'commune' => 'Port-au-Prince',
            'department' => 'Ouest',
            'is_available' => true,
        ]);

        $collector2 = User::create([
            'name' => 'Marie Claire Collectrice',
            'phone' => '+50999000003',
            'password' => Hash::make('password'),
            'role' => 'collector',
            'status' => 'active',
            'commune' => 'Delmas',
            'department' => 'Ouest',
            'is_available' => true,
        ]);

        // ── Sample Citizens ────────────────────────────────────────────────
        $citizen1 = User::create([
            'name' => 'Pierre Paul Citoyen',
            'phone' => '+50999000010',
            'password' => Hash::make('password'),
            'role' => 'citizen',
            'status' => 'active',
            'commune' => 'Pétionville',
            'department' => 'Ouest',
            'points' => 30,
        ]);

        $citizen2 = User::create([
            'name' => 'Sophie Dupont',
            'phone' => '+50999000011',
            'email' => 'sophie@email.com',
            'password' => Hash::make('password'),
            'role' => 'citizen',
            'status' => 'active',
            'commune' => 'Delmas',
            'department' => 'Ouest',
            'points' => 50,
        ]);

        // ── Sample Collection Requests ─────────────────────────────────────
        CollectionRequest::create([
            'citizen_id' => $citizen1->id,
            'zone_id' => $zone1->id,
            'waste_type' => 'household',
            'priority' => 'normal',
            'status' => 'pending',
            'address' => 'Rue Capois, Port-au-Prince',
            'latitude' => 18.5428,
            'longitude' => -72.3380,
            'notes' => '3 sacs de déchets ménagers',
        ]);

        CollectionRequest::create([
            'citizen_id' => $citizen2->id,
            'collector_id' => $collector1->id,
            'zone_id' => $zone1->id,
            'waste_type' => 'recyclable',
            'priority' => 'high',
            'status' => 'completed',
            'address' => 'Delmas 75, Port-au-Prince',
            'latitude' => 18.5600,
            'longitude' => -72.3100,
            'completed_at' => now()->subHours(3),
            'rating' => 5,
            'review' => 'Service excellent, rapide et efficace !',
        ]);

        // ── Sample Reports ─────────────────────────────────────────────────
        Report::create([
            'reporter_id' => $citizen1->id,
            'zone_id' => $zone1->id,
            'type' => 'illegal_dump',
            'severity' => 'high',
            'status' => 'pending',
            'title' => 'Dépôt sauvage devant l\'école nationale',
            'description' => 'Un grand dépôt de déchets s\'est formé devant l\'école nationale du quartier. Cela représente un danger pour les enfants.',
            'address' => 'École Nationale, Rue des Casernes, Port-au-Prince',
            'latitude' => 18.5420,
            'longitude' => -72.3370,
        ]);

        Report::create([
            'reporter_id' => $citizen2->id,
            'type' => 'blocked_canal',
            'severity' => 'critical',
            'status' => 'pending',
            'title' => 'Canal principal bouché à Delmas 33',
            'description' => 'Le canal principal de Delmas 33 est complètement bouché par des déchets. Risque d\'inondation lors des prochaines pluies.',
            'address' => 'Delmas 33, Canal principal',
            'latitude' => 18.5560,
            'longitude' => -72.3050,
        ]);

        // ── Sample Mission ─────────────────────────────────────────────────
        $mission = Mission::create([
            'created_by' => $admin->id,
            'zone_id' => $zone1->id,
            'title' => 'Nettoyage grand Place Boyer',
            'description' => 'Mission de nettoyage de la Place Boyer et ses alentours. Nous avons besoin de 5 jeunes motivés pour une journée de travail rémunéré.',
            'type' => 'cleaning',
            'status' => 'open',
            'slots' => 5,
            'slots_taken' => 1,
            'payment' => 1500,
            'address' => 'Place Boyer, Pétionville',
            'latitude' => 18.5128,
            'longitude' => -72.2929,
            'starts_at' => now()->addDays(3),
            'ends_at' => now()->addDays(3)->addHours(8),
            'requirements' => ['Bonne condition physique', 'Disponible toute la journée'],
            'equipment_provided' => ['Gants', 'Sacs poubelle', 'Masque'],
        ]);

        MissionApplication::create([
            'mission_id' => $mission->id,
            'user_id' => $collector1->id,
            'status' => 'accepted',
            'motivation' => 'Je veux contribuer au nettoyage de mon quartier.',
            'accepted_at' => now(),
        ]);

        $this->command->info('✅ Base de données initialisée avec succès !');
        $this->command->info('👤 Admin: admin@mwenpwop.ht / Admin@2025');
        $this->command->info('📱 Collector: +50999000002 / password');
        $this->command->info('👤 Citizen: +50999000010 / password');
    }
}
