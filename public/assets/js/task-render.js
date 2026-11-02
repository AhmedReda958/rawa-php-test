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
        id: task.id, title: task.title, description: task.description, status: task.status, priority: task.priority,
        due: task.due_date || '', created: task.created_at,
    });

    // Treat user content as text, including titles that contain HTML characters.
    const content = element('div', 'task-content');
    const heading = element('div', 'task-heading');
    heading.append(element('h3', 'task-title', task.title));
    content.append(heading);
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
    const actions = element('div', 'task-actions');
    const edit = element('button', 'edit-task');
    edit.type = 'button';
    edit.setAttribute('aria-label', `Edit ${task.title}`);
    edit.title = 'Edit task';
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('aria-hidden', 'true');
    const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
    path.setAttribute('d', 'm15 5 4 4M4 20l4-1 12-12a2.8 2.8 0 0 0-4-4L4 15z');
    svg.append(path);
    edit.append(svg);
    heading.append(edit);
    actions.append(element('span', 'badge task-status', statusLabels[task.status]));

    const nextStatus = { pending: 'in_progress', in_progress: 'completed' }[task.status];
    if (nextStatus) {
        const change = element('button', 'button-secondary change-status', `Mark as ${statusLabels[nextStatus]}`);
        change.type = 'button';
        change.dataset.status = nextStatus;
        actions.append(change);
    }
    item.append(content, actions);
    return item;
}
