import './availability';
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';

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

const adminSidebar = document.querySelector('[data-admin-sidebar]');
const adminSidebarToggle = document.querySelector('[data-admin-sidebar-toggle]');
const adminMobileToggle = document.querySelector('[data-admin-mobile-toggle]');
const adminMobileOverlay = document.querySelector('[data-admin-mobile-overlay]');

if (adminSidebar && adminSidebarToggle && adminMobileToggle && adminMobileOverlay) {
    const storageKey = 'cancruz-admin-sidebar-collapsed';

    const setCollapsed = (collapsed) => {
        adminSidebar.dataset.collapsed = collapsed ? 'true' : 'false';
        adminSidebarToggle.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        adminSidebarToggle.setAttribute('aria-label', collapsed ? 'Expandir menú' : 'Contraer menú');
    };

    const setMobileOpen = (open) => {
        adminSidebar.dataset.mobileOpen = open ? 'true' : 'false';
        adminMobileOverlay.hidden = !open;
        adminMobileToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        adminMobileToggle.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        document.body.classList.toggle('admin-menu-open', open);
    };

    setCollapsed(window.localStorage.getItem(storageKey) === 'true');
    setMobileOpen(false);

    adminSidebarToggle.addEventListener('click', () => {
        const collapsed = adminSidebar.dataset.collapsed === 'true';
        setCollapsed(!collapsed);
        window.localStorage.setItem(storageKey, String(!collapsed));
    });

    adminMobileToggle.addEventListener('click', () => {
        setMobileOpen(adminSidebar.dataset.mobileOpen !== 'true');
    });

    adminMobileOverlay.addEventListener('click', () => setMobileOpen(false));

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && adminSidebar.dataset.mobileOpen === 'true') {
            setMobileOpen(false);
            adminMobileToggle.focus();
        }
    });

    adminSidebar.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setMobileOpen(false));
    });
}

const adminCalendar = document.querySelector('#admin-calendar');

if (adminCalendar) {
    const drawer = document.querySelector('[data-calendar-drawer]');
    const dialog = drawer.querySelector('[role="dialog"]');
    const drawerTitle = drawer.querySelector('[data-calendar-drawer-title]');
    const drawerContent = drawer.querySelector('[data-calendar-drawer-content]');
    const blockForm = drawer.querySelector('[data-calendar-block-form]');
    const entryInput = blockForm.querySelector('input[name="entry_date"]');
    const outInput = blockForm.querySelector('input[name="out_date"]');
    const token = blockForm.querySelector('input[name="_token"]').value;
    let lastTrigger = null;

    const statusLabels = {
        pending: 'Pendiente',
        confirmed: 'Confirmada',
    };

    const formatDate = (date) => new Intl.DateTimeFormat('es-ES', {
        dateStyle: 'long',
        timeZone: 'UTC',
    }).format(new Date(`${date}T00:00:00Z`));

    const addDetail = (label, value) => {
        const wrapper = document.createElement('div');
        const heading = document.createElement('dt');
        const detail = document.createElement('dd');

        heading.className = 'admin-calendar-detail__label';
        detail.className = 'admin-calendar-detail__value';
        heading.textContent = label;
        detail.textContent = value;
        wrapper.append(heading, detail);
        drawerContent.append(wrapper);
    };

    const addAction = (label, action, method = 'POST', destructive = false) => {
        const form = document.createElement('form');
        const methodInput = document.createElement('input');
        const tokenInput = document.createElement('input');
        const button = document.createElement('button');

        form.method = 'POST';
        form.action = action;
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = token;
        button.className = destructive ? 'button button--danger' : 'button';
        button.type = 'submit';
        button.textContent = label;

        if (destructive) {
            button.addEventListener('click', (event) => {
                if (!window.confirm('¿Confirmar esta acción?')) {
                    event.preventDefault();
                }
            });
        }

        form.append(tokenInput, button);

        if (method !== 'POST') {
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = method;
            form.append(methodInput);
        }

        drawerContent.append(form);
    };

    const openDrawer = (title, trigger) => {
        lastTrigger = trigger ?? document.activeElement;
        drawerTitle.textContent = title;
        dialog.setAttribute('aria-label', title);
        drawer.hidden = false;
        drawer.setAttribute('aria-hidden', 'false');
        drawer.querySelector('[data-calendar-drawer-close]').focus();
    };

    const closeDrawer = () => {
        drawer.hidden = true;
        drawer.setAttribute('aria-hidden', 'true');
        drawerContent.replaceChildren();
        blockForm.hidden = true;
        if (lastTrigger instanceof HTMLElement) {
            lastTrigger.focus();
        }
    };

    const showReservation = (event) => {
        const { extendedProps } = event.event;
        const start = event.event.startStr;
        const end = event.event.endStr;

        drawerContent.replaceChildren();
        blockForm.hidden = true;
        addDetail('Huésped', event.event.title);
        addDetail('Estado', statusLabels[extendedProps.status] ?? extendedProps.status);
        addDetail('Estancia', `${formatDate(start)} - ${formatDate(end)}`);
        addDetail('Email', extendedProps.email);

        if (extendedProps.message) {
            addDetail('Mensaje', extendedProps.message);
        }

        const editLink = document.createElement('a');
        editLink.className = 'button';
        editLink.href = extendedProps.editUrl;
        editLink.textContent = 'Editar reserva';
        drawerContent.append(editLink);

        if (extendedProps.confirmUrl) {
            addAction('Confirmar reserva', extendedProps.confirmUrl);
        }

        addAction('Cancelar reserva', extendedProps.cancelUrl, 'POST', true);
        openDrawer('Detalle de la reserva', event.el);
    };

    const showBlock = (event) => {
        const { extendedProps } = event.event;

        drawerContent.replaceChildren();
        blockForm.hidden = true;
        addDetail('Fechas', `${formatDate(event.event.startStr)} - ${formatDate(event.event.endStr)}`);
        addDetail('Motivo', extendedProps.reason);
        addAction('Eliminar bloqueo', extendedProps.deleteUrl, 'DELETE', true);
        openDrawer('Detalle del bloqueo', event.el);
    };

    const showBlockForm = (dateStr, trigger) => {
        const outDate = new Date(`${dateStr}T00:00:00Z`);
        outDate.setUTCDate(outDate.getUTCDate() + 1);
        const nextDate = outDate.toISOString().slice(0, 10);

        drawerContent.replaceChildren();
        const description = document.createElement('p');
        description.className = 'admin-calendar-drawer__description';
        description.textContent = 'El bloqueo se aplicará a todas las noches entre ambas fechas.';
        drawerContent.append(description);
        blockForm.hidden = false;
        entryInput.value = dateStr;
        outInput.value = nextDate;
        openDrawer('Crear bloqueo', trigger);
    };

    drawer.querySelectorAll('[data-calendar-drawer-close]').forEach((button) => {
        button.addEventListener('click', closeDrawer);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !drawer.hidden) {
            closeDrawer();
        }
    });

    const calendar = new Calendar(adminCalendar, {
        plugins: [dayGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        firstDay: 1,
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth',
        },
        events: adminCalendar.dataset.calendarEventsUrl,
        eventClick: (info) => {
            if (info.event.extendedProps.type === 'block') {
                showBlock(info);
                return;
            }

            showReservation(info);
        },
        dateClick: (info) => showBlockForm(info.dateStr, info.dayEl),
        editable: false,
        selectable: false,
    });

    calendar.render();
}
