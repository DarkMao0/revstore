// Показать пароль
const passwordInput = document.querySelector('.secure');
const eyeImg = document.querySelector('.eye_image');
function showPassword() {
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeImg.src = '../img/svg/eye_open.svg';
    }
    else {
        passwordInput.type = 'password';
        eyeImg.src = '../img/svg/eye_closed.svg';
    }
}
eyeImg.addEventListener('click', showPassword);
// Защита пароля
const passwordSecurity = document.querySelectorAll('.secure');
passwordSecurity.forEach(element => {
    element.addEventListener('copy', function(nocopy) {
        nocopy.preventDefault();
    });
    element.addEventListener('paste', function(nopaste) {
        nopaste.preventDefault();
    });
});