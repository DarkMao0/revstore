document.addEventListener('DOMContentLoaded', () => {
    const bars = document.querySelectorAll('.rating-bar');
    const ratingInput = document.querySelector('#rating');
    const ratingLabel = document.querySelector('#rating-label');

    bars.forEach(bar => {
        bar.addEventListener('click', () => {
            const value = bar.getAttribute('data-value');
            const rank = bar.getAttribute('data-rank');
            
            // Удаляем активные классы со всех полос
            bars.forEach(b => b.classList.remove('active-S', 'active-A', 'active-B', 'active-C', 'active-D'));
            
            // Добавляем активный класс для выбранных полос (от D до выбранного ранга)
            bars.forEach(b => {
                if (b.getAttribute('data-value') <= value) {
                    b.classList.add(`active-${rank}`);
                }
            });
            
            // Обновляем скрытое поле и метку
            ratingInput.value = value;
            ratingLabel.textContent = `Ранг: ${rank}`;
            ratingLabel.classList.add('show');
        });
    });
});