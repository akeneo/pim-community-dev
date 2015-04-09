'use strict';

define(
    ['underscore'],
    function (_) {
        return {
            flagTemplate: _.template(
                '<span class="flag-language">' +
                    '<i class="flag flag-<%= country %>"></i>' +
                    '<span class="language"><%= language %></span>' +
                '</span>'
            ),
            getFlag: function (locale) {
                if (locale) {
                    var info = locale.split('_');

                    return this.flagTemplate({
                        'country': info[1].toLowerCase(),
                        'language': info[0]
                    });
                } else {
                    return '';
                }
            }
        };
    }
);
