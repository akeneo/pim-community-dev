'use strict';

define([
        'jquery',
        'oro/mediator'
    ], function (
        $,
        mediator
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
                    url: this.getUrl(code)
                }).then(function (entity) {
                    mediator.trigger('pim_enrich:form:entity:post_remove', code);

                    return entity;
                }.bind(this));
            },

            /**
             * Get the entity url
             *
             * @return {String}
             */
            getUrl: function () {
                throw new Error('This method need to be implemented');
            }
        };
    }
);
