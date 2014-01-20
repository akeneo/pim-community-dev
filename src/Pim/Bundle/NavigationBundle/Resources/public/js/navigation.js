/**
 * Extends Oro Navigation to allow adding flash messages to be displayed on next page load
 *
 * @param {type} OroNavigation
 * @returns {unresolved}
 */
define(
    ['oronavigation/js/navigation', 'oro/messenger'],
    function(OroNavigation, messenger) {

        var QUERY_STRING_REGEX = /^[^\?]+\??/,
            URL_PATH_REGEX = /\?.+/,
            flashMessages = [],
            parent = OroNavigation.prototype,
            instance,
            Navigation = OroNavigation.extend({
                /**
                 * Adds a flash message to be displayed on next page load
                 * @see oro/messenger
                 */
                addFlashMessage: function() {
                    flashMessages.push(arguments);
                },
                /**
                 * @inheritdoc
                 */
                afterRequest: function() {
                    var message;
                    parent.afterRequest.call(this);
                    while (message = flashMessages.shift()) {
                        messenger.notificationFlashMessage.apply(messenger, message);
                    }
                }
            });
        /**
         * @inheritdoc
         */
        Navigation.getInstance = function() {
            return instance;
        };
        /**
         * @inheritdoc
         */
        Navigation.setup = function(options) {
            instance = new Navigation(options);
        };
        return Navigation;
    }
);
