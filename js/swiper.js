document.addEventListener('DOMContentLoaded', () => {
    // Инициализация основного слайдера (.sw1)
    const swiper1 = new Swiper('.sw1', {
        slidesPerView: 1,
        effect: 'fade',
        speed: 1500,
        loop: true,
        autoplay: {
            delay: 3000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.pag1',
            clickable: true,
        },
    });

    // Инициализация всех слайдеров продуктов (.sw2)
    const swipers2 = document.querySelectorAll('.sw2');
    swipers2.forEach((swiperElement, index) => {
        new Swiper(swiperElement, {
            slidesPerView: 3,
            spaceBetween: 20,
            loop: true,
            centeredSlides: false,
            slidesOffsetBefore: 10,
            slidesOffsetAfter: 10,
            navigation: {
                nextEl: swiperElement.querySelector('.next2'),
                prevEl: swiperElement.querySelector('.prev2'),
            },
            breakpoints: {
                320: {
                    slidesPerView: 1.5,
                    spaceBetween: 10,
                    slidesOffsetBefore: 5,
                    slidesOffsetAfter: 5,
                },
                480: {
                    slidesPerView: 1.5,
                    spaceBetween: 10,
                    slidesOffsetBefore: 5,
                    slidesOffsetAfter: 5,
                },
                768: {
                    slidesPerView: 2,
                    spaceBetween: 15,
                    slidesOffsetBefore: 10,
                    slidesOffsetAfter: 10,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                    slidesOffsetBefore: 10,
                    slidesOffsetAfter: 10,
                },
            },
        });
    });
});