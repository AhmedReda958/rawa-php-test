import { validateTaskInput } from './task-validation.js';

export function initTaskForm(form, onCreated) {
    const submit = form.querySelector('button[type="submit"]');
    const formError = form.querySelector('#task-form-error');
    const notice = document.querySelector('#task-notice');
    const dialog = document.querySelector('#task-dialog');
    const open = document.querySelector('#open-task-form');
    const close = document.querySelector('#close-task-form');
    const fields = ['title', 'description', 'priority', 'status', 'due_date'];
    let saving = false;
    let noticeTimer;
    form.noValidate = true;

    open.addEventListener('click', () => {
        showErrors({});
        formError.hidden = true;
        dialog.showModal();
    });
    close.addEventListener('click', () => dialog.close());
    dialog.addEventListener('cancel', (event) => {
        if (saving) event.preventDefault();
    });

    function showErrors(errors) {
        for (const name of fields) {
            form.querySelector(`#${name}-error`).textContent = errors[name] || '';
            form.elements[name].setAttribute('aria-invalid', String(Boolean(errors[name])));
        }

        const first = fields.find(name => errors[name]);
        if (first) form.elements[first].focus();
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (saving) return;

        formError.hidden = true;
        notice.hidden = true;
        clearTimeout(noticeTimer);
        const data = new FormData(form);
        const errors = validateTaskInput(Object.fromEntries(data));
        if (form.elements.due_date.validity.badInput) {
            errors.due_date = 'Please enter a complete, valid date.';
        }
        showErrors(errors);
        if (Object.keys(errors).length) return;

        saving = true;
        submit.disabled = true;
        close.disabled = true;
        submit.textContent = 'Saving…';

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();

            if (!response.ok) {
                showErrors(result.errors || {});
                formError.textContent = result.message || 'Unable to save your task.';
                formError.hidden = false;
                return;
            }

            const visible = onCreated(result.task);
            form.reset();
            dialog.close();
            notice.textContent = visible ? 'Task added.' : 'Task added. Change your filters to see it.';
            notice.hidden = false;
            noticeTimer = setTimeout(() => { notice.hidden = true; }, 5000);
        } catch {
            formError.textContent = 'Unable to confirm the save. Check your connection and refresh the list before retrying.';
            formError.hidden = false;
        } finally {
            saving = false;
            submit.disabled = false;
            close.disabled = false;
            submit.textContent = 'Save task';
        }
    });

    open.hidden = false;
}
