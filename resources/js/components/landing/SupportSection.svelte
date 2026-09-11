<script lang="ts">
    import { __ } from '../../lib/i18n';
    import { reveal } from '../../lib/reveal';
    import LandingIcon from './LandingIcon.svelte';

    const subjects = ['product', 'partnership', 'transaction'];

    let fullName = $state('');
    let email = $state('');
    let subject = $state('');
    let message = $state('');

    function sendMessage(event: SubmitEvent) {
        event.preventDefault();

        const selectedSubject = subject ? __(`landing.contact.subjects.${subject}`) : __('landing.contact.message_subject');
        const body = [
            `${__('landing.contact.name')}: ${fullName}`,
            `${__('landing.contact.email')}: ${email}`,
            '',
            message,
        ].join('\n');

        window.location.href = `mailto:contact@onwayspay.com?subject=${encodeURIComponent(selectedSubject)}&body=${encodeURIComponent(body)}`;
    }
</script>

<section id="contact" class="section contact-showcase dotted">
    <span class="contact-orbit contact-orbit-left" aria-hidden="true"></span>
    <span class="contact-orbit contact-orbit-right" aria-hidden="true"></span>

    <div class="page-width">
        <header class="contact-heading" use:reveal>
            <p class="contact-kicker"><span></span>{__('landing.contact.eyebrow')}<span></span></p>
            <h2>{__('landing.contact.heading')}</h2>
            <p>{__('landing.contact.intro')}</p>
        </header>

        <div class="contact-layout">
            <aside class="contact-info-card" use:reveal={{ direction: 'left' }}>
                <div class="contact-art" aria-hidden="true">
                    <span class="contact-headset">
                        <span class="contact-chat">•••</span>
                    </span>
                    <span class="contact-art-message">•••</span>
                    <span class="contact-art-ring"></span>
                </div>

                <div class="contact-info-copy">
                    <h3>{__('landing.contact.discuss')}</h3>
                    <p>{__('landing.contact.discuss_body')}</p>
                    <div class="contact-topics">
                        {#each subjects as item}
                            <span>{__(`landing.contact.subjects.${item}`)}</span>
                        {/each}
                    </div>
                    <a class="contact-info-row" href="mailto:contact@onwayspay.com">
                        <span><LandingIcon name="mail" size={23} /></span>
                        <span><strong>{__('landing.contact.support')}</strong><small>contact@onwayspay.com</small></span>
                    </a>
                    <a class="contact-info-row" href="https://wa.me/2250509635034" target="_blank" rel="noopener noreferrer">
                        <span><LandingIcon name="clock" size={23} /></span>
                        <span><strong>WhatsApp</strong><small>+225 05 09 63 50 34</small></span>
                    </a>
                </div>
            </aside>

            <form class="contact-form-card" onsubmit={sendMessage} use:reveal={{ direction: 'right' }}>
                <div class="contact-form-title">
                    <span><LandingIcon name="receipt" size={25} /></span>
                    <h3>{__('landing.contact.form_heading')}</h3>
                </div>

                <label for="contact-name">{__('landing.contact.name')}</label>
                <div class="contact-control"><span><LandingIcon name="user" size={19} /></span><input id="contact-name" bind:value={fullName} autocomplete="name" required placeholder={__('landing.contact.name_placeholder')} /></div>

                <label for="contact-email">{__('landing.contact.email')}</label>
                <div class="contact-control"><span><LandingIcon name="mail" size={19} /></span><input id="contact-email" type="email" bind:value={email} autocomplete="email" required placeholder={__('landing.contact.email_placeholder')} /></div>

                <label for="contact-subject">{__('landing.contact.subject')}</label>
                <div class="contact-control"><span><LandingIcon name="list" size={19} /></span><select id="contact-subject" bind:value={subject} required><option value="" disabled>{__('landing.contact.subject_placeholder')}</option>{#each subjects as item}<option value={item}>{__(`landing.contact.subjects.${item}`)}</option>{/each}</select></div>

                <label for="contact-message">{__('landing.contact.message')}</label>
                <div class="contact-control contact-message-control"><span><LandingIcon name="pencil" size={19} /></span><textarea id="contact-message" bind:value={message} required rows="4" placeholder={__('landing.contact.message_placeholder')}></textarea></div>

                <p class="contact-privacy"><span><LandingIcon name="lock" size={15} /></span>{__('landing.contact.privacy')}</p>
                <button type="submit" class="contact-submit"><span><LandingIcon name="send" size={19} /></span>{__('landing.contact.send')}</button>
            </form>
        </div>
    </div>
</section>
