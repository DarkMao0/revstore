const header = document.querySelector('.header');
const backToTop = document.querySelector('.back-to-top');

window.addEventListener('scroll', function() {
    if (window.scrollY > 0) {
        header.classList.add('header_shadow');
        backToTop.classList.add('show_button');
    }
    else {
        header.classList.remove('header_shadow');
        backToTop.classList.remove('show_button');
    }
});

backToTop.addEventListener('click', function() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});