'use strict';
/**
 * This module sets parent attributes as read only
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form'
    ],
    function (
        _,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            addFieldExtension: function (event) {
                var productModel = this.getFormData();
                if (!productModel.meta.attributes_for_this_level) {
                    return;
                }

                var levelAttributeCodes = productModel.meta.attributes_for_this_level;
                var field = event.field;

                if (!_.contains(levelAttributeCodes, field.attribute.code)) {
                    field.setEditable(false);
                }

                return this;
            }
        });
    }
);
