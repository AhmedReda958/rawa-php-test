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
