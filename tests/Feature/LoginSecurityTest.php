<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UnitPengolah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $unit = UnitPengolah::firstOrCreate(
            ['kode' => 'SEK'],
            [
                'nama' => 'Sekretariat',
                'kepala_nama' => 'Kepala Sekretariat',
                'lama_pinjam_standar' => 7,
                'lama_pinjam_maks' => 7,
                'aktif' => true,
            ]
        );

        $this->user = User::firstOrCreate(
            ['username' => 'test.security.user'],
            [
                'name' => 'Petugas Keamanan Test',
                'password' => bcrypt('password123'),
                'role' => 'operator',
                'unit_pengolah_id' => $unit->id,
                'aktif' => true,
            ]
        );

        RateLimiter::clear('test.security.user|127.0.0.1');
    }

    public function test_login_page_renders_human_verification_and_no_quick_login(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertSee('Verifikasi Keamanan')
            ->assertSee('human_verification')
            ->assertSee('captchaQuestionBadge')
            ->assertDontSee('Akun Cepat Pengujian:')
            ->assertDontSee('isiAkun(');
    }

    public function test_captcha_refresh_endpoint_returns_json(): void
    {
        $response = $this->getJson(route('captcha.refresh'));

        $response->assertOk()
            ->assertJsonStructure(['success', 'question']);

        $this->assertTrue($response->json('success'));
        $this->assertNotEmpty($response->json('question'));
    }

    public function test_login_fails_with_invalid_captcha_answer(): void
    {
        $this->get('/login'); // Session gets populated with human_answer
        $correctAnswer = session('human_answer');

        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'password123',
            'human_verification' => $correctAnswer + 99, // Wrong answer
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('human_verification');
        $this->assertGuest();
    }

    public function test_login_fails_with_valid_captcha_but_wrong_password(): void
    {
        $this->get('/login');
        $correctAnswer = session('human_answer');

        $response = $this->from('/login')->post('/login', [
            'username' => $this->user->username,
            'password' => 'wrong-password',
            'human_verification' => $correctAnswer,
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_login_succeeds_with_valid_captcha_and_correct_credentials(): void
    {
        $this->get('/login');
        $correctAnswer = session('human_answer');

        $response = $this->post('/login', [
            'username' => $this->user->username,
            'password' => 'password123',
            'human_verification' => $correctAnswer,
        ]);

        $response->assertRedirect(route('dasbor'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_rate_limiter_blocks_after_multiple_failed_attempts(): void
    {
        $throttleKey = 'test.security.user|127.0.0.1';
        RateLimiter::clear($throttleKey);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($throttleKey, 60);
        }

        $response = $this->from('/login')->post('/login', [
            'username' => 'test.security.user',
            'password' => 'password123',
            'human_verification' => 8,
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('username');
        $this->assertStringContainsString('Terlalu banyak percobaan masuk', session('errors')->first('username'));
    }
}
