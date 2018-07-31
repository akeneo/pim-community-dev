'use strict';
/**
 * State module for the mapping screen.
 * The goal of this module is to not detect state as changed if the value is changed from null to ''.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['pim/form/common/state'],
    function (BaseState) {
        return BaseState.extend({
            /**
             * {@inheritdoc}
             */
            hasModelChanged: function () {
                return JSON.stringify(this.emptyToNullValues(JSON.parse(this.state))) !==
                    JSON.stringify(this.emptyToNullValues(this.getFormData()));
            },

            /**
             * Transform '' values to null
             *
             * @param object: Object
             *
             * @returns Object
             */
            emptyToNullValues(object) {
                return Object.keys(object).reduce((accumulator, identifier) => {
                    accumulator[identifier] = object[identifier] === '' ? null : object[identifier];

                    return accumulator;
                }, {});
            }
        })
    }
);
