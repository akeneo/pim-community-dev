'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pim/template/product/meta/updated'
    ],
    function (_, BaseForm, mediator, formTemplate) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:update_state', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                var data = this.getFormData().values;
                var stringData = JSON.stringify(data, null, 0);
                $('#pim_enrich_mass_edit_choose_action_operation_values').val(stringData);

                console.log('data =', data);

                return this;
            }
        });
    }
);
