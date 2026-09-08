<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Un visitante sin sesión debe ser enviado al login.
     */
    public function test_root_redirects_to_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }
}
