/* jshint browser:true */
(function (factory) {
    'use strict';
    /* global define, jQuery, _, Oro */
    if (typeof define === 'function' && define.amd) {
        define(['jQuery', '_', 'Oro', '__'], factory);
    } else {
        factory(jQuery, _, Oro, _.__);
    }
}(function ($, _, Oro, __) {
    'use strict';
    var defaults = {
            container: '',
            delay: false,
            template: $.noop
        },
        queue = [],

        /**
         * Shows notification message
         *
         * @param {(string|boolean)} type 'error'|'success'|false
         * @param {(string|Array)} message text of message or if is an array
         *      it's arguments for translation function
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
        message = Oro.NotificationMessage = function(type, message, options) {
            var container = (options || {}).container ||  defaults.container,
                args = Array.prototype.slice.call(arguments),
                actions = {close: $.noop};
            if (container && $(container).length) {
                actions = showMessage.apply(null, args);
            } else {
                // if container is not ready then save message for later
                queue.push([args, actions]);
            }
            return actions;
        },

        /**
         * Same arguments as for Oro.NotificationMessage
         */
        showMessage = function(type, message, options) {
            // if message is an array assume this is arguments for translation function
            var msg = _.isArray(message) ? __.apply(null, message): message,
                opt = _.extend({}, defaults, options || {}),
                $el = $(opt.template({type: type, message: msg})).appendTo(opt.container),
                delay = opt.delay || (opt.flash && 5000),
                actions = {close: _.bind($el.alert, $el, 'close')};
            if (delay) {
                _.delay(actions.close, delay);
            }
            return actions;
        };

    message.setup = function(options) {
        _.extend(defaults, options);
        while (queue.length) {
            var args = queue.shift();
            _.extend(args[1], showMessage.apply(null, args[0]));
        }
    };

    return Oro.NotificationMessage;
}));
