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
    Oro.BackboneError.Dispatch = function (model, xhr, options) {
        var self = Oro.BackboneError.Dispatch;
        self.init(model, xhr, options);
    };

    _.extend(Oro.BackboneError.Dispatch, {
        /**
         * Backbone error dispatch
         *
         * @param {Object} model
         * @param {Object} xhr
         * @param {Object} options
         */
        init: function(model, xhr, options) {
            if (xhr.status == 401) {
                this._processRedirect();
            } else {
                if (xhr.readyState == 4) {
                    this._processModal(xhr);
                }
            }
        },

        /**
         * Backbone error - modal window
         * @param {Object} xhr
         * @private
         */
        _processModal: function(xhr) {
            var message = Oro.BackboneError.Message;
            if (Oro.debug) {
                message += '<br><b>Debug:</b>' + xhr.responseText;
            }

            var modal = new Oro.BootstrapModal({
                title: Oro.BackboneError.Header,
                content: message,
                cancelText: false
            });
            modal.open();
        },

        /**
         * Backbone error - redirect to login
         * @private
         */
        _processRedirect: function() {
            document.location.href = Routing.generate('oro_user_security_login');
            return;
        }
    });
});
