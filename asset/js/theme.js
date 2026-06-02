$(document).ready(function () {
  //Dropdown fix
  $('.dropdown-menu a.dropdown-toggle').on('click', function (e) {
    if (!$(this).next().hasClass('show')) {
      $(this).parents('.dropdown-menu').first().find('.show').removeClass("show");
    }
    var $subMenu = $(this).next(".dropdown-menu");
    $subMenu.toggleClass('show');


    $(this).parents('li.nav-item.dropdown.show').on('hidden.bs.dropdown', function (e) {
      $('.dropdown-submenu .show').removeClass("show");
    });


    return false;
  });
  // Focus on search modal input
  const collectionSearchModal = document.getElementById('collectionSearchModal');
  if (collectionSearchModal) {
    collectionSearchModal.addEventListener('shown.bs.modal', event => {
      document.getElementById("collection-search-input").focus();
    });
  }
  // Item browse and search
  if ($('body').hasClass('resource browse') || $('body').hasClass('resource search')) {
    const entries = performance.getEntriesByType("navigation");
    if (entries.length > 0 && entries[0].type === 'back_forward') {
      let lastSearch = JSON.parse(sessionStorage.getItem(window.location.href));
      if (lastSearch && "gridrow" in lastSearch && lastSearch.gridrow) {
        $("#grid-row").replaceWith(lastSearch.gridrow);
      }
    }
    // Infinite scroll/load more
    // check if results have more than one page
    if ($('.pagination .next').length) {
      var status = $(`
        <div class="page-load-status">
          <div class="row justify-content-center mt-3 mt-sm-5">
            <div class="col-auto">
              <div class="spinner-border infinite-scroll-request" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
          </div>
        </div>
        `);
      status.hide();
      var loadButton = `
      <div class="row justify-content-center mt-3 mt-sm-5">
        <div class="col-auto">
          <button id="load-button" class="btn btn-outline-dark btn-lg fw-bold rounded-0 border-2" type="button" aria-controls="grid" aria-label="Load more results">
              Load More Results +
          </button>
        </div>
      </div>
      `;
      $('.pagination-row').after(loadButton).after(status);
      $('#grid-row').attr('aria-live', 'polite')
      let nextURL;
      let appendElement = '.grid-item-container';
      let currentItemCount;

      function updateNextURL(doc) {
        if ($(doc).find('.next').length) {
          nextURL = $(doc).find('.next').attr('href');
          if (!nextURL.includes('&scroll_request=true')) {
            nextURL = nextURL + '&scroll_request=true';
          }
        } else {
          nextURL = null;
        }
      }

      function getLinkData(doc) {
        if ($(doc).find('script[type="application/ld+json"]').length) {
          var newLinkData = $(doc).find('script[type="application/ld+json"]');
          $('script[type="application/ld+json"]').last().after(newLinkData);
        }
      }

      function updateFocus() {
        var firstLoadedElement = $(appendElement)[currentItemCount];
        $(firstLoadedElement).find('a').focus();
        getItemCount();
      }

      function getItemCount() {
        currentItemCount = $(appendElement).length
      }
      // get initial nextURL
      updateNextURL(document);
      getItemCount();
      let $container = $('#grid-row').infiniteScroll({
        // options
        // use function to set custom URLs
        path: function () {
          return nextURL;
        },
        checkLastPage: '.next',
        append: appendElement,
        scrollThreshold: false,
        button: '#load-button',
        hideNav: '.pagination-row',
        status: '.page-load-status',
        history: false,
      });
      // update nextURL on page load
      $container.on('load.infiniteScroll', function (event, body, path, response) {
        history.replaceState(null, null, path.replace('&scroll_request=true', ''));
        updateNextURL(body);
        getLinkData(body);
      });
      $container.on('append.infiniteScroll', function () {
        updateFocus();
      });
      window.addEventListener('beforeunload', () => {
        const params = new URLSearchParams(window.location.search);
        if (params.has('page') && params.get('page') > 1) {
          const gridrow = document.getElementById('grid-row');
          const thisSearch = { gridrow: gridrow.outerHTML };
          sessionStorage.setItem(window.location.href, JSON.stringify(thisSearch));
        }
      });
    }

    $("#sort-dropdown .dropdown-item").click(function () {
      let queryParams = new URLSearchParams(window.location.search);
      if ($(this).data("sort_by")) {
        queryParams.set("sort_by", $(this).data("sort_by"));
      }
      if ($(this).data("sort_order")) {
        queryParams.set("sort_order", $(this).data("sort_order"));
      }
      if ($(this).data("sort")) {
        queryParams.set("sort", $(this).data("sort"));
      }
      queryParams.set("page", 1);
      window.location.search = queryParams.toString();
    });
  }
  //Media viewer
  document.querySelectorAll('.splide').forEach(splideElement => {
    const thisSplide = new Splide(splideElement, {
      type: 'slide',
      rewind: true,
      updateOnMove: true,
      focus: 'center',
      // omitEnd: true,
      autoWidth: true,
      height: '75px',
      // gap: '0.5rem',
      pagination: false,
      isNavigation: true,
      breakpoints: {
        767: {
          height: '50px',
        },
      },
      classes: {
        arrow: 'slider-arrow',
      }
    }).mount();
    thisSplide.on('active', function (Slide) {
      const thisSlide = $(Slide.slide);
      const selectedTab = thisSlide.data('target');
      $('#media-slider-container .splide .splide__slide').removeClass('selected').attr('aria-selected', 'false');
      thisSlide.addClass('selected').attr('aria-selected', 'true');
      $('#mediaTabContent .tab-pane').removeClass('show active');
      // Pause video when changing tabs
      $('#mediaTabContent .tab-pane:not(' + selectedTab + ') .youtube').each(function () {
        $(this)[0].contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
      });
      $('#mediaTabContent .tab-pane:not(' + selectedTab + ') .vimeo').each(function () {
        $(this)[0].contentWindow.postMessage('{"method":"pause"}', '*');
      });
      $('#mediaTabContent .tab-pane:not(' + selectedTab + ') .vjs-tech').each(function () {
        $(this).get(0).pause();
      });
      // Now show tab
      $(selectedTab).addClass('show active');
      $('#current-slide-indicator').text(thisSlide.data('slidenumber'));
    });
  });
  if (window.matchMedia('(min-width: 768px)').matches) {
    //tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"], [data-bs-tooltip="tooltip"]');
    tooltipTriggerList.forEach(tooltipTriggerEl => {
      const tooltip = new bootstrap.Tooltip(tooltipTriggerEl);
      tooltipTriggerEl.addEventListener('show.bs.tooltip', () => {
        tooltipTriggerList.forEach(tooltipTriggerEltoCheck => {
          if (tooltipTriggerEltoCheck != tooltipTriggerEl) {
            const thisTooltip = bootstrap.Tooltip.getInstance(tooltipTriggerEltoCheck);
            thisTooltip.hide();
          }
        })
      })
    })
  }
  //clipboard
  $('.clip-button').each(function (index) {
    new ClipboardJS(this);
  }).on('click', function () {
    // hide any of the checks and then show this one specifically
    $('.bi-clipboard-check').hide();
    $('.bi-clipboard').show();
    $(this).children('.bi-clipboard').hide();
    $(this).children('.bi-clipboard-check').show();
    setTimeout(function () {
      $('.bi-clipboard-check').hide();
      $('.bi-clipboard').show();
    }, 5000);
  });
  $('#link-copy').on('click', function () {
    const tooltip = bootstrap.Tooltip.getInstance('#link-copy');
    tooltip.show();
    setTimeout(function () {
      tooltip.hide();
    }, 3000);
  });
  //toasts
  if ($('#embargo').length) {
    var embargo = $('#embargo');
    toast = new bootstrap.Toast(embargo);
    toast.show();
  }
  //advanced search item set dropdown
  $('#advanced-search-modal #item-sets option:contains("Select item set…")').text('Select collection…');

});