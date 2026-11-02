import { validateTaskInput } from './task-validation.js';
import { hideTaskNotice, showTaskNotice } from './task-notice.js';

export function initTaskForm(form, onSaved) {
    const submit = form.querySelector('button[type="submit"]');
    const formError = form.querySelector('#task-form-error');
    const dialog = document.querySelector('#task-dialog');
    const open = document.querySelector('#open-task-form');
    const close = document.querySelector('#close-task-form');
    const heading = document.querySelector('#task-form-heading');
    const fields = ['title', 'description', 'priority', 'status', 'due_date'];
    let saving = false;
    form.noValidate = true;

    function openForm(task = null) {
        form.reset();
        form.elements.task_id.value = task ? task.id : '';
        heading.textContent = task ? 'Edit task' : 'Add a task';
        submit.textContent = task ? 'Save changes' : 'Save task';

        if (task) {
            for (const name of fields) {
                form.elements[name].value = task[name] ?? '';
            }
        }

        showErrors({});
        formError.hidden = true;
        dialog.showModal();
    }

    open.addEventListener('click', () => openForm());
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
        hideTaskNotice();
        const data = new FormData(form);
        const errors = validateTaskInput(Object.fromEntries(data));
        if (form.elements.due_date.validity.badInput) {
            errors.due_date = 'Please enter a complete, valid date.';
        }
        showErrors(errors);
        if (Object.keys(errors).length) return;

        saving = true;
        const editing = form.elements.task_id.value !== '';
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

            dialog.close();
            const visible = onSaved(result.task);
            form.reset();
            const message = editing ? 'Task updated.' : 'Task added.';
            showTaskNotice(visible ? message : `${message} Change your filters to see it.`);
        } catch {
            formError.textContent = 'Unable to confirm the save. Check your connection and refresh the list before retrying.';
            formError.hidden = false;
        } finally {
            saving = false;
            submit.disabled = false;
            close.disabled = false;
            submit.textContent = editing ? 'Save changes' : 'Save task';
        }
    });

    open.hidden = false;
    return { edit: (task) => openForm(task) };
}
