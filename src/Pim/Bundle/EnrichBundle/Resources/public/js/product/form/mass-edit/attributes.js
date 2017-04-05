'use strict';

/**
 * Override of the attributes module.
 *
 * Purpose of this override is to avoid XHR call when removing an attribute
 * from the Product Edit Form (as we simply want to remove it from the DOM).
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/field-manager',
        'pim/security-context',
        'pim/form/common/attributes',
        'oro/mediator'
    ],
    function (
        FieldManager,
        SecurityContext,
        BaseAttributes,
        mediator
    ) {
        return BaseAttributes.extend({
            locked: false,

            /**
             * Listen to mass edit form unlock and lock events
             *
             * {@inheritdoc}
             */
            configure: function () {
                mediator.on('mass-edit:form:lock', this.onLock.bind(this));
                mediator.on('mass-edit:form:unlock', this.onUnlock.bind(this));

                return BaseAttributes.prototype.configure.apply(this, arguments);
            },

            /**
             * Override for field render to maintain form locked state
             * @param  {jQueryElement} panel Attribute panel element
             * @param  {Object} field Attribute field
             */
            appendField: function (panel, field) {
                if (field.canBeSeen()) {
                    field.setLocked(this.locked);
                    field.render();
                    FieldManager.addVisibleField(field.attribute.code);
                    panel.append(field.$el);
                }
            },

            /**
             * Set mass edit form as locked
             *
             * {@inheritdoc}
             */
            onLock: function () {
                this.locked = true;
            },

            /**
             * Set mass edit form as unlocked
             *
             * {@inheritdoc}
             */
            onUnlock: function () {
                this.locked = false;
                this.render();
            },

            /**
             * {@inheritdoc}
             */
            removeAttribute: function (event) {
                if (!SecurityContext.isGranted('pim_enrich_product_remove_attribute')) {
                    return;
                }
                var attributeCode = event.currentTarget.dataset.attribute;
                var product = this.getFormData();
                var fields = FieldManager.getFields();

                this.triggerExtensions('add-attribute:update:available-attributes');

                delete product.values[attributeCode];
                delete fields[attributeCode];

                this.setData(product);
                this.getRoot().trigger('pim_enrich:form:remove-attribute:after');

                this.render();
            }
        });
    }
);
