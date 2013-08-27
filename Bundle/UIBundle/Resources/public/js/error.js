/* global define */
define(['underscore', 'routing', 'oro/app', 'oro/bootstrap-modal'],
function(_, routing, app, BootstrapModal) {
    'use strict';

    var defaults = {
            header: 'Server error',
            message: 'Error! Incorrect server response.'
        },

        /**
         * @export oro/error
         */
        error = {
            dispatch: function(model, xhr, options) {
                var self = error.dispatch;
                self.init(model, xhr, _.extend({}, defaults, options));
            }
        };

    _.extend(error.dispatch, {
        /**
         * Error dispatch
         *
         * @param {Object} model
         * @param {Object} xhr
         * @param {Object} options
         */
        init: function(model, xhr, options) {
            if (xhr.status === 401) {
                this._processRedirect();
            } else if (xhr.readyState === 4) {
                this._processModal(xhr, options);
            }
        },

        /**
         * Shows modal window
         * @param {Object} xhr
         * @param {Object} options
         * @private
         */
        _processModal: function(xhr, options) {
            var modal,
                message = options.message;
            if (app.debug) {
                message += '<br><b>Debug:</b>' + xhr.responseText;
            }

            modal = new BootstrapModal({
                title: options.header,
                content: message,
                cancelText: false
            });
            modal.open();
        },

        /**
         * Redirects to login
         * @private
         */
        _processRedirect: function() {
            document.location.href = routing.generate('oro_user_security_login');
        }
    });

    return error;
});
