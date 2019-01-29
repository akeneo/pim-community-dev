define(
    ['jquery', 'underscore', 'backbone', 'oro/messenger', 'oro/error'],
    function ($, _, Backbone, messenger, Error) {
        'use strict';

        return Backbone.View.extend({

            action: null,

            initialize: function (action) {
                this.action = action;
            },

            run: function () {
                $.get(this.action.getLinkWithParameters())
                    .done(function () {
                        messenger.notify(
                            'success',
                            _.__('pim_datagrid.mass_action.quick_export.success')
                        );
                    })
                    .fail(function (jqXHR) {
                        if (jqXHR.status === 401) {
                            Error.dispatch(null, jqXHR);
                        } else {
                            messenger.notify(
                                'error',
                                _.__(jqXHR.responseText)
                            );
                        }
                    });
            }
        });
    }
);
