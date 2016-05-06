'use strict';
/**
 * Add attribute extension for variant groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/common/add-attribute'
    ],
    function (
        _,
        AddAttribute
    ) {
        return AddAttribute.extend({

            /**
             * {@inheritdoc}
             */
            getExcludedAttributes: function () {
                var entity = this.getFormData();

                return AddAttribute.prototype.getExcludedAttributes.apply(this, arguments).then(
                    function (excludedAttributes) {
                        return _.union(
                            excludedAttributes,
                            entity.axis
                        );
                    }
                );
            }
        });
    }
);
