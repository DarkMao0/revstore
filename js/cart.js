// Кастомный input number
document.addEventListener('DOMContentLoaded', function() {
    const quantityField = document.querySelectorAll('.quantity_changer');

    quantityField.forEach(function(changer) {
        const decreaseButton = changer.querySelector('button:first-of-type');
        const increaseButton = changer.querySelector('button:last-of-type');
        const quantityInput = changer.querySelector('.quantity_input');

        decreaseButton.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value, 10);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
            }
        });

        increaseButton.addEventListener('click', function() {
            let currentValue = parseInt(quantityInput.value, 10);
            quantityInput.value = currentValue + 1;
        });
    });
});