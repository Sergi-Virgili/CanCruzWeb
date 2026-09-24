import './availability';

const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileMenu = document.querySelector('[data-mobile-menu]');

if (menuToggle && mobileMenu) {
    const setOpen = (open) => {
        mobileMenu.hidden = !open;
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuToggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        document.body.classList.toggle('menu-open', open);
    };

    menuToggle.addEventListener('click', () => {
        const open = menuToggle.getAttribute('aria-expanded') === 'true';
        setOpen(!open);

        if (!open) {
            mobileMenu.querySelector('a')?.focus();
        }
    });

    mobileMenu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const wasOpen = menuToggle.getAttribute('aria-expanded') === 'true';
            setOpen(false);

            if (wasOpen) {
                menuToggle.focus();
            }
        }
    });
}
