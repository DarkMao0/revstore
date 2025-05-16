// Аватарка
const fileInput = document.querySelector('.user_image');
const imagePreview = document.querySelector('.stock_image');
const userImage = imagePreview.src;

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
        imagePreview.src = userImage;
        imagePreview.style.display = 'block';
    }
});
// Удаление аватарки
document.addEventListener('DOMContentLoaded', function() {
    const deleteAvatar = document.getElementById('delete_avatar');
    const fileInput = document.querySelector('.user_image');
    const imagePreview = document.querySelector('.stock_image');
    const defaultAvatar = '../img/svg/prof.svg';
    const currentAvatar = imagePreview.getAttribute('data-avatar');

    deleteAvatar.addEventListener('change', function() {
        if (deleteAvatar.checked) {
            fileInput.disabled = true;
            fileInput.classList.add('not_allowed');
            imagePreview.src = defaultAvatar;
        }
        else {
            fileInput.disabled = false;
            fileInput.classList.remove('not_allowed');
            imagePreview.src = currentAvatar;
        }
    });
});