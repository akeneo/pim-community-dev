define(
    ['jquery', 'underscore', 'backbone', 'oro/messenger'],
    function ($, _, Backbone, messenger) {
        'use strict';

        return Backbone.View.extend({

            action: null,

            initialize: function (action) {
                this.action = action;
            },

            run: function () {
                $.get(this.action.getLinkWithParameters())
                    .done(function () {
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pim.grid.mass_action.quick_export.launched')
                        );
                    })
                    .error(function (jqXHR) {
                        messenger.notificationFlashMessage(
                            'error',
                            _.__(jqXHR.responseText)
                        );
                    });
            }
        });
    }
);
