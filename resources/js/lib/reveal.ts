type RevealOptions = { delay?: number; direction?: 'left' | 'right' | 'up' };

export function reveal(node: HTMLElement, options: RevealOptions = {}) {
    node.dataset.reveal = options.direction === 'up' || !options.direction ? '' : options.direction;
    node.dataset.revealDelay = String(options.delay ?? 0);

    if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        node.classList.add('is-visible');
        return {};
    }

    const observer = new IntersectionObserver(([entry]) => {
        if (entry.isIntersecting) {
            node.classList.add('is-visible');
            observer.disconnect();
        }
    }, { threshold: 0.12, rootMargin: '0px 0px -48px' });

    observer.observe(node);
    return { destroy: () => observer.disconnect() };
}
