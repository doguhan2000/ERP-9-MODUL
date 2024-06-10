document.addEventListener('DOMContentLoaded', () => {
    const menuToggle = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('main-content');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('collapsed');
    });

    sidebar.addEventListener('mouseenter', () => {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
    });

    sidebar.addEventListener('mouseleave', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
        }
    });
});
