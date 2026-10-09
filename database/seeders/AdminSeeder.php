<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Het beheerdersaccount voor /admin, met de gegevens uit .env.
     *
     * Staan ADMIN_EMAIL of ADMIN_PASSWORD niet ingesteld, dan komt er geen
     * account: liever niemand dan iedereen met een standaardwachtwoord.
     *
     * Los te draaien met db:seed --class=AdminSeeder. Bestaat het account al,
     * dan krijgt het de naam en het wachtwoord uit .env, zonder dubbel account.
     */
    public function run(): void
    {
        $admin = config('services.lxrs_admin');

        if (blank($admin['email']) || blank($admin['password'])) {
            $this->command?->warn('Geen beheerder aangemaakt: zet ADMIN_EMAIL en ADMIN_PASSWORD in .env.');

            return;
        }

        // Het wachtwoord wordt gehasht door de 'hashed'-cast op User.
        User::updateOrCreate(
            ['email' => $admin['email']],
            ['name' => $admin['name'], 'password' => $admin['password']],
        );
    }
}
