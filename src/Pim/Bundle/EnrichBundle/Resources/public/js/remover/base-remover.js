'use strict';

define([
        'jquery',
        'module',
        'oro/mediator',
        'routing'
    ], function (
        $,
        module,
        mediator,
        Routing
    ) {
        return {
            /**
             * Remove an entity
             *
             * @param {String} code
             *
             * @return {Promise}
             */
            remove: function (code) {
                return $.ajax({
                    type: 'DELETE',
                    url: this.getUrl(code),
                    data: { _method: 'DELETE' }
                }).then(function (entity) {
                    mediator.trigger('pim_enrich:form:entity:post_remove', code);

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
                return Routing.generate(module.config().url, {code: code});
            }
        };
    }
);
