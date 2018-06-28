'use strict';

define(
    ['underscore', 'pim/template/i18n/flag'],
    function (_, template) {
        return {
            flagTemplate: _.template(template),
            getFlag: function (locale, displayLanguage) {
                displayLanguage = (undefined === displayLanguage) ? true : displayLanguage;
                if (locale) {
                    const info = locale.split('_');
                    let country = info[1];

                    if (3 === info.length) {
                        country = info[2];
                    }

                    return this.flagTemplate({
                        'country': country.toLowerCase(),
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
