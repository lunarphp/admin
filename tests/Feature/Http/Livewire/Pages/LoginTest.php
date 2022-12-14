<?php

namespace Lunar\Hub\Tests\Feature\Http\Livewire\Pages;

use Lunar\Hub\Tests\TestCase;

class LoginTest extends TestCase
{
    /** @test */
    public function login_page_contains_livewire_component()
    {
        $this->get('/hub/login')->assertSeeLivewire('hub.components.login-form');
    }
}
