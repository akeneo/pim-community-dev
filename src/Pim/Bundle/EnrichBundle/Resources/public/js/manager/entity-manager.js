'use strict';

define(['module', 'jquery', 'underscore'], function (module, $, _) {
    return {
        repositories: {},
        initialize: function () {
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

            return deferred.promise();
        },
        getRepository: function (entityType) {
            return (this.repositories[entityType] || this.repositories['default']).loadedModule;
        },
        clear: function (entityType, entity) {
            return this.getRepository(entityType).clear(entity);
        }
    };
});
