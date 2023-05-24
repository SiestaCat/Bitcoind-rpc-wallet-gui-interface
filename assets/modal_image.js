//Credits: Yong Wang https://stackoverflow.com/a/61511955/17875459
const waitForElm = function(selector) {
    return new Promise(resolve => {
        if (document.querySelector(selector)) {
            return resolve(document.querySelector(selector));
        }

        const observer = new MutationObserver(mutations => {
            if (document.querySelector(selector)) {
                resolve(document.querySelector(selector));
                observer.disconnect();
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
}

//Credits: Siniša Grubor https://codepen.io/sinisag/pen/vPEajE
waitForElm('#modal-image').then((elm) => {
    // Modal Setup
    let modal = document.getElementById('modal-image');

    document.querySelector('#modal-image .modal-close').addEventListener('click', function() { 
        modal.style.display = "none";
    });

    // global handler
    document.querySelectorAll('.modal-image-target').forEach(function(e) {
        e.addEventListener('click', function () { 
            modal.style.display = "block";
            document.querySelector('#modal-image img').src = e.getAttribute('data-modal-image-src');
        });
    });
    
});






