'use strict';
/**
 * This service is in charge of storing and fetching the sequential edit collection
 * from the locale storage.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [],
    function () {
        return {
            /**
             * Set the collection with the given parameter
             *
             * @param {array} entities
             */
            set: function (entities) {
                sessionStorage.setItem('sequential_edit_entities', JSON.stringify(entities));
            },

            /**
             * Clear the locale storage
             */
            clear: function () {
                sessionStorage.setItem('sequential_edit_entities', JSON.stringify([]));
            },

            /**
             * Get the sequential edit collection
             *
             * @return {array}
             */
            get: function () {
                return null === sessionStorage.getItem('sequential_edit_entities') ?
                    [] :
                    JSON.parse(sessionStorage.getItem('sequential_edit_entities'));
            }
        };
    }
);
