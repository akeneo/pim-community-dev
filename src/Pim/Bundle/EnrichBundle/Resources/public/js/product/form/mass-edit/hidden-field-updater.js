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
        'jquery',
        'pim/form'
    ],
    function ($, BaseForm) {
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
             */
            render: function () {
                var data = this.getFormData().values;
                var stringData = JSON.stringify(data, null, 0);
                $('#pim_enrich_mass_edit_choose_action_operation_values').val(stringData);

                return this;
            }
        });
    }
);
