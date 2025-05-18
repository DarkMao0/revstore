let progress = 0;
const segments = [
    document.getElementById('segment1'),
    document.getElementById('segment2'),
    document.getElementById('segment3'),
    document.getElementById('segment4'),
    document.getElementById('segment5')
];
const loadingText = document.getElementById('loadingText');
const loader = document.getElementById('loader');

function updateLoadingBar() {
    const segmentCount = Math.ceil(progress / 20);
    segments.forEach((segment, index) => {
        if (index < segmentCount) {
            segment.classList.remove('empty');
        } else {
            segment.classList.add('empty');
        }
    });
    loadingText.textContent = `LOADING: ${progress}%`;
}

function simulateLoading() {
    loader.classList.add('active');
    let interval = setInterval(() => {
        if (progress < 100) {
            progress += 1;
            updateLoadingBar();
        } else {
            clearInterval(interval);
            loader.classList.add('hidden');
            setTimeout(() => {
                loader.style.display = 'none';
            }, 200); // Ждём 1 секунду (длительность анимации slice)
        }
    }, 15);
}

window.addEventListener('load', simulateLoading);