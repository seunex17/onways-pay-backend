<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingIcon from './LandingIcon.svelte';

    const questions = [
        { number: 1, icon: 'bank' },
        { number: 2, icon: 'wallet' },
        { number: 3, icon: 'phone' },
        { number: 4, icon: 'globe' },
        { number: 5, icon: 'arrow-left-right' },
        { number: 6, icon: 'fuel' },
        { number: 7, icon: 'clock' },
    ] as const;

    const socialLinks = [
        { name: 'WhatsApp', href: 'https://wa.me/2250509635034' },
        { name: 'Instagram', href: 'https://www.instagram.com/onwayspay/' },
        { name: 'TikTok', href: 'https://www.tiktok.com/@onwayspay' },
        { name: 'Facebook', href: 'https://www.facebook.com/OnwaysPay' },
    ];

    let activeQuestion = $state<number | null>(1);

    function toggleQuestion(number: number) {
        activeQuestion = activeQuestion === number ? null : number;
    }
</script>

<section id="faq" class="section faq-showcase dotted">
    <span class="faq-orbit faq-orbit-left" aria-hidden="true"></span>
    <span class="faq-orbit faq-orbit-right" aria-hidden="true"></span>

    <div class="page-width">
        <header class="faq-heading" use:reveal>
            <p class="faq-kicker"><span></span>FAQ<span></span></p>
            <h2>{__('landing.faq.heading')}</h2>
            <p>{__('landing.faq.intro')}</p>
        </header>

        <div class="faq-layout">
            <aside class="faq-help-card" use:reveal={{ direction: 'left' }}>
                <div class="faq-help-art" aria-hidden="true">
                    <span class="faq-question-mark">?</span>
                    <span class="faq-message faq-message-one">•••</span>
                    <span class="faq-message faq-message-two">•••</span>
                    <span class="faq-help-ring"></span>
                </div>
                <div class="faq-help-copy">
                    <h3>{__('landing.faq.help_heading')}</h3>
                    <p>{__('landing.faq.help_body')}</p>
                    <a class="faq-contact-button" href="mailto:contact@onwayspay.com">
                        <span class="faq-contact-icon"><LandingIcon name="headphones" size={25} /></span>
                        <span><small>{__('landing.faq.need_help')}</small><strong>{__('landing.support.contact')}</strong></span>
                        <span><LandingIcon name="arrow-right" size={19} /></span>
                    </a>
                    <div class="faq-socials" aria-label={__('landing.faq.social_label')}>
                        {#each socialLinks as social}
                            <a href={social.href} target="_blank" rel="noopener noreferrer">{social.name}</a>
                        {/each}
                    </div>
                </div>
            </aside>

            <div class="faq-list" use:reveal={{ direction: 'right' }}>
                {#each questions as question}
                    <article class:faq-item-open={activeQuestion === question.number} class="faq-item">
                        <button
                            type="button"
                            class="faq-question"
                            onclick={() => toggleQuestion(question.number)}
                            aria-expanded={activeQuestion === question.number}
                            aria-controls={`faq-answer-${question.number}`}
                        >
                            <span class="faq-item-icon"><LandingIcon name={question.icon} size={24} /></span>
                            <span>{__(`landing.faq.q${question.number}`)}</span>
                            <span class="faq-chevron" aria-hidden="true"></span>
                        </button>
                        {#if activeQuestion === question.number}
                            <div id={`faq-answer-${question.number}`} class="faq-answer">
                                <p>{__(`landing.faq.a${question.number}`)}</p>
                            </div>
                        {/if}
                    </article>
                {/each}
            </div>
        </div>
    </div>
</section>
