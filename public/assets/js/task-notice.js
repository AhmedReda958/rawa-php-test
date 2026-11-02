let noticeTimer;

export function hideTaskNotice() {
    clearTimeout(noticeTimer);
    document.querySelector('#task-notice').hidden = true;
}

export function showTaskNotice(message, error = false) {
    hideTaskNotice();
    const notice = document.querySelector('#task-notice');
    notice.className = error ? 'error' : 'success';
    notice.textContent = message;
    notice.hidden = false;
    if (!error) noticeTimer = setTimeout(() => { notice.hidden = true; }, 5000);
}
