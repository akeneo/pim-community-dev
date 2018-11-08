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
                return $.getJSON('js/translation/' + UserContext.get('user_default_locale') + '.js')
                    .then(function (messages) {
                        Translator.fromJSON(messages);
                    });
            }
        };
    }
);
