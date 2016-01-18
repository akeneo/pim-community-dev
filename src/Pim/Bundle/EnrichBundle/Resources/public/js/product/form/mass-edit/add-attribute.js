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
        'pim/product-edit-form/attributes/add-attribute'
    ],
    function (
        $,
        _,
        BaseAddAttribute
    ) {
        return BaseAddAttribute.extend({
            /**
             * {@inheritdoc}
             */
            initialize: function () {
                BaseAddAttribute.prototype.initialize.apply(this, arguments);

                this.defaultOptions.placeholder = _.__('pim_enrich.form.product.mass_edit.select_attributes');
                this.defaultOptions.buttonTitle = _.__('pim_enrich.form.product.mass_edit.select');
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
