'use strict';

/**
 * Mass Edit Common Attributes exclusive module.
 *
 * It listens to any change on the Product Edit Form and update accordingly an
 * hidden field that contains the JSON value of the whole form.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'jquery',
        'pim/form',
        'pim/user-context'
    ],
    function (_, $, BaseForm, UserContext) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.triggerModelUpdate);
                this.listenTo(this.getRoot(), 'pim_enrich:form:remove-attribute:after', this.triggerModelUpdate);
                this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.triggerModelUpdate);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             *
             * We need to set values to null if they don't match the current selected locale or scope.
             * We can't directly delete them as the structure (scope/channel) is used for validation.
             * These unused values will be removed later in the back office.
             */
            triggerModelUpdate: function () {
                var values = _.mapObject(this.getFormData().values, function (attributeValues) {
                    return _.map(attributeValues, function (value) {
                        if (null !== value.locale && UserContext.get('catalogLocale') !== value.locale) {
                            value.data = null;
                        }
                        if (null !== value.scope && UserContext.get('catalogScope') !== value.scope) {
                            value.data = null;
                        }

                        return value;
                    });
                });
                this.setData({values: values}, {silent: true});

                this.getRoot().trigger('pim_enrich:mass_edit:model_updated', {values: values});

                return this;
            }
        });
    }
);
