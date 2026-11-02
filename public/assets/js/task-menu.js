import { initTaskDelete } from './task-delete.js';

export function initTaskMenu(taskList, onEdit, onDeleted) {
    const deletion = initTaskDelete(onDeleted);

    function closeMenus(except = null) {
        for (const menu of taskList.querySelectorAll('.task-menu')) {
            if (menu !== except) menu.open = false;
        }
    }

    taskList.addEventListener('click', (event) => {
        const toggle = event.target.closest('.task-menu-toggle');
        if (toggle) {
            closeMenus(toggle.closest('.task-menu'));
            return;
        }

        const edit = event.target.closest('.edit-task');
        if (edit) {
            closeMenus();
            onEdit(edit.closest('.task-item'));
            return;
        }

        const remove = event.target.closest('.delete-task');
        if (!remove) return;
        closeMenus();
        deletion.open(remove.closest('.task-item'));
    });

    taskList.addEventListener('keydown', (event) => {
        const menu = event.target.closest('.task-menu');
        if (event.key === 'Escape' && menu?.open) {
            event.preventDefault();
            closeMenus();
            menu.querySelector('.task-menu-toggle').focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.task-menu')) closeMenus();
    });

    for (const menu of taskList.querySelectorAll('.task-menu')) menu.hidden = false;
}
