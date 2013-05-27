var Oro = Oro || {};
Oro.BackboneError = Oro.BackboneError || {};

/**
 * Server error header
 *
 * @type {String}
 */
Oro.BackboneError.Header = "Server error";

/**
 * Server error message
 *
 * @type {String}
 */
Oro.BackboneError.Message = "Error! Incorrect server response.";

$(function() {
    /**
     * Backbone error modal
     *
     * @param {Object} model
     * @param {Object} xhr
     * @param {Object} options
     * @constructor
     */
    Oro.BackboneError.Modal = function(model, xhr, options) {
        var message = Oro.BackboneError.Message;
        if (Oro.debug) {
            message += '<br><b>Debug:</b>' + xhr.responseText;
        }

        var modal = new Oro.BootstrapModal({
            title: Oro.BackboneError.Header,
            content: message
        });
        modal.open();
    };
});
