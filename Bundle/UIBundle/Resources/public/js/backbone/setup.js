var Oro = Oro || {};

/**
 * Debug flag
 *
 * @type {Boolean}
 */
Oro.debug = false;

// setup actions after including of backbone.js entity files
$(function() {
    // Override default Backbone.sync
    Backbone.basicSync = Backbone.sync;
    Backbone.sync = function(method, model, options) {
        if (!options || !_.has(options, 'error')) {
            options['error'] = Oro.BackboneError.Dispatch;
        }

        Backbone.basicSync(method, model, options);
    };

    //Backbone.emulateJSON = true;
    //Backbone.history.start();
});
