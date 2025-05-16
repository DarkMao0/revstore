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

const swiper2 = new Swiper('.sw2', {
    slidesPerView: 4,
    loop: true,
    navigation: {
      nextEl: '.next2',
      prevEl: '.prev2',
    },
});