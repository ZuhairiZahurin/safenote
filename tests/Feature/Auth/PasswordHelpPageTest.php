<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

class PasswordHelpPageTest extends TestCase
{
    public function test_the_help_page_tells_staff_who_to_contact(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertOk();
        $response->assertSee(config('school.support.contact'));
        $response->assertSee(config('school.support.email'));
        $response->assertSee(config('school.support.phone'));
        $response->assertSee('does not email reset links', false);
    }

    public function test_the_page_asks_for_no_email_address(): void
    {
        $response = $this->get('/forgot-password');

        // An address field here would be an account enumeration oracle, and
        // there is nothing to submit it to any more.
        $response->assertDontSee('<form', false);
        $response->assertDontSee('name="email"', false);
    }

    public function test_the_email_reset_endpoints_are_gone(): void
    {
        // The help page still answers GET, so posting an address to it is a
        // method error rather than a missing route; the reset pages are gone.
        $this->post('/forgot-password', ['email' => 'teacher@safenote.test'])->assertMethodNotAllowed();
        $this->get('/reset-password/any-token')->assertNotFound();
        $this->post('/reset-password', [])->assertNotFound();
    }
}
