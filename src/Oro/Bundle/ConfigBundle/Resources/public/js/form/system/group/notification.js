"use strict";

define([
        'pim/form',
        'text!oro/template/system/group/notification',
        'bootstrap.bootstrapswitch'
    ],
    function(
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            className: 'tab-pane',
            events: {
                'change input[type="checkbox"]': 'updateModel'
            },
            isGroup: true,
            label: _.__('oro_config.form.config.group.notification.title'),
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    'notification': this.getFormData()['pim_analytics___version_update'].value
                }));

                this.$('.switch').bootstrapSwitch();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateModel: function (event) {
                var data = this.getFormData();
                data['pim_analytics___version_update'].value = $(event.target).prop('checked') ? '1' : '0';
                this.setData(data);
            }
        });
    }
);
