'use strict';

/**
 * Add attribute extension for mass edit common attributes.
 *
 * It's an override on the "add attribute" extension since we need to reject
 * unique attributes.
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
        'pim/product-edit-form/attributes/add-attribute'
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
             * {@inheritdoc}
             */
            initialize: function () {
                this.defaultOptions.placeholder = _.__('pim_enrich.form.product.mass_edit.select_attributes');
                this.defaultOptions.buttonTitle = _.__('pim_enrich.form.product.mass_edit.select');

                BaseAddAttribute.prototype.initialize.apply(arguments);
            },

            /**
             * {@inheritdoc}
             */
            getSelectSearchParameters: function (term, page) {
                var parameters = BaseAddAttribute.prototype.getSelectSearchParameters.apply(this, [term, page]);

                return $.extend(true, parameters, {
                    options: {
                        exclude_unique: 1
                    }
                });
            }
        });
    }
);
