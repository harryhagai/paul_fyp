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
    public function test_the_homepage_redirects_to_the_shop(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('shop'));
    }
}
