'use strict';

define(['underscore', 'pim/user-context', 'pim/i18n'], function (_, UserContext, i18n) {
    return {
        /**
         * Format a collection of entities into a list of choices as follows.
         * From :
         * [
         *     {
         *         code: 'webcams',
         *         labels: {
         *             en_US:'Webcams',
         *             fr_FR:'Webcams',
         *             de_DE:'Webcams'
         *         }
         *     },
         *     {
         *         code: 'mugs',
         *         labels: {
         *             en_US: 'Mugs',
         *             fr_FR: 'Chopes\/Mugs',
         *             de_DE: 'Tassen'
         *         }
         *     }
         * ]
         *
         * to (for locale "de_DE") :
         *
         * [
         *     { id: 'webcams', text: 'Webcams' },
         *     { id: 'mugs', text: 'Tassen' }
         * ]
         *
         * @param {Array} entities
         *
         * @return {Array}
         */
        format: function (entities) {
            var choices = [];
            _.each(entities, function (entity) {
                choices.push(this.formatOne(entity));
            }.bind(this));

            return choices;
        },

        /**
         * Format an entity into a choice as follows.
         * From :
         * {
         *     code: 'webcams',
         *     label: {
         *         en_US:'Webcams',
         *         fr_FR:'Webcams',
         *         de_DE:'Webcams'
         *     }
         * }
         *
         * to (for locale "de_DE") :
         *
         * { id: 'webcams', text: 'Webcams' }
         *
         * @param {Object} entity
         *
         * @return {Object}
         */
        formatOne: function (entity) {
            return {
                id: entity.code,
                text: i18n.getLabel(entity.labels, UserContext.get('catalogLocale'), entity.code)
            };
        }
    };
});
