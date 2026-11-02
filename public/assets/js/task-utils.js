const priorityRank = { high: 3, medium: 2, low: 1 };

export function compareTasks(first, second, sortBy) {
    const newest = second.created.localeCompare(first.created) || Number(second.id) - Number(first.id);

    switch (sortBy) {
        case 'oldest':
            return -newest;
        case 'due':
            // Keep tasks without a due date at the end.
            return (first.due || '9999-12-31').localeCompare(second.due || '9999-12-31') || newest;
        case 'priority':
            return priorityRank[second.priority] - priorityRank[first.priority] || newest;
        case 'title':
            return first.title.localeCompare(second.title, undefined, { sensitivity: 'base' }) || newest;
        default:
            return newest;
    }
}

export function matchesTask(task, filters) {
    return task.title.toLowerCase().includes(filters.query)
        && (filters.status === 'all' || task.status === filters.status)
        && (filters.priority === 'all' || task.priority === filters.priority);
}
