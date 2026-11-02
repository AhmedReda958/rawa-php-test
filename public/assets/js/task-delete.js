import { hideTaskNotice, showTaskNotice } from './task-notice.js';

export function initTaskDelete(onDeleted) {
    const dialog = document.querySelector('#delete-task-dialog');
    const form = document.querySelector('#delete-task-form');
    const cancel = document.querySelector('#cancel-delete-task');
    const confirm = document.querySelector('#confirm-delete-task');
    const error = document.querySelector('#delete-task-error');
    const title = document.querySelector('#delete-task-title');
    let selectedItem = null;
    let deleting = false;

    function cancelDeletion() {
        if (deleting) return;
        dialog.close();
        selectedItem?.querySelector('.task-menu-toggle').focus();
        selectedItem = null;
    }

    cancel.addEventListener('click', cancelDeletion);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        cancelDeletion();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!selectedItem || deleting) return;

        deleting = true;
        cancel.disabled = true;
        confirm.disabled = true;
        confirm.textContent = 'Deleting…';
        error.hidden = true;
        hideTaskNotice();
        const id = selectedItem.dataset.id;
        const data = new FormData();
        data.set('action', 'delete');
        data.set('task_id', id);
        data.set('csrf_token', document.querySelector('#task-form').elements.csrf_token.value);

        try {
            const response = await fetch(form.action, {
                method: 'POST', body: data, credentials: 'same-origin',
                headers: { Accept: 'application/json' },
            });
            const result = await response.json();

            if (!response.ok) {
                error.textContent = result.message || 'Unable to delete the task.';
                error.hidden = false;
                return;
            }

            dialog.close();
            selectedItem = null;
            onDeleted(id);
            showTaskNotice('Task deleted.');
        } catch {
            error.textContent = 'Unable to confirm the deletion. Refresh the list before retrying.';
            error.hidden = false;
        } finally {
            deleting = false;
            cancel.disabled = false;
            confirm.disabled = false;
            confirm.textContent = 'Delete';
        }
    });

    return {
        open(item) {
            selectedItem = item;
            title.textContent = item.dataset.title;
            error.hidden = true;
            dialog.showModal();
        },
    };
}
