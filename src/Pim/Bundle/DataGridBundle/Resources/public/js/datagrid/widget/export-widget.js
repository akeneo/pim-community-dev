define(
    ['backbone', 'oro/messenger'],
    function( Backbone, messenger) {
        'use strict';

        var ExportWidget = Backbone.View.extend({

            action: null,

            initialize: function(action) {
                this.action = action;
            },

            run: function() {
                $.ajax(this.action.getLinkWithParameters());
                messenger.notificationFlashMessage('success', _.__('pim.grid.mass_action.quick_export.launched'));
            }
        });

        return ExportWidget;
    }
);
