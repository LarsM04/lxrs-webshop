<?php

namespace Tests\Feature\Http\Controllers\Admin;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'lars@lxrs2004.com';

    private const WACHTWOORD = 'een-goed-wachtwoord';

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create(['email' => self::EMAIL, 'password' => self::WACHTWOORD]);
    }

    /** @return array<string, string> */
    private function goedeGegevens(): array
    {
        return ['email' => self::EMAIL, 'password' => self::WACHTWOORD];
    }

    /** @return array<string, string> */
    private function foutWachtwoord(): array
    {
        return ['email' => self::EMAIL, 'password' => 'fout'];
    }

    public function test_login_page_shows_the_email_and_password_fields(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('type="email"', false)
            ->assertSee('type="password"', false);
    }

    public function test_correct_credentials_log_in_and_redirect_to_the_dashboard(): void
    {
        $this->post(route('admin.login.store'), $this->goedeGegevens())
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    /** @return array<string, array{string, string}> */
    public static function verkeerdeGegevens(): array
    {
        return [
            'fout wachtwoord' => [self::EMAIL, 'fout'],
            'onbekend e-mailadres' => ['iemand@example.com', self::WACHTWOORD],
        ];
    }

    #[DataProvider('verkeerdeGegevens')]
    public function test_wrong_credentials_give_the_same_message_and_do_not_log_in(string $email, string $wachtwoord): void
    {
        $this->post(route('admin.login.store'), ['email' => $email, 'password' => $wachtwoord])
            ->assertSessionHasErrors(['email' => 'Onjuist e-mailadres of wachtwoord.']);

        $this->assertGuest();
    }

    public function test_empty_form_is_rejected(): void
    {
        $this->post(route('admin.login.store'), [])
            ->assertSessionHasErrors(['email', 'password']);

        $this->assertGuest();
    }

    public function test_login_is_blocked_after_five_failed_attempts(): void
    {
        $this->freezeTime();

        foreach (range(1, LoginRequest::MAX_POGINGEN) as $poging) {
            $this->post(route('admin.login.store'), $this->foutWachtwoord());
        }

        $this->post(route('admin.login.store'), $this->goedeGegevens())
            ->assertSessionHasErrors(['email' => 'Te veel pogingen. Probeer het over 60 seconden opnieuw.']);

        $this->assertGuest();
    }

    public function test_login_works_again_a_minute_after_being_blocked(): void
    {
        $this->freezeTime();

        foreach (range(1, LoginRequest::MAX_POGINGEN) as $poging) {
            $this->post(route('admin.login.store'), $this->foutWachtwoord());
        }

        $this->travel(61)->seconds();

        $this->post(route('admin.login.store'), $this->goedeGegevens())
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_successful_login_resets_the_failed_attempts(): void
    {
        foreach (range(1, LoginRequest::MAX_POGINGEN - 1) as $poging) {
            $this->post(route('admin.login.store'), $this->foutWachtwoord());
        }
        $this->post(route('admin.login.store'), $this->goedeGegevens());
        $this->post(route('admin.logout'));

        foreach (range(1, LoginRequest::MAX_POGINGEN - 1) as $poging) {
            $this->post(route('admin.login.store'), $this->foutWachtwoord());
        }

        $this->post(route('admin.login.store'), $this->goedeGegevens())
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_login_gives_the_session_a_new_id(): void
    {
        $this->get(route('admin.login'));
        $oudId = session()->getId();

        $this->withCookie(config('session.cookie'), $oudId)
            ->post(route('admin.login.store'), $this->goedeGegevens());

        $this->assertNotSame($oudId, session()->getId());
    }

    public function test_logged_in_admin_skips_the_login_page(): void
    {
        $this->actingAs(User::sole())
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_logout_logs_out_and_returns_to_the_login_page_with_a_message(): void
    {
        $this->actingAs(User::sole())
            ->followingRedirects()
            ->post(route('admin.logout'))
            ->assertOk()
            ->assertSee('type="password"', false)
            ->assertSee('Je bent uitgelogd.');

        $this->assertGuest();
    }
}
