/**
 * assets/js/toast.js
 * Lightweight toast notification system.
 * Usage: showToast('success', 'Task added!');
 */

function ensureToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }
    return container;
}

const TOAST_ICONS = {
    success: 'fa-circle-check',
    error: 'fa-circle-exclamation',
    info: 'fa-circle-info',
    warning: 'fa-triangle-exclamation',
};

function showToast(type, message, duration = 4000) {
    const container = ensureToastContainer();

    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <i class="fa-solid ${TOAST_ICONS[type] || TOAST_ICONS.info}"></i>
        <span>${message}</span>
    `;

    container.appendChild(toast);

    setTimeout(() => {
        toast.classList.add('hide');
        toast.addEventListener('animationend', () => toast.remove());
    }, duration);
}

// On page load, render any server-side flash messages
// that were embedded in a hidden JSON script tag (see includes/footer.php)
document.addEventListener('DOMContentLoaded', () => {
    const flashData = document.getElementById('flash-data');
    if (flashData) {
        try {
            const flashes = JSON.parse(flashData.textContent);
            flashes.forEach(f => showToast(f.type, f.message));
        } catch (e) {
            console.error('Could not parse flash messages', e);
        }
    }
});
