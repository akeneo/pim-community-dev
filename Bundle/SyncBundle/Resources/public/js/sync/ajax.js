/* jshint browser:true */
(function (factory) {
    "use strict";
    /* global define, jQuery, _, Oro */
    if (typeof define === 'function' && define.amd) {
        define(['JSON', 'jQuery', '_', 'OroSynchronizer'], factory);
    } else {
        factory(JSON, jQuery, _, Oro.Synchronizer);
    }
}(function (JSON, $, _, Synchronizer) {
    "use strict";

    var defaultOptions = {
            waitUpdate: 5000,
            waitSubscription: 500,
            maxRetries: 10
        },

        /**
         * Invokes doFetchUpdates function with a delay (which is configured via options)
         */
        fetchUpdates = function () {
            this.updater = _.delay(_.bind(doFetchUpdates, this), this.options.waitUpdate);
        },

        /**
         * Collects all subscribed channels and makes request for updates.
         * If there's no subscribed channels for the moment, then just invoke fetchUpdates(),
         * to be executed again after a while.
         */
        doFetchUpdates = function () {
            if (_.isEmpty(this.channels) ) {
                fetchUpdates.call(this);
                return;
            }
            var channels = this.channels,
                payload = _.chain(channels)
                    .filter(function (obj) {
                        return obj.token !== '' && obj.token !== '-';
                    })
                    .map(function (obj) {
                        return obj.token;
                    })
                    .value();
            $.ajax({
                url: this.options.url,
                type: "POST",
                data: {
                    action: 'fetchUpdates',
                    payload: JSON.stringify(payload)
                },
                success: function (payload) {
                    _.each(payload, function (obj) {
                        if (!obj.channel || !obj.attributes) {
                            return;
                        }
                        var channel = channels[obj.channel];
                        if (channel) {
                            _.each(channel.callbacks, function(callback){
                                callback(obj.attributes);
                            });
                        }
                    });
                },
                complete: _.bind(fetchUpdates, this)
            });
        },

        /**
         * Invokes doSubscribe function but with a delay (which is configured via options)
         *
         * @param {number=} attempt number of attempts which service have to do after error occurred
         */
        subscribe = function (attempt) {
            if (!this.subscriber) {
                attempt = attempt || 0;
                this.subscriber = _.delay(_.bind(doSubscribe, this), this.options.waitSubscription, attempt);
            }
        },

        /**
         * Collect all channels which don't have tokens and makes subscribe request
         *
         * @param {number} attempt number of attempts which service have to do after error occurred
         */
        doSubscribe = function (attempt) {
            var channels = this.channels,
                payload = _.chain(channels)
                    .filter(function (obj) {
                        return obj.token === '' ? Boolean(obj.token = '-') : false;
                    })
                    .map(function (obj) {
                        return obj.channel;
                    })
                    .value();
            delete this.subscriber;
            $.ajax({
                url: this.options.url,
                type: "POST",
                data: {
                    action: 'subscribe',
                    payload: JSON.stringify(payload)
                },
                success: function (payload) {
                    _.each(payload, function (obj) {
                        if (!obj.channel) {
                            return;
                        }
                        if (obj.token) {
                            channels[obj.channel].token = obj.token;
                        } else {
                            // assumed user have no permission to listening to this channel
                            delete channels[obj.channel];
                        }
                    });
                },
                error: function () {
                    // on error remove all tokens for sent channels
                    _.each(payload, function (channel) {
                        channels[channel].token = '';
                    });
                },
                complete: _.bind(function (xhr, status) {
                    attempt = status === 'error' ? attempt + 1 : 0;
                    if (attempt <= this.options.maxRetries && (attempt || _.some(channels, function (obj) {
                        return obj.token === '';
                    }))) {
                        subscribe.call(this, attempt);
                    }
                }, this)
            });
        };

    /**
     * Synchronizer service build over AJAX
     *
     * @constructor
     * @param {Object} options to configure service
     * @param {string} options.url is required
     * @param {number=} options.waitUpdate default is 5000 (5s)
     * @param {number=} options.waitSubscription is time before actual subscribe request after first
     *      subscribe call, default is 500 (1/2s). During this time, service waits for more
     *      subscribers and after it makes a request to subscribe them all ta ones
     * @param {number=} options.maxRetries quantity of attempts before stop trying
     *      to subscribe after an error response received, default is 10
     */
    Synchronizer.Ajax = function (options) {
        this.options = _.extend({}, defaultOptions, options);
        if (!this.options.url) {
            throw new Error('URL option is required');
        }
        this.channels = {};
        fetchUpdates.call(this);
    };

    Synchronizer.Ajax.prototype = {
        /**
         * Subscribes update callback function on a channel
         *
         * @param {string} channel is an URL which broadcasts updates
         * @param {function(Object)} callback is a function which accepts JSON
         *      with attributes' values and performs update
         */
        subscribe: function (channel, callback) {
            var obj = this.channels[channel];
            if (!obj) {
                this.channels[channel] = {
                    channel: channel,
                    callbacks: [callback],
                    token: ''
                };
                subscribe.call(this);
            } else {
                obj.callbacks.push(callback);
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
            var obj = this.channels[channel];
            if (obj) {
                if (callback) {
                    obj.callbacks = _.without(obj.callbacks, callback);
                }
                if (!obj.callbacks.length || !callback) {
                    delete this.channels[channel];
                }
            }
        }
    };

    return Synchronizer.Ajax;
}));


