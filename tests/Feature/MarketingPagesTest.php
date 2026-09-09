<?php

use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

it('renders each marketing page as a separate Inertia page', function (string $uri, string $component) {
    $this->get($uri)
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component($component)
            ->where('locale', 'en')
            ->where('language', fn (Collection $language): bool => $language->get('landing.nav.home') === 'Home'));
})->with([
    'home' => ['/', 'Home'],
    'services' => ['/services', 'Services'],
    'how it works' => ['/how-it-works', 'HowItWorks'],
    'security' => ['/security', 'Security'],
    'faq' => ['/faq', 'Faq'],
    'contact' => ['/contact', 'Contact'],
]);

it('renders document attributes in the Blade root for hydration', function () {
    $this->get('/')
        ->assertSee('<html lang="en" data-theme="onways-light">', false);
});

it('uses the configured application locale when no language is saved', function () {
    config()->set('app.locale', 'fr');

    $this->withHeader('Accept-Language', 'en-US')->get('/')
        ->assertInertia(fn (Assert $page) => $page
            ->where('locale', 'fr')
            ->where('language', fn (Collection $language): bool => $language->get('landing.nav.home') === 'Accueil'));
});

it('persists French and shares Laravel translations with subsequent pages', function () {
    $this->from('/services')->post('/language/fr')
        ->assertRedirect('/services')
        ->assertSessionHas('locale', 'fr');

    $this->get('/services')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Services')
            ->where('locale', 'fr')
            ->where('language', fn (Collection $language): bool => $language->get('landing.nav.home') === 'Accueil'
                && $language->get('landing.services.fuel.title') === 'Bons de carburant'));
});

it('returns 404 for an unsupported marketing locale', function () {
    $this->post('/language/de')->assertNotFound();
});
