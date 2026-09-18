const menuToggle = document.getElementById('menu-toggle');
const sidebar = document.getElementById('sidebar');

if (menuToggle && sidebar) {
    const setOpen = (open) => {
        sidebar.classList.toggle('translate-x-0', open);
        sidebar.classList.toggle('-translate-x-full', !open);
        menuToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    menuToggle.addEventListener('click', () => {
        setOpen(!sidebar.classList.contains('translate-x-0'));
    });

    sidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
}
