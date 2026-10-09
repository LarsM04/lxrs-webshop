<?php

namespace Tests\Feature\Database\Seeders;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    private function beheerderInEnv(?string $email = 'lars@lxrs2004.com', ?string $wachtwoord = 'geheim'): void
    {
        config(['services.lxrs_admin' => ['name' => 'Lars', 'email' => $email, 'password' => $wachtwoord]]);
    }

    public function test_creates_the_admin_from_the_env_settings(): void
    {
        $this->beheerderInEnv();

        $this->seed(AdminSeeder::class);

        $beheerder = User::sole();
        $this->assertSame('Lars', $beheerder->name);
        $this->assertSame('lars@lxrs2004.com', $beheerder->email);
        $this->assertTrue(Hash::check('geheim', $beheerder->password));
    }

    /** @return array<string, array{?string, ?string}> */
    public static function onvolledigeGegevens(): array
    {
        return [
            'geen e-mailadres' => [null, 'geheim'],
            'geen wachtwoord' => ['lars@lxrs2004.com', null],
            'leeg wachtwoord' => ['lars@lxrs2004.com', ''],
        ];
    }

    #[DataProvider('onvolledigeGegevens')]
    public function test_creates_no_admin_without_email_or_password(?string $email, ?string $wachtwoord): void
    {
        $this->beheerderInEnv($email, $wachtwoord);

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_running_again_updates_the_password_without_a_second_account(): void
    {
        $this->beheerderInEnv(wachtwoord: 'oud');
        $this->seed(AdminSeeder::class);

        $this->beheerderInEnv(wachtwoord: 'nieuw');
        $this->seed(AdminSeeder::class);

        $this->assertTrue(Hash::check('nieuw', User::sole()->password));
    }
}
