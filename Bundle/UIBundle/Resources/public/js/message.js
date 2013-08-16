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
            container: '#flash-messages .flash-messages-holder',
            delay: false,
            template: _.template(
                '<div class="alert <% if (type) { %><%= \'alert-\' + type %><% } %> fade in top-messages ">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    '<div class="message"><%= message %></div>'+
                '</div>'
            )
        },

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
         */
        message = Oro.NotificationMessage = function(type, message, options) {
            // if message is an array assume this is arguments for translation function
            if (_.isArray(message)) {
                message = _.__.apply(_, message);
            }
            var opt = _.extend({}, defaults, options || {}),
                $el = jQuery(opt.template({type: type, message: message})).appendTo(opt.container),
                delay = opt.delay || (opt.flash && 5000);
            if (delay) {
                _.delay(_.bind($el.alert, $el, 'close'), delay);
            }
        };

    message.setup = function(options) {
        _.extend(defaults, options);
    };

    return Oro.NotificationMessage;
}));
