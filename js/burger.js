document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger');
    const navContainer = document.querySelector('.nav_container');
    const navSections = document.querySelector('.nav_sections');

    if (burger && navContainer && navSections) {
        burger.addEventListener('click', () => {
            // Переключаем класс active для открытия/закрытия меню
            navContainer.classList.toggle('active');
            navSections.classList.toggle('active');
            burger.classList.toggle('active');

            // Логи для отладки
            console.log('Burger clicked, menu toggled');
        });

        // Закрытие меню при клике на ссылку внутри nav_sections
        const navLinks = navSections.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navContainer.classList.remove('active');
                navSections.classList.remove('active');
                burger.classList.remove('active');
            });
        });
    } else {
        console.log('Burger menu elements not found');
    }
});