// Simplu exemplu pentru a afișa imaginea mare la click pe thumbnail (fără lightbox real)
document.addEventListener('DOMContentLoaded', function() {
    const mainImageElement = document.getElementById('mainProductImage');
    const secondaryImages = document.querySelectorAll('.secondary-thumbnail');

    if (mainImageElement && secondaryImages.length > 0) {
        secondaryImages.forEach(thumb => {
            thumb.addEventListener('click', function() {
                mainImageElement.src = this.dataset.largeSrc || this.src; // Folosește data-large-src dacă e definit
            });
        });
    }

    // Poți adăuga aici cod pentru un lightbox mai avansat
    // De exemplu, la click pe imaginea principală sau un thumbnail, deschide un modal
});
