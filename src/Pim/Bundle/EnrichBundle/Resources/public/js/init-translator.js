'use strict';

define([
        'jquery',
        'pim/user-context',
        'translator-lib'
    ], function (
        $,
        UserContext,
        Translator
    ) {
        return {
            fetch: function () {
                return $.getJSON('js/translation/' + UserContext.get('uiLocale').split('_')[0] + '.js')
                    .then(function (messages) {
                        Translator.fromJSON(messages);
                    });
            }
        };
    }
);
