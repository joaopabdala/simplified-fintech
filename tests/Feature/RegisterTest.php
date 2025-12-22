<?php

namespace Tests\Feature;

use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use function PHPUnit\Framework\assertTrue;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_user_with_wallet(): void
    {
        $userData = [
            "first_name" => "nome",
            "last_name" => "sobrenome",
            "email" => "teste@email.com",
            "password" => "321654987",
            "password_confirmation" => "321654987",
            "document" => "321165498722",
            "user_type" => "common"
        ];

        $response = $this->post('api/register', $userData);
        $response->assertStatus(201);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $response->json('user.id')
        ]);
    }
}
