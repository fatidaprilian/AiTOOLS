document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const categoryButtons = document.querySelectorAll('.filter-category-btn');
    const toolsGrid = document.getElementById('ai-tools-grid');
    if (!toolsGrid) return;

    const toolCards = Array.from(toolsGrid.getElementsByClassName('ai-tool-card'));
    let currentSearchTerm = '';
    let currentCategory = 'All';

    function filterAndSearchTools() {
        toolCards.forEach(card => {
            const title = card.querySelector('h3')?.textContent.toLowerCase() || '';
            const category = card.dataset.category;
            const searchMatch = currentSearchTerm === '' || title.includes(currentSearchTerm);
            const categoryMatch = currentCategory === 'All' || category === currentCategory;
            card.style.display = (searchMatch && categoryMatch) ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            currentSearchTerm = event.target.value.toLowerCase();
            filterAndSearchTools();
        });
    }

    categoryButtons.forEach(button => {
        button.addEventListener('click', function() {
            currentCategory = this.textContent.trim();
            categoryButtons.forEach(btn => {
                btn.classList.remove('bg-blue-200', 'dark:bg-blue-900', 'text-blue-700', 'dark:text-blue-200');
                btn.classList.add('bg-white', 'dark:bg-gray-800', 'border', 'border-gray-300', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
            });
            this.classList.add('bg-blue-200', 'dark:bg-blue-900', 'text-blue-700', 'dark:text-blue-200');
            this.classList.remove('bg-white', 'dark:bg-gray-800', 'border', 'border-gray-300', 'dark:border-gray-700', 'text-gray-700', 'dark:text-gray-300');
            filterAndSearchTools();
        });
    });

    filterAndSearchTools();
});