$(document).ready(function () {
    const exhibitCarouselEl = document.getElementById("exhibitCarousel");
    const exhibitCarousel = bootstrap.Carousel.getOrCreateInstance(exhibitCarouselEl);
    const exhibitNavigationEl = document.getElementById('exhibitNavigation')
    const exhibitNavigation = bootstrap.Offcanvas.getOrCreateInstance(exhibitNavigationEl);
    exhibitCarouselEl.addEventListener('slide.bs.carousel', event => {
        $(".pagination-current-page").text(event.to + 1);
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
        $('.slide-trigger').removeClass('active disabled').attr({
            "aria-current": "false",
            "aria-disabled": "false"
        });
        $(`.slide-trigger[data-index="${event.to}"]`).addClass('active disabled').attr({
            "aria-current": "true",
            "aria-disabled": "true"
        });
        // pause any videos
        $('.youtube').each(function () {
            $(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
        });
        $('.vimeo').each(function () {
            $(this)[0].contentWindow.postMessage('{"method":"pause"}', '*');
        });
        $('.vjs-tech').each(function () {
            $(this).get(0).pause();
        });
    })
    exhibitCarouselEl.addEventListener('slid.bs.carousel', event => {
        // event.relatedTarget is the newly activated slide DOM element
        event.relatedTarget.focus({ preventScroll: true });
    });
    $('.slide-trigger').on('click', function () {
        exhibitNavigation.hide();
        exhibitNavigationEl.addEventListener('hidden.bs.offcanvas', event => {
            exhibitCarousel.to($(this).data('index'));
        }, { once: true });
    })

    const viewerModalEl = document.getElementById('viewerModal');
    viewerModalEl.addEventListener('show.bs.modal', event => {
        const title = viewerModalEl.querySelector('#viewerModalLabel');
        title.textContent = event.relatedTarget.dataset.title;
        const viewerLink = viewerModalEl.querySelector('#viewerLink');
        viewerLink.href = event.relatedTarget.dataset.record;
    })
    viewerModalEl.addEventListener('shown.bs.modal', event => {
        const body = viewerModalEl.querySelector('.modal-body');
        body.innerHTML = event.relatedTarget.dataset.media;
        const thisOSD = body.querySelector('.openseadragon-frame .openseadragon');
        // if it's an osd instance, it needs to be loaded, and then destroyed on close
        if (thisOSD) {
            let thisViewer = loadViewer($(thisOSD), thisOSD.id, thisOSD.dataset.infojson, thisOSD.dataset.authtoken, thisOSD.dataset.thumbnail, thisOSD.dataset.expiration);
            viewerModalEl.addEventListener('hide.bs.modal', event => {
                thisViewer.destroy();
                thisViewer = null;
            }, { once: true });
        }
    })
    viewerModalEl.addEventListener('hide.bs.modal', event => {
        const body = viewerModalEl.querySelector('.modal-body');
        body.innerHTML = `
            <div class="ratio ratio-16x9">
                <div></div>
            </div>
        `;
    });
});