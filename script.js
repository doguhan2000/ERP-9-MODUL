document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.module-btn');

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const module = button.getAttribute('data-module');
            if (module) {
                window.location.href = `${module}.html`;
            }
        });
    });
});
