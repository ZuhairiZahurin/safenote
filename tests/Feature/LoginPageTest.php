<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginPageTest extends TestCase
{
    public function test_the_password_field_offers_a_reveal_button(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertSee('id="togglePassword"', false);
        // type="button" keeps it from submitting the form when activated by key.
        $response->assertSee('type="button"', false);
        $response->assertSee('aria-pressed="false"', false);
        $response->assertSee('has-reveal', false);
    }

    /**
     * The reveal button broke once because the decorative-icon rule also matched
     * the icon nested inside the button, positioning it outside the button's own
     * box and collapsing the button to a 14x10 sliver that nobody could hit.
     */
    public function test_the_stylesheet_keeps_the_reveal_button_clickable(): void
    {
        $css = file_get_contents(public_path('css/safenote.css'));

        $this->assertStringContainsString('.sn-field > .bi {', $css,
            'the leading-icon rule must stay scoped to direct children');
        $this->assertStringNotContainsString('.sn-field .bi {', $css,
            'an unscoped rule would drag the reveal button\'s icon out of the button');

        $reveal = substr($css, strpos($css, '.sn-reveal {'));
        $reveal = substr($reveal, 0, strpos($reveal, '}'));
        $this->assertStringContainsString('width: 34px', $reveal, 'the button needs a real hit area');
        $this->assertStringContainsString('height: 34px', $reveal, 'the button needs a real hit area');
    }
}
