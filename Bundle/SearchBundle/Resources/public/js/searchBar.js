$(document).ready(function () {
    var _searcjFlag = false;

    if (!_.isUndefined(Oro.Events)) {
        Oro.Events.bind(
            'hash_navigation_request:complete',
            function () {
                SearchByTagClose();
            },
            this
        );

        Oro.Events.bind(
            'hash_navigation_request:form-start',
            function (form) {
                if ($(form).hasClass('search-form')) {
                    var send = true;
                    var $searchString = $.trim($(form).find('.search').val());
                    if ($searchString.length == 0) {
                        send = false;
                    }
                    Oro.Registry.setElement('form_validate', send);
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

    $('#search-bar-dropdown li a').click(function (e) {
        $( '#search-bar-dropdown li' ).each(function( index ) {
            $(this).removeClass('active');
        });
        $(this).parent().addClass('active');
        $('#search-bar-from').val($(this).parent().attr('data-alias'));
        $('#search-bar-button').html($(this).html() + '<span class="caret"></span>');
        SearchByTagClose();
        SearchInputWidth();
        e.preventDefault();
    });

    if ($('#search-bar-search').size()) {
        SearchInputWidth();
    }

    function SearchByTag() {
        var queryString = jQuery('#search-bar-search').val();

        if (queryString == '' || queryString.length < 3) {
            $('#search-div').removeClass('header-search-focused');
            $('#search-dropdown').empty();
        } else {
            $.ajax({
                url: Routing.generate('oro_api_get_search', { _format: 'html' }),
                data: {
                    search: queryString,
                    from: $('#search-bar-from').val(),
                    limit: 5
                },
                success: function (data) {
                    var count = 0;

                    $('#search-div').removeClass('header-search-focused');
                    $('#search-dropdown').html(data);

                    count = $('#search-dropdown li').length;

                    $('#recordsCount').val(count);

                    if (count > 0) {
                        $('#search-div').addClass('header-search-focused');

                        /**
                         * Backbone event. Fired when search ajax request is complete
                         * @event top_search_request:complete
                         */
                        Oro.Events.trigger('top_search_request:complete');
                    }
                }
            });
        }
    };

    function SearchInputWidth() {
        var _generalWidth = $('#search-div').width(),
            _btnSearchWidth = $('#search-div button.btn-search').outerWidth() + $('#search-bar-button').outerWidth(),
            _inputSearchWidth = _generalWidth - _btnSearchWidth;

        $('#search-bar-search').width(_inputSearchWidth);

        var _dropdownSearchWidth = $('#search-div div.header-search-frame').width() - 1;
        /* just need a design without border */
        $('#search-div div#search-dropdown').outerWidth(_dropdownSearchWidth);
    };

    function SearchByTagClose() {
        if ($('#search-bar-search').size()) {
            var queryString2 = $('#search-bar-search').val();

            $('#search-div')
                .removeClass('header-search-focused')
                .toggleClass('search-focus', queryString2.length > 0);
        }
    };

    $('#search-bar-search').keydown(function (event) {
        if (event.keyCode == 13) {
            $('#top-search-form').submit();
            event.preventDefault();

            return false;
        }
    });

    $('#search-bar-search').keyup(function (event) {
        switch(event.keyCode) {
            case 40: //down
            case 38: //up
                $('#search-div').addClass('header-search-focused');
                $('#search-dropdown a:first').focus();
                event.preventDefault();

                return false;
            default:
                SearchByTag();
        }
    });

    $(document).on('keydown', '#search-dropdown a', function (evt) {
        var $this = $(this);

        function select_previous () {
            $this.parent('li').prev().find('a').focus();
            evt.stopPropagation();
            evt.preventDefault();

            return false;
        }

        function select_next () {
            $this.parent('li').next().find('a').focus();
            evt.stopPropagation();
            evt.preventDefault();

            return false;
        }

        switch(evt.keyCode) {
            case 13: // Enter key
            case 32: // Space bar
                $this.click();
                evt.stopPropagation();
                break;
            case 9: // Tab key
                if (D.shiftKey) {
                    select_previous();
                }
                else {
                    select_next();
                }
                evt.preventDefault();
                break;
            case 38: // Up arrow
                select_previous();
                break;
            case 40: // Down arrow
                select_next();
                break;
        }
    });

    $(document).on('focus', '#search-dropdown a', function () {
        $(this).parent('li').addClass('hovered');
    });

    $(document).on('focusout', '#search-dropdown a', function () {
        $(this).parent('li').removeClass('hovered');
    });

    $('#search-bar-search').focusout(function () {
        if (!_searcjFlag) {
            SearchByTagClose()
        }
    });

    $('#search-div')
        .mouseenter(function () {
            _searcjFlag = true;
        }).mouseleave(function () {
            _searcjFlag = false;
        });

    $('#search-bar-search').focusin(function () {
        $('#search-div').addClass('search-focus');
    });
});
