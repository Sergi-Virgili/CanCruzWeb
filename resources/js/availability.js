import { Litepicker } from 'litepicker';
import 'litepicker/dist/css/litepicker.css';

function formatLocalDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function lockNights(occupied) {
    return occupied
        .map(({ entry, out }) => {
            const start = new Date(`${entry}T00:00:00`);
            const end = new Date(`${out}T00:00:00`);
            end.setDate(end.getDate() - 1);

            return end >= start ? [formatLocalDate(start), formatLocalDate(end)] : null;
        })
        .filter(Boolean);
}

function updateSummary(entryInput, outInput, summary) {
    const entry = new Date(`${entryInput.value}T00:00:00`);
    const out = new Date(`${outInput.value}T00:00:00`);
    const nights = Math.round((out - entry) / 86400000);

    if (!entryInput.value || !outInput.value || nights <= 0) {
        summary.textContent = 'Selecciona una entrada y una salida.';

        return false;
    }

    const formatDate = (value) => value.split('-').reverse().join('/');
    summary.textContent = `Entrada: ${formatDate(entryInput.value)} · Salida: ${formatDate(outInput.value)} · ${nights} ${nights === 1 ? 'noche' : 'noches'}`;

    return true;
}

async function initializeAvailabilityCalendars() {
    const forms = document.querySelectorAll('[data-availability-calendar]');

    if (forms.length === 0) {
        return;
    }

    const today = new Date();
    const horizon = new Date();
    horizon.setFullYear(horizon.getFullYear() + 1);

    const pickers = [];

    forms.forEach((form) => {
        const entryInput = form.querySelector('[name="entry_date"]');
        const outInput = form.querySelector('[name="out_date"]');
        const status = form.querySelector('[data-availability-status]');
        const summary = form.querySelector('[data-availability-summary]');
        const calendarMount = form.querySelector('[data-calendar-mount]');
        const dateStep = form.querySelector('[data-date-step]');
        const contactStep = form.querySelector('[data-contact-step]');
        const continueButton = form.querySelector('[data-booking-continue]');
        const backButton = form.querySelector('[data-booking-back]');
        const desktopCalendar = window.matchMedia('(min-width: 900px)');

        if (!entryInput || !outInput || !status || !summary) {
            return;
        }

        const monthCount = () => (desktopCalendar.matches ? 2 : 1);

        const picker = new Litepicker({
            element: entryInput,
            elementEnd: outInput,
            parentEl: calendarMount,
            singleMode: false,
            format: 'YYYY-MM-DD',
            lang: 'es-ES',
            minDate: formatLocalDate(today),
            maxDate: formatLocalDate(horizon),
            numberOfMonths: calendarMount ? monthCount() : 1,
            numberOfColumns: calendarMount ? monthCount() : 1,
            inlineMode: Boolean(calendarMount),
            lockDays: [],
            disallowLockDaysInRange: true,
            keyboardNavigation: true,
        });

        entryInput.type = 'text';
        outInput.type = 'text';
        entryInput.inputMode = 'none';
        outInput.inputMode = 'none';
        const syncSelection = () => {
            const hasValidRange = updateSummary(entryInput, outInput, summary);

            if (continueButton) {
                continueButton.disabled = !hasValidRange;
            }
        };

        ['change', 'input'].forEach((eventName) => {
            entryInput.addEventListener(eventName, syncSelection);
            outInput.addEventListener(eventName, syncSelection);
        });
        picker.on('selected', syncSelection);
        syncSelection();

        if (calendarMount) {
            desktopCalendar.addEventListener('change', () => {
                picker.setOptions({
                    numberOfMonths: monthCount(),
                    numberOfColumns: monthCount(),
                });
            });
        }

        if (form.hasAttribute('data-progressive-booking') && dateStep && contactStep && continueButton) {
            const showContactStep = () => {
                dateStep.hidden = true;
                contactStep.hidden = false;
                form.querySelector('[name="name"]')?.focus();
            };
            const showDateStep = () => {
                dateStep.hidden = false;
                contactStep.hidden = true;
                entryInput.focus();
            };

            continueButton.hidden = false;
            continueButton.addEventListener('click', showContactStep);
            backButton?.addEventListener('click', showDateStep);

            if (form.dataset.startStep === 'contact') {
                const dateError = dateStep.querySelector('.booking-error');
                if (dateError) {
                    showDateStep();
                } else {
                    showContactStep();
                }
            } else {
                contactStep.hidden = true;
            }
        }

        status.textContent = 'Cargando disponibilidad...';
        pickers.push({ picker, status });
    });

    if (pickers.length === 0) {
        return;
    }

    try {
        const response = await fetch(forms[0].dataset.availabilityUrl, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const data = await response.json();
            const lockedNights = lockNights(data.occupied ?? []);

            pickers.forEach(({ picker, status }) => {
                picker.setLockDays(lockedNights);
                status.textContent = 'Calendario actualizado.';
            });
        } else {
            pickers.forEach(({ status }) => {
                status.textContent = 'No se pudo cargar la disponibilidad; las fechas se verificarán al enviar.';
            });
        }
    } catch {
        pickers.forEach(({ status }) => {
            status.textContent = 'No se pudo cargar la disponibilidad; las fechas se verificarán al enviar.';
        });
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAvailabilityCalendars);
} else {
    initializeAvailabilityCalendars();
}
