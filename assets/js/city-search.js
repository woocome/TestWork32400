(function($) {
    const cities = {
        searchInputSelector: '#city-search',
        searchFormSelector: '#city-search-form',
        tableBodySelector: '#city-table tbody',
        ajaxRequest: null,
        init: function() {
            this.onSearchInput();
        },
        debounce: function(func, delay) {
            let timer;

            return function(...args) {
                const context = this;
                clearTimeout(timer);
                timer = setTimeout(() => func.apply(context, args), delay);
            };
        },
        onSearchInput: function() {
            $(this.searchInputSelector).on('input', this.debounce(this.searchCities, 300));
        },
        searchCities: function(e) {
            const searchValue = $(e.target).val();

            // cancel previous request
            if (this.ajaxRequest) this.ajaxRequest.abort();

            this.ajaxRequest = $.ajax({
                type: 'POST',
                url: city_search.ajax_url,
                data: {
                    ajax_nonce: city_search.ajax_nonce,
                    action: city_search.action,
                    search: searchValue
                },
                success: function(response) {
                    $(cities.tableBodySelector).html(response);
                },
                complete: function() {
                    cities.removeErrorMessage();
                }
            });
        },
        searchErrorMessage: function(message) {
            this.removeErrorMessage();

            $(this.searchFormSelector).append(`<span class="error text-error js-search-error">${message}</p>`);
        },
        removeErrorMessage: function() {
            $('.js-search-error').remove();
        }
    }
    $(document).ready(function() {
        cities.init();
    });
    
})(jQuery)