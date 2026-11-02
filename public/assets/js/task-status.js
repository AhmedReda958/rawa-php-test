import { hideTaskNotice, showTaskNotice } from './task-notice.js';

export function initTaskStatus(taskList, onSaved) {
    const form = document.querySelector('#task-form');
    const saving = new Set();

    taskList.addEventListener('click', async (event) => {
        const choice = event.target.closest('.change-status');
        if (!choice) return;
        const item = choice.closest('.task-item');
        const id = item.dataset.id;
        if (saving.has(id)) return;

        saving.add(id);
        hideTaskNotice();
        const buttons = item.querySelectorAll('button');
        for (const button of buttons) button.disabled = true;
        const data = new FormData();
        data.set('action', 'status');
        data.set('task_id', id);
        data.set('status', choice.dataset.status);
        data.set('csrf_token', form.elements.csrf_token.value);

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();

            if (!response.ok) {
                showTaskNotice(result.message || 'Unable to change the status.', true);
                return;
            }

            const visible = onSaved(result.task);
            showTaskNotice(visible ? 'Status updated.' : 'Status updated. Change your filters to see this task.');
        } catch {
            showTaskNotice('Unable to confirm the update. Refresh the list before retrying.', true);
        } finally {
            saving.delete(id);
            for (const button of buttons) button.disabled = false;
        }
    });

    for (const button of taskList.querySelectorAll('.change-status')) button.hidden = false;
}
