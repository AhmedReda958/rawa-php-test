import { compareTasks, matchesTask } from './task-utils.js';
import { initTaskForm } from './task-form.js';
import { createTaskElement } from './task-render.js';

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
    let activeStatus = 'all';

    function updateTasks() {
        const query = search.value.trim().toLowerCase();
        const sorted = [...tasks].sort((a, b) => compareTasks(a.dataset, b.dataset, sort.value));
        let visible = 0;

        for (const task of sorted) {
            const matches = matchesTask(task.dataset, {
                query,
                status: activeStatus,
                priority: priorityFilter.value,
            });
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
            const count = button.dataset.status === 'all'
                ? tasks.length
                : tasks.filter(task => task.dataset.status === button.dataset.status).length;
            button.textContent = `${button.dataset.label} (${count})`;
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
    initTaskForm(document.querySelector('#task-form'), (task) => {
        const item = createTaskElement(task);
        tasks.push(item);
        updateTasks();
        return !item.hidden;
    });
    updateTasks();
}
