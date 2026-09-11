<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';

    const countries = [
        { flag: '🇹🇬', name: 'Togo' },
        { flag: '🇧🇯', name: 'Bénin' },
        { flag: '🇨🇮', name: 'Côte d’Ivoire' },
        { flag: '🇧🇫', name: 'Burkina Faso' },
        { flag: '🇨🇲', name: 'Cameroun' },
        { flag: '🇸🇳', name: 'Sénégal' },
        { flag: '🇲🇱', name: 'Mali' },
        { flag: '🇬🇳', name: 'Guinée' },
    ];
</script>

<section id="coverage" class="section coverage-section">
    <div class="page-width coverage-layout">
        <div use:reveal={{ direction: 'left' }}>
            <p class="eyebrow"><span>{__('landing.sections.coverage')}</span></p>
            <h2>{__('landing.coverage.heading')}</h2>
            <p class="intro">{__('landing.coverage.body')}</p>
            <div class="africa-note">
                <div class="africa-note-copy">{__('landing.extra.africa')}</div>
                <div class="africa-note-line" aria-hidden="true"></div>
            </div>
        </div>

        <div class="countries-grid">
            {#each countries as country, index}
                <div class="country-card" use:reveal={{ delay: index % 4 }}>
                    <span class="country-flag" aria-hidden="true">{country.flag}</span>
                    <strong>{country.name}</strong>
                    <span class="gold-dash" aria-hidden="true"></span>
                </div>
            {/each}
        </div>
    </div>
    <div class="ribbon" aria-hidden="true"></div>
</section>
