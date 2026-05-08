document.addEventListener('DOMContentLoaded', () => {
    // Inject Theme Toggle Button if it doesn't exist and we want it injected automatically
    // But it's better to explicitly place it in the HTML for fine-tuning position.
    
    // Create toggle if it doesn't exist in the auth pages
    if (!document.getElementById('btn-theme-toggle') && document.querySelector('.auth-wrapper')) {
        const toggleHtml = `
            <div class="theme-switch-wrapper">
                <button id="btn-theme-toggle" class="btn-theme" title="Alternar Tema">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', toggleHtml);
    }

    const themeToggle = document.getElementById('btn-theme-toggle');
    const htmlEl = document.documentElement;

    // Check saved theme or system preference
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const defaultTheme = savedTheme || (systemPrefersDark ? 'dark' : 'light');
    
    htmlEl.setAttribute('data-theme', defaultTheme);
    updateToggleIcon(defaultTheme);

    if (themeToggle) {
        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlEl.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            htmlEl.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateToggleIcon(newTheme);
        });
    }

    function updateToggleIcon(theme) {
        if (!themeToggle) return;
        if (theme === 'dark') {
            themeToggle.innerHTML = '<i class="fa-solid fa-sun"></i>';
        } else {
            themeToggle.innerHTML = '<i class="fa-solid fa-moon"></i>';
        }
    }
});
