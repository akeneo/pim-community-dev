'use strict';
/**
 * Sequential edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [],
    function () {
        return {
            set: function (entities) {
                sessionStorage.setItem('sequential_edit_entities', JSON.stringify(entities));
            },

            clear: function () {
                sessionStorage.setItem('sequential_edit_entities', JSON.stringify([]));
            },

            get: function () {
                return JSON.parse(
                    null === sessionStorage.getItem('sequential_edit_entities') ?
                    [] :
                    sessionStorage.getItem('sequential_edit_entities')
                );
            }
        };
    }
);
