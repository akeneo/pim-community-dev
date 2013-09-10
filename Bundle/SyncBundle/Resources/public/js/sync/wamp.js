/* jshint browser:true */
define(['jquery', 'underscore', 'backbone', 'autobahn'],
function ($, _, Backbone, ab) {
    'use strict';
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
            this.trigger('connection_established');
            _.each(this.channels, function(callbacks, channel) {
                _.each(callbacks, function(callback) {
                    session.subscribe(channel, callback);
                });
            });
        },

        /**
         * Handler on losing connection
         *
         * @param {number} code
         *      CONNECTION_CLOSED = 0
         *      CONNECTION_LOST = 1
         *      CONNECTION_RETRIES_EXCEEDED = 2
         *      CONNECTION_UNREACHABLE = 3
         *      CONNECTION_UNSUPPORTED = 4
         *      CONNECTION_UNREACHABLE_SCHEDULED_RECONNECT = 5
         *      CONNECTION_LOST_SCHEDULED_RECONNECT = 6
         * @param {string} msg text message
         * @param {Object} details
         * @param {number} details.delay in ms, before next reconnect attempt
         * @param {number} details.maxretries max number of attempts
         * @param {number} details.retries number of scheduled attempt
         */
        onHangup = function(code, msg, details) {
            this.trigger('connection_lost', _.extend({code: code}, details || {}));
            this.session = null;
        },

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
         *
         * @export  oro/sync/wamp
         * @class   oro.sync.Wamp
         */
        Wamp = function (options) {
            this.options = _.extend({}, defaultOptions, options);
            if (!this.options.host) {
                throw new Error('host option is required');
            }
            this.channels = {};
            if (this.options.debug) {
                ab.debug(true, true, true);
            }
            this.connect();
        };

    Wamp.prototype = {
        /**
         * Initiate connection process
         */
        connect: function() {
            if (!this.session) {
                var wsuri = 'ws://' + this.options.host + ':' + this.options.port;
                ab.connect(wsuri, _.bind(onConnect, this), _.bind(onHangup, this), this.options);
            }
        },

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

    _.extend(Wamp.prototype, Backbone.Events);

    return Wamp;
});
