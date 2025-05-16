// Аватарка
const fileInput = document.querySelector('.user_image');
const imagePreview = document.querySelector('.stock_image');
const defaultImage = '/img/svg/prof.svg';

fileInput.addEventListener('change', function() {
    if (this.files.length > 0) {
        const img = this.files[0];

        if (img.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(showAvatar) {
                imagePreview.src = showAvatar.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(img);
        }
    }
    else {
        imagePreview.src = defaultImage;
        imagePreview.style.display = 'block';
    }
});