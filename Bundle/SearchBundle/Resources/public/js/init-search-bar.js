$(document).ready(function () {
    var _searchFlag = false;
    var timeout = 700;
    var searchBarContainer = $('#search-div');
    var searchBarInput = searchBarContainer.find('#search-bar-search');
    var searchBarDropdown = searchBarContainer.find('#search-bar-dropdown');
    var searchBarButton = searchBarContainer.find('#search-bar-button');
    var searchBarForm = $('#search-bar-from');
    var searchDropdown = searchBarContainer.find('#search-dropdown');

    if (!_.isUndefined(Oro.Events)) {
        Oro.Events.bind(
            'hash_navigation_request:complete',
            SearchByTagClose,
            this
        );

        Oro.Events.bind(
            'hash_navigation_request:form-start',
            function (form) {
                if ($(form).hasClass('search-form')) {
                    var $searchString = $.trim($(form).find('.search').val());
                    Oro.Registry.setElement('form_validate', $searchString.length > 0);
                }
            },
            this
        );
    }

    $('.search-form').submit(function(){
        var $searchString = $.trim($(this).find('.search').val());
        if ($searchString.length == 0) {
            return false;
        }
        // clear value after search
        //$(this).find('.search').val('').blur();
        SearchByTagClose();
    });


    searchBarDropdown.find('li a').click(function (e) {
        searchBarDropdown
            .find('li.active')
            .removeClass('active');
        $(this)
            .closest('li')
            .addClass('active');
        searchBarForm.val($(this).parent().attr('data-alias'));
        searchBarButton.find('.search-bar-type').html($(this).html());
        SearchByTagClose();
        e.preventDefault();
    });

    var searchInterval = null;
    function SearchByTag() {
        clearInterval(searchInterval);
        var queryString = searchBarInput.val();

        if (queryString == '' || queryString.length < 3) {
            searchBarContainer.removeClass('header-search-focused');
            searchDropdown.empty();
        } else {
            $.ajax({
                url: Routing.generate('oro_api_get_search', { _format: 'html' }),
                data: {
                    search: queryString,
                    from: searchBarForm.val(),
                    limit: 5
                },
                success: function(data) {
                    searchBarContainer.removeClass('header-search-focused');
                    searchDropdown.html(data);

                    var count = searchDropdown.find('li').length;

                    $('#recordsCount').val(count);

                    if (count > 0) {
                        searchBarContainer.addClass('header-search-focused');

                        /**
                         * Backbone event. Fired when search ajax request is complete
                         * @event top_search_request:complete
                         */
                        Oro.Events.trigger('top_search_request:complete');
                    }
                }
            });
        }
    }

    function SearchByTagClose() {
        if (searchBarInput.size()) {
            var queryString2 = searchBarInput.val();

            searchBarContainer
                .removeClass('header-search-focused')
                .toggleClass('search-focus', queryString2.length > 0);
        }
    }

    searchBarInput.keydown(function(event) {
        if (event.keyCode == 13) {
            $('#top-search-form').submit();
            event.preventDefault();

            return false;
        }
    });

    searchBarInput.keypress(function(e) {
        if (e.keyCode == 8 || e.keyCode == 46 || (e.which !== 0 && e.charCode !== 0 && !e.ctrlKey && !e.altKey)) {
            clearInterval(searchInterval);
            searchInterval = setInterval(SearchByTag, timeout);
        } else {
            switch (e.keyCode) {
                case 40:
                case 38:
                    searchBarContainer.addClass('header-search-focused');
                    searchDropdown.find('a:first').focus();
                    e.preventDefault();
                    return false;
                case 27:
                    searchBarContainer.removeClass('header-search-focused');
                    break;
            }
        }
    });

    $(document).on('keydown', '#search-dropdown a', function (evt) {
        var $this = $(this);

        var selectPrevious = _.bind(function() {
            $this.parent('li').prev().find('a').focus();
            evt.stopPropagation();
            evt.preventDefault();

            return false;
        }, this);

        var selectNext = _.bind(function() {
            $this.parent('li').next().find('a').focus();
            evt.stopPropagation();
            evt.preventDefault();

            return false;
        }, this);

        switch(evt.keyCode) {
            case 13: // Enter key
            case 32: // Space bar
                this.click();
                evt.stopPropagation();
                break;
            case 9: // Tab key
                if (evt.shiftKey) {
                    selectPrevious();
                }
                else {
                    selectNext();
                }
                evt.preventDefault();
                break;
            case 38: // Up arrow
                selectPrevious();
                break;
            case 40: // Down arrow
                selectNext();
                break;
            case 27:
                searchBarContainer.removeClass('header-search-focused');
                searchBarInput.focus();
                break;
        }
    });

    $(document).on('focus', '#search-dropdown a', function () {
        $(this).parent('li').addClass('hovered');
    });

    $(document).on('focusout', '#search-dropdown a', function () {
        $(this).parent('li').removeClass('hovered');
    });

    searchBarInput.focusout(function () {
        if (!_searchFlag) {
            SearchByTagClose()
        }
    });

    searchBarContainer
        .mouseenter(function () {
            _searchFlag = true;
        })
        .mouseleave(function () {
            _searchFlag = false;
        });

    searchBarInput.focusin(function () {
        searchBarContainer.addClass('search-focus');
    });
});
