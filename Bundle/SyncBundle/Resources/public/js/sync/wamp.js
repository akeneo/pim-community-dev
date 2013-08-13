/* jshint browser:true */
(function (factory) {
    "use strict";
    /* global define, jQuery, _, ab, Oro */
    if (typeof define === 'function' && define.amd) {
        define(['jQuery', '_', 'ab', 'OroSynchronizer'], factory);
    } else {
        factory(jQuery, _, ab, Oro.Synchronizer);
    }
}(function ($, _, ab, Synchronizer) {
    "use strict";
    var defaultOptions = {
            port: 80,
            debug: false
        },

        /**
         * Wraps callback in order to make it compatible with autobahn event callback
         */
        wrapCallback = function(callback) {
            var wrapper = function(channel, attributes) {
                callback(attributes);
            };
            wrapper.origCallback = callback;
            return wrapper;
        },

        /**
         * Handler on start connection
         * if list of subscriptions is not empty, auto subscribe all of them
         */
        onConnect = function(session){
            this.session = session;
            _.each(this.channels, function(callbacks, channel) {
                _.each(callbacks, function(callback) {
                    session.subscribe(channel, callback);
                });
            });
        },

        /**
         * Handler on losing connection
         */
        onHangup = function() {
            this.session = null;
        };

    /**
     * Synchronizer service build over WAMP (autobahn.js implementation)
     *
     * @constructor
     * @param {Object} options to configure service
     * @param {string} options.host is required
     * @param {number=} options.port default is 80
     * @param {number=} options.retryDelay time before next reconnection attempt, default is 5000 (5s)
     * @param {number=} options.maxRetries quantity of attempts before stop reconnection, default is 10
     * @param {boolean=} options.skipSubprotocolCheck, default is false
     * @param {boolean=} options.skipSubprotocolAnnounce, default is false
     * @param {boolean=} options.debug, default is false
     */
    Synchronizer.Wamp = function (options) {
        this.options = _.extend({}, defaultOptions, options);
        if (!this.options.host) {
            throw new Error('host option is required');
        }
        this.channels = {};
        if (this.options.debug) {
            ab.debug(true, true, true);
        }
        var wsuri = 'ws://' + this.options.host + ':' + this.options.port;
        ab.connect(wsuri, _.bind(onConnect, this), _.bind(onHangup, this), this.options);
    };

    Synchronizer.Wamp.prototype = {
        /**
         * Subscribes update callback function on a channel
         *
         * @param {string} channel is an URL which broadcasts updates
         * @param {function(Object)} callback is a function which accepts JSON
         *      with attributes' values and performs update
         */
        subscribe: function (channel, callback) {
            callback = wrapCallback(callback);
            (this.channels[channel] = this.channels[channel] || []).push(callback);
            if (this.session) {
                this.session.subscribe(channel, callback);
            }
        },

        /**
         * Removes subscription of update callback function for a channel
         *
         * @param {string} channel is an URL which broadcasts updates
         * @param {function(Object)=} callback an optional parameter,
         *      if was no function corresponded then removes all callbacks for a channel
         */
        unsubscribe: function (channel, callback) {
            var callbacks = this.channels[channel];
            if (!callbacks) {
                return;
            }
            if (callback) {
                // maps corresponded callback to a wrapped one
                callback = _.findWhere(callbacks, {origCallback: callback});
                // removes that callback from collection
                callbacks = this.channels[channel] = _.without(callbacks, callback);
            }
            if (!callbacks.length || !callback) {
                delete this.channels[channel];
            }
            if (this.session) {
                try {
                    this.session.unsubscribe(channel, callback);
                } catch (e) {}
            }
        }
    };

    return Synchronizer.Wamp;
}));
