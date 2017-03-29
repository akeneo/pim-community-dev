/* global define */
define([
    'jquery',
    'underscore',
    'text!pim/template/system/flash_message'
],
function ($, _, flash_message_template) {
    'use strict';

    var defaults = {
        container: '#flash-messages .flash-messages-holder',
        delay: false,
        template: _.template(flash_message_template)
    };
    var queue = [];
    var storageKey = 'flash';
    var icons = {
        '': 'icon-warning-orangelight.svg',
        'success': 'icon-check.svg',
        'danger': 'icon-warning-redlight.svg',
        'error': 'icon-warning-redlight.svg'
    };
    var closeIcons = {
        '': 'icon-delete-orangedark.svg',
        'success': 'icon-delete-greendark.svg',
        'danger': 'icon-delete-reddark.svg',
        'error': 'icon-delete-reddark.svg'
    };

    /**
     * Same arguments as for Oro.NotificationMessage
     */
    var showMessage = function (type, message, options) {
        var opt = _.extend({}, defaults, options || {});
        var delay = opt.delay || (opt.flash && 5000);
        var $el = $(opt.template({
            type: type,
            message: message,
            messageTitle: '',
            delay: delay,
            icon: icons[type || ''],
            closeIcon: closeIcons[type || '']
        })).appendTo(opt.container);
        var actions = {
            close: _.bind($el.alert, $el, 'close')
        };
        // if (delay) {
        //     _.delay(actions.close, delay);
        // }
        return actions;
    };

    /**
     * Get flash messages from localStorage or cookie
     */
    var getStoredMessages = function () {
        var messages;
        if (localStorage) {
            messages = JSON.parse(localStorage.getItem(storageKey));
        } else if ($.cookie) {
            messages = JSON.parse($.cookie(storageKey));
        }

        return messages || [];
    };

    /**
     * Set stored messages to cookie or localStorage
     */
    var setStoredMessages = function (flashMessages) {
        var messages = JSON.stringify(flashMessages);
        if (localStorage) {
            localStorage.setItem(storageKey, messages);
        } else if ($.cookie) {
            $.cookie(storageKey, messages);
        }

        return true;
    };

    /**
     * @export oro/messenger
     * @name   oro.messenger
     */
    return {
        /**
         * Shows notification message
         *
         * @param {(string|boolean)} type 'error'|'success'|false
         * @param {string} message text of message
         * @param {Object=} options
         *
         * @param {(string|jQuery)} options.container selector of jQuery with container element
         * @param {(number|boolean)} options.delay time in ms to auto close message
         *      or false - means to not close automatically
         * @param {Function} options.template template function
         * @param {boolean} options.flash flag to turn on default delay close call, it's 5s
         *
         * @return {Object} collection of methods - actions over message element,
         *      at the moment there's only one method 'close', allows to close the message
         */
        notificationMessage: function (type, message, options) {
            var container = (options || {}).container || defaults.container;
            var args = Array.prototype.slice.call(arguments);
            var actions = {close: $.noop};
            if (container && $(container).length) {
                actions = showMessage.apply(null, args);
            } else {
                // if container is not ready then save message for later
                queue.push([args, actions]);
            }

            return actions;
        },

        notificationFlashMessage: function (type, message, options) {
            return this.notificationMessage(type, message, _.extend({flash: true}, options));
        },

        setup: function (options) {
            _.extend(defaults, options);

            var flashMessages = getStoredMessages();
            $.each(flashMessages, function (index, message) {
                queue.push(message);
            });
            setStoredMessages([]);

            while (queue.length) {
                var args = queue.shift();
                _.extend(args[1], showMessage.apply(null, args[0]));
            }
        },

        addMessage: function (type, message, options) {
            var args = [type, message, _.extend({flash: true}, options)];
            var actions = {close: $.noop};

            if (options.hashNavEnabled) {
                queue.push([args, actions]);
            } else { // add message to localStorage or cookie
                var flashMessages = getStoredMessages();
                flashMessages.push([args, actions]);
                setStoredMessages(flashMessages);
            }
        }
    };
});
