'use strict';
/**
 * Category tab extension override to allow permission configuration
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
define(
    [
        'underscore',
        'pim/product-edit-form/categories',
        'pim/form'
    ],
    function (_, Categories, BaseForm) {
        return Categories.extend({
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: function () {
                        return this.getFormData().meta.is_owner;
                    }.bind(this),
                    label: _.__('pim_enrich.form.product.tab.categories.title')
                });

                // Don't call parent as it will override our 'isVisible' option
                return BaseForm.prototype.configure.apply(this, arguments);
            }
        });
    }
);
