'use strict';

define(
    ['underscore', 'pim/template/i18n/flag'],
    function (_, template) {
        return {
            flagTemplate: _.template(template),
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
