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
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:remove-attribute:after', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:add-attribute:after', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             *
             * We need to set values to null if they don't match the current selected locale or scope.
             * We can't directly delete them as the structure (scope/channel) is used for validation.
             * These unused values will be removed later in the back office.
             */
            render: function () {
                var selectedLocale = UserContext.get('catalogLocale');
                var selectedChannel = UserContext.get('catalogScope');
                var data = this.getFormData().values;

                data = _.mapObject(data, function (attributeValues) {
                    return _.map(attributeValues, function (value) {
                        if (null !== value.locale && selectedLocale !== value.locale) {
                            value.data = null;
                        }
                        if (null !== value.scope && selectedChannel !== value.scope) {
                            value.data = null;
                        }

                        return value;
                    });
                });

                this.setData({values: data}, {silent: true});

                var stringData = JSON.stringify(data, null, 0);
                $('#pim_enrich_mass_edit_choose_action_operation_values').val(stringData);
                $('#pim_enrich_mass_edit_choose_action_operation_attribute_locale').val(selectedLocale);
                $('#pim_enrich_mass_edit_choose_action_operation_attribute_channel').val(selectedChannel);

                return this;
            }
        });
    }
);
