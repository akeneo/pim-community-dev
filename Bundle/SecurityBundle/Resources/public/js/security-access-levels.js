/* global require */
require(['jquery', 'routing'],
    function ($, routing) {
        'use strict';

        var accessLevelLinkSelector = '.access_level_value a';
        var selectDivSelector = '.access_level_value_choice';
        var linkDivSelector = 'access_level_value_link';

        var accessLevelRoute = 'oro_security_access_levels';

        var objectIdentityAttribute = 'data-identity';
        var selectorNameAttribute = 'data-selector-name';
        var selectorIdAttribute = 'data-selector-id';
        var valueAttribute = 'data-value';

        $(function () {
            $(document).on('click', accessLevelLinkSelector, function () {
                var link = $(this);
                var parentDiv = link.parent().parent();
                var selectDiv = parentDiv.find(selectDivSelector);
                var linkDiv = parentDiv.find(linkDivSelector);
                link.hide();
                $.ajax({
                    url: routing.generate(accessLevelRoute, { _format: 'json' }),
                    data: {
                        oid: parentDiv.attr(objectIdentityAttribute)
                    },
                    success: function (data) {
                        var selector = $('<select>');
                        selector.attr('name', parentDiv.attr(selectorNameAttribute));
                        selector.attr('id', parentDiv.attr(selectorIdAttribute));
                        $.each(data, function (value, text) {
                            var option = $('<option>').attr('value', value).text(text);
                            if (parentDiv.attr(valueAttribute) == value) {
                                option.attr('selected', 'selected');
                            }
                            selector.append(option);
                        });
                        selectDiv.append(selector);
                        selectDiv.show();
                        linkDiv.remove();
                        $('select').uniform('update');
                    },
                    error: function () {
                        link.show();
                    }
                });

                return false;
            });
        });
    });
