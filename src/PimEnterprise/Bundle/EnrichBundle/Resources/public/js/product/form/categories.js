'use strict';
/**
 * Category tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'pim/product-edit-form/categories',
        'pim/form'
    ],
    function (Categories, BaseForm) {
        return Categories.extend({
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: _.bind(function () { return this.getFormData().meta.is_owner }, this),
                    label: _.__('pim_enrich.form.product.tab.categories.title')
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            }
        });
    }
);
