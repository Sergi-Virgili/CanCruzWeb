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

        if (!entryInput || !outInput) {
            return;
        }

        pickers.push(new Litepicker({
            element: entryInput,
            elementEnd: outInput,
            singleMode: false,
            format: 'YYYY-MM-DD',
            lang: 'es-ES',
            minDate: formatLocalDate(today),
            maxDate: formatLocalDate(horizon),
            numberOfMonths: 1,
            lockDays: [],
            disallowLockDaysInRange: true,
        }));
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

            pickers.forEach((picker) => picker.setLockDays(lockedNights));
        }
    } catch (error) {
        // Leave the calendar usable without locked nights when availability cannot be loaded.
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeAvailabilityCalendars);
} else {
    initializeAvailabilityCalendars();
}
