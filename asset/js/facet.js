$(document).ready(function () {
    $(".facet-sort").click(function () {
        let facetPane = $(this).closest(".facet-pane");
        queryData = facetPane.data();
        // set the new sort
        queryData.sort = $(this).data("sort");
        // reset page to the first
        queryData.currentPage = 1;
        // Call function to update page
        update_pane(facetPane, queryData);
        // Update dropdown display
        $(this).closest(".facet-sort-dropdown-menu").find(".facet-sort").prop("disabled", false).toggleClass("disabled");
        $(this).prop("disabled", true);
        $(this).closest(".facet-sort-dropdown").find(".facet-dropdown-text").text($(this).text());
    });
    $(".facet-pagination-button").click(function () {
        let facetPane = $(this).closest(".facet-pane");
        queryData = facetPane.data();
        let totalPages = Math.ceil(queryData.total / queryData.perPage);
        switch ($(this).data("paginationStep")) {
            case "first":
                queryData.currentPage = 1;
                break;
            case "previous":
                queryData.currentPage = ((queryData.currentPage - 1) > 0) ? (queryData.currentPage - 1) : 1;
                break;
            case "next":
                queryData.currentPage = ((queryData.currentPage + 1) <= totalPages) ? (queryData.currentPage + 1) : totalPages;
                break;
            case "last":
                queryData.currentPage = totalPages;
                break;
        }
        update_pane(facetPane, queryData);
    });
    // reset scroll when tab is selected
    const tabElms = document.querySelectorAll('#facets-tab a[data-bs-toggle="list"]')
    tabElms.forEach(tabElm => {
        tabElm.addEventListener('shown.bs.tab', event => {
            document.getElementById('facetContent').scrollTop = 0;
        })
    })
});

function update_pane(facetPane, queryData) {

    // Hide pane and add loading animation
    let facetList = facetPane.find(".facet-list");
    facetList.empty();
    facetList.html(`
        <div id="facet-loader">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Fetching data...</span>
            </div>
        </div>
        `);
    // Update paginator
    facetPane.find(".pagination-current-page").text(queryData.currentPage);
    let totalPages = Math.ceil(queryData.total / queryData.perPage);
    facetPane.find(".facet-pagination-button").each(function () {
        switch ($(this).data("paginationStep")) {
            case "first":
                queryData.currentPage == 1 ? $(this).prop("disabled", true) : $(this).prop("disabled", false);
                break;
            case "previous":
                queryData.currentPage == 1 ? $(this).prop("disabled", true) : $(this).prop("disabled", false);
                break;
            case "next":
                queryData.currentPage == totalPages ? $(this).prop("disabled", true) : $(this).prop("disabled", false);
                break;
            case "last":
                queryData.currentPage == totalPages ? $(this).prop("disabled", true) : $(this).prop("disabled", false);
                break;
        }
    });
    // Fetch data
    let queryParams = new URLSearchParams(window.location.search);
    queryParams.set('facet_name', queryData.facetName);
    queryParams.set('facet_page', queryData.currentPage);
    queryParams.set('per_page', queryData.perPage);
    queryParams.set('sort', queryData.sort);
    const url = "search/facet?" + queryParams.toString();
    fetch(url)
        .then((response) => response.json())
        .then((data) => {
            if (data && data.length) {
                facetList.empty();
                if (data.length > 20) {
                    facetList.addClass("column");
                } else {
                    facetList.removeClass("column");
                }
                data.forEach(facet => {
                    if (!((queryData.facetName == "item_set_dcterms_title") && (facet.value.toLowerCase() == "special collections and fit archive"))) {
                        facetList.append(`
                        <a href="${facet.url}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center text-dark fw-bold">
                            ${facet.value}
                            <span class="badge text-bg-light rounded-pill fw-normal">${facet.count}</span>
                        </a>
                        `);
                    }
                });
            } else {
                facetList.empty();
                facetList.html(`
                    <div class="alert alert-danger" role="alert">
                    There was an error loading additional facets. Please reload the page and try again. If you continue to run into this problem, please contact us at <a href="mailto:repository@fitnyc.edu?subject=Error loading facets on SPARC Digital">repository@fitnyc.edu</a>.
                    </div>
                    `);
            }
        })
        .catch((error) => {
            console.log(error);
            facetList.empty();
            facetList.html(`
                    <div class="alert alert-danger" role="alert">
                    There was an error loading additional facets. Please reload the page and try again. If you continue to run into this problem, please contact us at <a href="mailto:repository@fitnyc.edu?subject=Error loading facets on SPARC Digital">repository@fitnyc.edu</a>.
                    </div>
                    `);
        });
}