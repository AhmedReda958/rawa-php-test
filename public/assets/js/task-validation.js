export function validateTaskInput(values) {
    const errors = {};

    if (!values.title.trim()) {
        errors.title = 'Title is required.';
    } else if ([...values.title.trim()].length > 255) {
        errors.title = 'Title must be 255 characters or fewer.';
    }

    if ([...values.description.trim()].length > 5000) {
        errors.description = 'Description must be 5,000 characters or fewer.';
    }

    if (!['low', 'medium', 'high'].includes(values.priority)) {
        errors.priority = 'Choose a valid priority.';
    }

    if (!['pending', 'in_progress', 'completed'].includes(values.status)) {
        errors.status = 'Choose a valid status.';
    }

    if (values.due_date) {
        const date = new Date(`${values.due_date}T00:00:00Z`);

        if (!/^[1-9]\d{3}-\d{2}-\d{2}$/.test(values.due_date)
            || Number.isNaN(date.getTime())
            || date.toISOString().slice(0, 10) !== values.due_date) {
            errors.due_date = 'Enter a valid date between years 1000 and 9999.';
        }
    }

    return errors;
}
