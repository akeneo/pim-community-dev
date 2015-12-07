'use strict';

define(
    [
        'pim/form',
    ],
    function (BaseForm) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);
                this.listenTo(this.getRoot(), 'pim_enrich:form:remove-attribute:after', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var data = this.getFormData().values;
                var stringData = JSON.stringify(data, null, 0);
                $('#pim_enrich_mass_edit_choose_action_operation_values').val(stringData);

                return this;
            }
        });
    }
);
