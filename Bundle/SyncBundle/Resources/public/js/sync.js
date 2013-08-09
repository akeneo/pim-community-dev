/* jshint browser:true */
(function (factory) {
    "use strict";
    /* global define, Oro, _, Backbone */
    if (typeof define === 'function' && define.amd) {
        define(['Oro', '_', 'Backbone'], factory);
    } else {
        factory(Oro, _, Backbone);
    }
}(function (Oro, _, Backbone) {
    "use strict";
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
                service.subscribe(_.result(model, 'url'), _.bind(model.set, model));
                model.on('remove', unsubscribeModel);
            }
        },

        /**
         * Removes subscription for a provided model
         * @param {Backbone.Model} model
         */
        unsubscribeModel = function (model) {
            if (model.id) {
                service.unsubscribe(_.result(model, 'url'));
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

    return Oro.Synchronizer;
}));
