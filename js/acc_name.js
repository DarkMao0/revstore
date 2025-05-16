// Заглавные буквы
document.getElementById('user_name').addEventListener('input', function(event) {
    const input = event.target;
    const name = input.value.split(' ').map(word => {
        return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
    });
    input.value = name.join(' ');
});