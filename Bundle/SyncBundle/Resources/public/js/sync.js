/* jshint browser:true */
(function (factory) {
    'use strict';
    /* global define, jQuery, _, Backbone, Oro */
    if (typeof define === 'function' && define.amd) {
        define(['jQuery', '_', 'Backbone', 'Oro', 'OroNotificationMessage'], factory);
    } else {
        factory(jQuery, _, Backbone, Oro, Oro.NotificationMessage);
    }
}(function ($, _, Backbone, Oro, message) {
    'use strict';
    var service,

        /**
         * Oro.Synchronizer - saves provided sync service internally and
         * exists as base namespace for public methods
         *
         * @param {Object} serv service which provides backend synchronization
         * @param {Function} serv.subscribe
         * @param {Function} serv.unsubscribe
         * @returns Oro.Synchronizer
         *
         * @var {Function} sync protected shortcut for Oro.Synchronizer
         */
        sync = Oro.Synchronizer = function (serv) {
            if (!(_.isObject(serv) && _.isFunction(serv.subscribe) && _.isFunction(serv.unsubscribe))) {
                throw new Error('Synchronization service does not fit requirements');
            }
            service = serv;
            var onConnectionEstablished = function(){
                message('success', ['sync.connection.established'], {flash: true});
            };
            service.on('connection_lost', function(data){
                data = data || {};
                var attempt = data.retries || 0;
                message('error', ['sync.connection.lost', data, attempt], {flash: Boolean(attempt)});
                service.off('connection_established', onConnectionEstablished)
                    .once('connection_established', onConnectionEstablished);
            });
            return sync;
        },

        /**
         * Checks if backend synchronization service is defined, if not - throws Error
         * @throws Error
         */
        checkService = function() {
            if (_.isUndefined(service)) {
                throw new Error('Synchronization service is not defined');
            }
        },

        /**
         * Subscribes provided model on update event
         * @param {Backbone.Model} model
         */
        subscribeModel = function (model) {
            if (model.id) {
                // saves bound function in order to have same callback in unsubscribeModel call
                model['[[SetCallback]]'] = (model['[[SetCallback]]'] || _.bind(model.set, model));
                service.subscribe(_.result(model, 'url'), model['[[SetCallback]]']);
                model.on('remove', unsubscribeModel);
            }
        },

        /**
         * Removes subscription for a provided model
         * @param {Backbone.Model} model
         */
        unsubscribeModel = function (model) {
            if (model.id) {
                var args = [_.result(model, 'url')];
                if (_.isFunction(model['[[SetCallback]]'])) {
                    args.push(model['[[SetCallback]]']);
                }
                service.unsubscribe.apply(service, args);
            }
        },

        events = {
            add: subscribeModel,
            error: function (collection) {
                _.each(collection.models, unsubscribeModel);
            },
            reset: function(collection, options) {
                _.each(options.previousModels, function(model) {
                    model.urlRoot = collection.url;
                    unsubscribeModel(model);
                });
                _.each(collection.models, subscribeModel);
            }
        };

    /**
     * Establish connection with server and updates a provided object instantly
     *
     * @param {Backbone.Collection|Backbone.Model} obj
     * @returns {Oro.Synchronizer}
     */
    sync.keepRelevant = function (obj) {
        checkService();
        if (obj instanceof Backbone.Collection) {
            _.each(obj.models, subscribeModel);
            obj.on(events);
        } else if (obj instanceof Backbone.Model) {
            subscribeModel(obj);
        }
        return this;
    };

    /**
     * Drops instant update connection for provided object
     *
     * @param {Backbone.Collection|Backbone.Model} obj
     * @returns {Oro.Synchronizer}
     */
    sync.stopTracking = function (obj) {
        checkService();
        if (obj instanceof Backbone.Collection) {
            _.each(obj.models, unsubscribeModel);
            obj.off(events);
        } else if (obj instanceof Backbone.Model) {
            unsubscribeModel(obj);
        }
        return this;
    };

    /**
     * Makes service to give a try to connect to server
     */
    sync.reconnect = function() {
        service.connect();
    };

    return Oro.Synchronizer;
}));
