<?php

use Illuminate\Support\Collection;
use Inertia\Testing\AssertableInertia as Assert;

it('renders the marketing experience as one Inertia page', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('locale', 'en')
            ->where('language', fn (Collection $language): bool => $language->get('landing.nav.home') === 'Home'));
});

it('redirects former marketing pages to their single-page sections', function (string $uri, string $section) {
    $this->get($uri)->assertRedirect('/#'.$section);
})->with([
    'services' => ['/services', 'services'],
    'how it works' => ['/how-it-works', 'how'],
    'security' => ['/security', 'security'],
    'faq' => ['/faq', 'faq'],
    'contact' => ['/contact', 'contact'],
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
    $this->from('/#services')->post('/language/fr')
        ->assertRedirect('/#services')
        ->assertSessionHas('locale', 'fr');

    $this->get('/')
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->where('locale', 'fr')
            ->where('language', fn (Collection $language): bool => $language->get('landing.nav.home') === 'Accueil'
                && $language->get('landing.nav.close') === 'Fermer le menu de navigation'
                && $language->get('landing.hero.title_accent') === 'toute confiance.'
                && $language->get('landing.services.fuel.title') === 'Offres carburant'
                && $language->get('landing.services.featured') === 'Service phare'
                && $language->get('landing.detail.qr.2') === 'La station scanne le code en toute sécurité pour valider l’offre carburant.'
                && $language->get('landing.faq.help_heading') === 'Centre d’aide'
                && $language->get('landing.contact.send') === 'Envoyer le message'
                && $language->get('landing.support.heading') === 'Besoin d’un coup de main ?'
                && $language->get('landing.detail.more') === 'En savoir plus'));
});

it('returns 404 for an unsupported marketing locale', function () {
    $this->post('/language/de')->assertNotFound();
});
