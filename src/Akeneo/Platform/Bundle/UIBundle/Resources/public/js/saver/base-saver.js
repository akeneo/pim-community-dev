'use strict';

define([
        'jquery',
        'oro/mediator',
        'routing'
    ], function (
        $,
        mediator,
        Routing
    ) {
        return {
            /**
             * Save an entity
             *
             * @param {String} code
             * @param {Object} data
             * @param {String} method
             *
             * @return {Promise}
             */
            save: function (code, data, method) {
                return $.ajax({
                    /* todo: remove ternary when all instances using this module will provide method parameter */
                    type: 'undefined' === typeof method ? 'POST' : method,
                    url: this.getUrl(code),
                    data: JSON.stringify(data),
                    contentType: 'application/json; charset=UTF-8'
                }).then(function (entity) {
                    mediator.trigger('pim_enrich:form:entity:post_save', entity);

                    return entity;
                }.bind(this));
            },

            /**
             * Get the entity url
             * @param {String} code
             *
             * @return {String}
             */
            getUrl: function (code) {
                return Routing.generate(__moduleConfig.url, {code: code});
            }
        };
    }
);
