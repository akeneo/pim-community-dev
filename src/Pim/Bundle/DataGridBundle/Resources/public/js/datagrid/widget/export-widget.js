define(
    ['backbone'],
    function( Backbone) {
        'use strict';

        var ExportWidget = Backbone.View.extend({

            action: null,

            initialize: function(action) {
                this.action = action;
            },

            run: function() {
                window.location = this.action.getLinkWithParameters();
            }
        });

        return ExportWidget;
    }
);
