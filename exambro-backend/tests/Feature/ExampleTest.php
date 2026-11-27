<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/portal');
    }

    public function test_portal_returns_successful_response_without_admin_login_link(): void
    {
        $response = $this->get('/portal');

        $response->assertStatus(200);
        $response->assertDontSee('Login Guru / Admin');
    }
}
