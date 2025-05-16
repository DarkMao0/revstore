// Расширение блока
document.addEventListener('DOMContentLoaded', function() {
    const open_list = document.querySelectorAll('.open_list');

    open_list.forEach(function(open_list, index) {
        const filter = document.querySelectorAll('.filter')[index];
        const checkboxes = filter.querySelectorAll('input[type="checkbox"]');

        open_list.addEventListener('click', function() {
            this.classList.toggle('open');
            if (filter.style.maxHeight && filter.style.maxHeight !== '0px') {
                filter.style.maxHeight = '0px';
            }
            else {
                filter.style.maxHeight = 'none';
                const fullHeight = filter.scrollHeight + 'px';
                filter.style.maxHeight = '0px';
                setTimeout(function() {
                    filter.style.maxHeight = fullHeight;
                }, 10);
            }
        });
        const activeCheckbox = Array.from(checkboxes).some(checkbox => checkbox.checked);
        if (activeCheckbox) {
            open_list.classList.add('open');
            filter.style.maxHeight = filter.scrollHeight + 'px';
        }
    });
});
// Окно сортировки
const filterButton = document.querySelector('.sort_con');
const filterContent = document.querySelector('.sort_content');
const sortButton = document.querySelector('.sort');
const sortImage = document.querySelector('.sort svg');

function showFilterContent() {
    filterContent.style.visibility = 'visible';
    filterContent.style.opacity = '1';
    sortButton.style.backgroundColor = '#D40000FF';
    sortImage.style.fill = '#ffffff';
}

function hideFilterContent() {
    filterContent.style.opacity = '0';
    filterContent.style.visibility = 'hidden';
    sortButton.style.backgroundColor = '';
    sortImage.style.fill = '';
}

filterButton.addEventListener('click', function (event) {
    if (filterContent.style.visibility === 'visible') {
        hideFilterContent();
    }
    else {
        showFilterContent();
    }
    event.stopPropagation();
});

document.addEventListener('click', function (event) {
    const target = event.target;

    if (!filterButton.contains(target) && !filterContent.contains(target)) {
        hideFilterContent();
    }
});
filterContent.addEventListener('click', function(event) {
    event.stopPropagation();
});