'use strict';

const loginForm = document.querySelector('#login-form');

if (loginForm) {
    // Keep native field validation available when JavaScript is disabled.
    loginForm.noValidate = true;
    loginForm.addEventListener('submit', (event) => {
        loginForm.elements.email.value = loginForm.elements.email.value.trim();

        if (!loginForm.reportValidity()) {
            event.preventDefault();
        }
    });
}

const taskDashboard = document.querySelector('#task-dashboard');

if (taskDashboard && document.querySelector('#task-list')) {
    const taskList = document.querySelector('#task-list');
    const tasks = Array.from(taskList.querySelectorAll('.task-item'));
    const statusButtons = Array.from(document.querySelectorAll('.status-filter'));
    const search = document.querySelector('#task-search');
    const priorityFilter = document.querySelector('#priority-filter');
    const sort = document.querySelector('#task-sort');
    const results = document.querySelector('#task-results');
    const emptyState = document.querySelector('#task-empty');
    const priorityRank = { high: 3, medium: 2, low: 1 };
    let activeStatus = 'all';

    function updateTasks() {
        const query = search.value.trim().toLowerCase();
        const sorted = [...tasks].sort((first, second) => {
            const a = first.dataset;
            const b = second.dataset;
            const newest = b.created.localeCompare(a.created) || Number(b.id) - Number(a.id);

            switch (sort.value) {
                case 'oldest':
                    return -newest;
                case 'due':
                    // Undated tasks follow dated tasks, with newest first for equal dates.
                    return (a.due || '9999-12-31').localeCompare(b.due || '9999-12-31') || newest;
                case 'priority':
                    return priorityRank[b.priority] - priorityRank[a.priority] || newest;
                case 'title':
                    return a.title.localeCompare(b.title, undefined, { sensitivity: 'base' }) || newest;
                default:
                    return newest;
            }
        });
        let visible = 0;

        for (const task of sorted) {
            const matches = task.dataset.title.toLowerCase().includes(query)
                && (activeStatus === 'all' || task.dataset.status === activeStatus)
                && (priorityFilter.value === 'all' || task.dataset.priority === priorityFilter.value);
            task.hidden = !matches;
            visible += matches ? 1 : 0;
            // Move existing elements rather than rebuilding task content as HTML.
            taskList.append(task);
        }

        results.textContent = `${visible} of ${tasks.length} ${tasks.length === 1 ? 'task' : 'tasks'}`;
        emptyState.hidden = visible !== 0;
        document.querySelector('#empty-heading').textContent = tasks.length ? 'No matching tasks' : 'No tasks yet';
        document.querySelector('#empty-description').textContent = tasks.length
            ? 'Try a different search or reset your filters.'
            : 'Your tasks will appear here.';

        for (const button of statusButtons) {
            button.setAttribute('aria-pressed', String(button.dataset.status === activeStatus));
        }
    }

    for (const button of statusButtons) {
        button.addEventListener('click', () => {
            activeStatus = button.dataset.status;
            updateTasks();
        });
    }

    search.addEventListener('input', updateTasks);
    priorityFilter.addEventListener('change', updateTasks);
    sort.addEventListener('change', updateTasks);
    document.querySelector('#clear-filters').addEventListener('click', () => {
        activeStatus = 'all';
        search.value = '';
        priorityFilter.value = 'all';
        sort.value = 'newest';
        updateTasks();
    });

    document.querySelector('.task-stats').hidden = false;
    document.querySelector('.task-toolbar').hidden = false;
    updateTasks();
}
