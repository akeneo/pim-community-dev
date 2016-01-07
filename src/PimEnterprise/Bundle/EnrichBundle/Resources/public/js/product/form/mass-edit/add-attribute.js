'use strict';

/**
 * Add attribute extension for mass edit common attributes.
 * It's an override of the "add attribute" extension since we need to reject unique attributes.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/attribute-manager',
        'pim/user-context',
        'pim/fetcher-registry',
        'pim/mass-product-edit-form/add-attribute'
    ],
    function (
        $,
        _,
        AttributeManager,
        UserContext,
        FetcherRegistry,
        BaseAddAttribute
    ) {
        return BaseAddAttribute.extend({
            /**
             * TODO we MUST exclude attribute we can't add (rights)
             *
             * {@inheritdoc}
             */
            getSelectSearchParameters: function (term, page) {
                var parameters = BaseAddAttribute.prototype.getSelectSearchParameters.apply(this, [term, page]);

                return $.extend(true, parameters, {
                    options: {
                        exclude_unique: 1,
                        editable: 1
                    }
                });
            }
        });
    }
);
