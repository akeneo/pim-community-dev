"use strict";

define([
    'underscore',
        'oro/translator',
        'pim/form',
        'oro/template/system/tab/notification',
        'bootstrap.bootstrapswitch'
    ],
    function(
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            events: {
                'change input[type="checkbox"]': 'updateModel'
            },
            isGroup: true,
            label: __('oro_config.form.config.group.notification.title'),
            template: _.template(template),
            code: 'oro_config_notification',

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: this.label
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    notification: this.getFormData()['pim_analytics___version_update'].value,
                    label_system_notification: __('oro_config.form.config.group.notification.system_notification'),
                    label_yes: __('pim_common.yes'),
                    label_no: __('pim_common.no')
                }));

                this.$('.switch').bootstrapSwitch();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event} event
             */
            updateModel: function (event) {
                var data = this.getFormData();
                data['pim_analytics___version_update'].value = $(event.target).prop('checked') ? '1' : '0';
                this.setData(data);
            }
        });
    }
);
