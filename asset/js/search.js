$(document).ready(function () {
    const info = document.getElementById('full-text-info');
    const tooltip = new bootstrap.Tooltip(info);
    const suggesterURL = $('#suggesterscript').data('url');
    const terms = new Bloodhound({
        datumTokenizer: Bloodhound.tokenizers.obj.whitespace('term'),
        queryTokenizer: Bloodhound.tokenizers.whitespace,
        prefetch: suggesterURL
    });
    let termSuggester = null;
    if (!$('#full-text').prop('checked')) {
        initializeTypeahead();
    }
    $('#search-form-standard').on("submit", function (event) {
        event.preventDefault();
        if ($('#full-text').prop('checked')) {
            $('#full-text').prop('disabled', true);
            $(this).attr('action', '/ocrsearch');
            $(this).off('submit').submit();
        } else {
            $(this).off('submit').submit();
        }

    });
    $('#full-text').on('change', function () {
        if ($(this).is(':checked')) {
            if (termSuggester) {
                termSuggester.typeahead('destroy');
            }
        } else {
            initializeTypeahead();
        }
    });

    const advancedSearchModal = document.getElementById('advancedSearchModal');
    const itemsetselect = document.getElementById('item_set_id_full_text_select');
    advancedSearchModal.addEventListener('show.bs.modal', () => {
        if (itemsetselect.dataset.loaded == "notloaded") {
            //if item sets exist in session storage, load them. If not, use fetch to get them and store them in session storage
            if (sessionStorage.getItem("fullTextItemSets")) {
                const fullTextItemSets = JSON.parse(sessionStorage.getItem("fullTextItemSets"));
                loadItemSetsFullText(itemsetselect, fullTextItemSets);
                itemsetselect.dataset.loaded = "loaded";
            } else {
                fetch("/ocrsearch/itemsets.json")
                    .then((response) => response.json())
                    .then((fullTextItemSets) => {
                        sessionStorage.setItem("fullTextItemSets", JSON.stringify(fullTextItemSets));
                        loadItemSetsFullText(itemsetselect, fullTextItemSets);
                    })
                itemsetselect.dataset.loaded = "loaded";
            }
        }

    })

    const collectionSearchModal = document.getElementById('collectionSearchModal');
    if (collectionSearchModal) {
        const modalBody = collectionSearchModal.querySelector('.modal-body');
        collectionSearchModal.addEventListener('show.bs.modal', () => {
            if (modalBody.dataset.loaded == "notloaded") {
                //if item sets exist in session storage, load them. If not, use fetch to get them and store them in session storage
                if (sessionStorage.getItem("fullTextItemSets")) {
                    const fullTextItemSets = JSON.parse(sessionStorage.getItem("fullTextItemSets"));
                    loadCollectionSearch(modalBody, fullTextItemSets);
                    modalBody.dataset.loaded = "loaded";
                } else {
                    fetch("/ocrsearch/itemsets.json")
                        .then((response) => response.json())
                        .then((fullTextItemSets) => {
                            sessionStorage.setItem("fullTextItemSets", JSON.stringify(fullTextItemSets));
                            loadCollectionSearch(modalBody, fullTextItemSets);
                        })
                    modalBody.dataset.loaded = "loaded";
                }
            }
        });
    }

    function loadItemSetsFullText(itemsetselect, fullTextItemSets) {
        const currentItemSet = itemsetselect.dataset.currentitemset;
        fullTextItemSets.forEach(itemset => {
            const option = document.createElement("option");
            option.value = itemset.id;
            option.textContent = itemset.title;
            if (itemset.id == currentItemSet) {
                option.selected = true;
            }
            itemsetselect.appendChild(option);
        });
    }

    function loadCollectionSearch(modalBody, fullTextItemSets) {
        const collectionID = modalBody.dataset.collectionid;
        const collectionTitle = modalBody.dataset.collectiontitle;
        const fulltext = fullTextItemSets.some(collection => collection.id == collectionID);
        $(modalBody).empty().hide();
        $(modalBody).append(`
                <form id="collectionSearchForm" action="/search" method="get">
                    <div class="input-group value mb-2">
                      <input type="search" name="q" placeholder="Search the ${collectionTitle}" class="form-control" id="collection-search-input" aria-label="Collection Search" autocomplete="off" value=""> <button class="btn btn-dark rounded-0" type="submit" aria-label="Submit Collection Search">
                        <svg xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                          <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"></path>
                        </svg>
                      </button>
                    </div>
                  </form>
                `);
        const collectionSearchForm = document.getElementById('collectionSearchForm');
        if (fulltext) {
            $(collectionSearchForm).append(`
                <div class="form-check me-2 mb-0 fw-normal">
                    <input class="form-check-input border-1 border-secondary-subtle" type="checkbox" value="" name="collection-search-full-text" id="collection-search-full-text" checked>
                    <label class="form-check-label" for="full-text">
                        Full-Text Search
                    </label>
                </div>
                `);
        }
        $(modalBody).fadeIn();
        $(collectionSearchForm).on("submit", function (event) {
            event.preventDefault();
            if ($('#collection-search-full-text').prop('checked')) {
                $('#collection-search-full-text').prop('disabled', true);
                $(this).append(`<input type="hidden" name="item_set_id" value="${collectionID}">`);
                $(this).attr('action', '/ocrsearch');
                $(this).off('submit').submit();
            } else {
                $(this).append(`<input type="hidden" name="item_set_id[]" value="${collectionID}">`);
                $(this).off('submit').submit();
            }

        });

    }

    function initializeTypeahead() {
        termSuggester = $('#main-search-input').typeahead({
            hint: false,
            highlight: true,
            minLength: 0

        }, {
            name: 'terms',
            source: terms,
            display: 'term',
            limit: 15,
            templates: {
                suggestion: function (data) {
                    return '<div class="d-flex justify-content-between align-items-center"><div>' + data.term + '</div><span class="badge text-bg-light rounded-pill ms-1 fw-normal">' + data.count + '</span></div>';
                }
            }
        });
        termSuggester.on('typeahead:select', function (ev, data) {
            $(this).blur();
            const escapedTerm = solrEscape(data.term);
            const query = '\"' + escapedTerm + '\"';
            const search_url = new URL(document.getElementById("search-form-standard").action);
            search_url.searchParams.append($(this).attr("name"), query);
            search_url.searchParams.append('suggester', 'true');
            window.location.href = search_url;
        });
    }
    //solr query escape from https://github.com/joeybaker/lucene-escape-query/blob/master/index.js
    function solrEscape(str) {
        return [].map.call(str, function escapeSpecialCharacter(char) {
            if (char === '+' ||
                char === '-' ||
                char === '&' ||
                char === '|' ||
                char === '!' ||
                char === '(' ||
                char === ')' ||
                char === '{' ||
                char === '}' ||
                char === '[' ||
                char === ']' ||
                char === '^' ||
                char === '"' ||
                char === '~' ||
                char === '*' ||
                char === '?' ||
                char === ':' ||
                char === '\\' ||
                char === '/'
            ) return '\\' + char
            else return char
        }).join('')
    };
});