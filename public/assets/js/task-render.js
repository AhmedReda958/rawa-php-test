const statusLabels = { pending: 'Pending', in_progress: 'In Progress', completed: 'Completed' };
const priorityLabels = { low: 'Low', medium: 'Medium', high: 'High' };

function element(tag, className, text) {
    const node = document.createElement(tag);
    node.className = className;
    if (text !== undefined) node.textContent = text;
    return node;
}

export function createTaskElement(task) {
    const item = element('li', `task-item status-${task.status}`);
    Object.assign(item.dataset, {
        id: task.id, title: task.title, status: task.status, priority: task.priority,
        due: task.due_date || '', created: task.created_at,
    });

    // Treat user content as text, including titles that contain HTML characters.
    const content = element('div', 'task-content');
    content.append(element('h3', 'task-title', task.title));
    content.append(element('p', 'task-description', task.description || 'No description'));
    const meta = element('div', 'task-meta');
    meta.append(element('span', `badge priority-${task.priority}`, `${priorityLabels[task.priority]} priority`));
    const due = element('span', 'task-date');

    if (task.due_date) {
        const time = document.createElement('time');
        time.dateTime = task.due_date;
        time.textContent = new Date(`${task.due_date}T00:00:00Z`).toLocaleDateString('en-US', {
            month: 'short', day: 'numeric', year: 'numeric', timeZone: 'UTC',
        });
        due.append('Due: ', time);
    } else {
        due.textContent = 'No due date';
    }

    meta.append(due);
    content.append(meta);
    item.append(content, element('span', 'badge task-status', statusLabels[task.status]));
    return item;
}
