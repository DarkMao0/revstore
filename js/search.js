document.addEventListener('DOMContentLoaded', () => {
    const searchIcon = document.querySelector('.search_icon');
    const searchBar = document.querySelector('.search_bar');
    const searchPanel = document.querySelector('.search_panel');
    const searchField = searchPanel.querySelector('.search_field');
    const clearSearch = searchPanel.querySelector('.clear_search');
    const closeSearch = searchPanel.querySelector('.close_search');

    if (searchIcon && searchPanel && searchBar) {
        // Раскрытие панели поиска при клике на иконку
        searchIcon.addEventListener('click', () => {
            searchPanel.classList.add('active');
            searchBar.classList.add('hidden');
            searchField.focus();
        });

        // Закрытие панели при клике на крестик
        if (closeSearch) {
            closeSearch.addEventListener('click', () => {
                searchPanel.classList.remove('active');
                searchBar.classList.remove('hidden');
                searchField.value = '';
                clearSearch.style.display = 'none';
            });
        }

        // Закрытие панели при клике вне панели
        document.addEventListener('click', (e) => {
            if (!searchPanel.contains(e.target) && !searchIcon.contains(e.target)) {
                searchPanel.classList.remove('active');
                searchBar.classList.remove('hidden');
                searchField.value = '';
                clearSearch.style.display = 'none';
            }
        });

        // Закрытие панели при отправке формы
        searchPanel.querySelector('form').addEventListener('submit', () => {
            searchPanel.classList.remove('active');
            searchBar.classList.remove('hidden');
        });
    }

    if (searchField && clearSearch) {
        // Показ/скрытие кнопки очистки
        searchField.addEventListener('input', () => {
            clearSearch.style.display = searchField.value ? 'flex' : 'none';
        });

        // Очистка поля при клике на кнопку
        clearSearch.addEventListener('click', () => {
            searchField.value = '';
            clearSearch.style.display = 'none';
            searchField.focus();
        });

        // Изначально проверяем, есть ли текст в поле
        clearSearch.style.display = searchField.value ? 'flex' : 'none';
    }
});