'use strict';

define(['module', 'jquery', 'underscore'], function (module, $, _) {
    return {
        repositories: {},
        initializePromise: null,
        initialize: function () {
            if (null === this.initializePromise) {
                var deferred = $.Deferred();
                var repositories = {};

                _.each(module.config().repositories, function (config, name) {
                    config = _.isString(config) ? { module: config } : config;
                    config.options = config.options || {};
                    repositories[name] = config;
                });

                require(_.pluck(repositories, 'module'), _.bind(function () {
                    _.each(repositories, function (repository) {
                        repository.loadedModule = new (require(repository.module))(repository.options);
                    });

                    this.repositories = repositories;
                    deferred.resolve();
                }, this));

                this.initializePromise = deferred.promise();
            }

            return this.initializePromise;
        },
        getRepository: function (entityType) {
            return (this.repositories[entityType] || this.repositories['default']).loadedModule;
        },
        clear: function (entityType, entity) {
            return this.getRepository(entityType).clear(entity);
        }
    };
});
