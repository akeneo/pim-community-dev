'use strict';

define(
    ['underscore'],
    function (_) {
        return {
            // Why isn't it a template flag.html ?
            flagTemplate: _.template(
                '<span class="flag-language">' +
                    '<i class="flag flag-<%= country %>"></i>' +
                    '<% if (displayLanguage) { %><span class="language"><%= language %></span><% } %>' +
                '</span>'
            ),
            getFlag: function (locale, displayLanguage) {
                displayLanguage = (undefined === displayLanguage) ? true : displayLanguage;
                if (locale) { // if locale null ? What can be in local else ?
                    var info = locale.split('_');

                    return this.flagTemplate({
                        'country': info[1].toLowerCase(),
                        'language': info[0],
                        'displayLanguage': displayLanguage
                    });
                } else {
                    return '';
                }
            }
        };
    }
);
