'use strict';

define(
    ['underscore'],
    function (_) {
        return {
            flagTemplate: _.template( // TODO create template for it ?
                '<span class="flag-language">' +
                    '<i class="flag flag-<%= country %>"></i>' +
                    '<% if (displayLanguage) { %><span class="language"><%= language %></span><% } %>' +
                '</span>'
            ),
            getFlag: function (locale, displayLanguage) {
                displayLanguage = (undefined === displayLanguage) ? true : displayLanguage;
                if (locale) {
                    var info = locale.split('_');

                    return this.flagTemplate({
                        'country': info[1].toLowerCase(),
                        'language': info[0],
                        'displayLanguage': displayLanguage
                    });
                } else {
                    return '';
                }
            },
            getLabel: function (labels, locale, fallback) {
                return labels[locale] ? labels[locale] : '[' + fallback + ']';
            }
        };
    }
);
