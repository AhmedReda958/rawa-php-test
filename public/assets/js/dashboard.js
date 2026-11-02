import { compareTasks, matchesTask } from './task-utils.js';
import { initTaskForm } from './task-form.js';
import { createTaskElement } from './task-render.js';
import { initTaskStatus } from './task-status.js';
import { initTaskMenu } from './task-menu.js';

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
    function saveTask(task, focusSelector = '.task-menu-toggle') {
        const item = createTaskElement(task);
        const index = tasks.findIndex(existing => existing.dataset.id === String(task.id));

        if (index === -1) {
            tasks.push(item);
        } else {
            tasks[index].replaceWith(item);
            tasks[index] = item;
        }

        updateTasks();

        if (index !== -1 && !document.querySelector('#task-dialog').open) {
            const focusTarget = item.hidden
                ? document.querySelector('#open-task-form')
                : item.querySelector(focusSelector) || item.querySelector('.task-menu-toggle');
            focusTarget.focus();
        }

        return !item.hidden;
    }

    const taskForm = initTaskForm(document.querySelector('#task-form'), saveTask);
    initTaskStatus(taskList, task => saveTask(task, '.change-status'));

    initTaskMenu(taskList, (item) => {
        const task = item.dataset;
        taskForm.edit({
            id: task.id, title: task.title, description: task.description,
            priority: task.priority, status: task.status, due_date: task.due,
        });
    }, (id) => {
        const index = tasks.findIndex(task => task.dataset.id === id);
        if (index === -1) return;
        tasks[index].remove();
        tasks.splice(index, 1);
        updateTasks();
        const focusTarget = taskList.querySelector('.task-item:not([hidden]) .task-menu-toggle')
            || document.querySelector('#open-task-form');
        focusTarget.focus();
    });

    updateTasks();
}
