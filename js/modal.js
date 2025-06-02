// JavaScript для открытия и закрытия модального окна
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');
    const closeBtn = document.getElementsByClassName('close')[0];

    if (modal && modalImg && closeBtn) {
        document.querySelectorAll('.review-image').forEach(img => {
            img.onclick = function() {
                modal.style.display = 'block';
                modalImg.src = this.src;
            };
        });

        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        };
    }
});