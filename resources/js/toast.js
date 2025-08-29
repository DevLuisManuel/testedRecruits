import Toastify from 'toastify-js';
import 'toastify-js/src/toastify.css';

const BASE = {
    duration: 3500,
    gravity: 'top',
    position: 'right',
    close: true,
    stopOnFocus: true,
};

const palette = {
    success: 'linear-gradient(135deg, #16a34a, #22c55e)',
    error: 'linear-gradient(135deg, #dc2626, #ef4444)',
    warning: 'linear-gradient(135deg, #d97706, #f59e0b)',
    info: 'linear-gradient(135deg, #2563eb, #3b82f6)',
    neutral: 'linear-gradient(135deg, #374151, #6b7280)',
};

window.toast = (message, {type = 'neutral', ...opts} = {}) => {
    Toastify({
        ...BASE,
        text: message,
        style: {background: palette[type] ?? palette.neutral},
        ...opts,
    }).showToast();
};

// Here listen the events from the livewire component
window.addEventListener('toast', (e) => {
    const {message, type = 'neutral', ...opts} = e.detail ?? {};
    window.toast(message, {type, ...opts});
});
