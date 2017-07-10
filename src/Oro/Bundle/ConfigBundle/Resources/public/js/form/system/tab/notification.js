

import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'oro/template/system/tab/notification';
import 'bootstrap.bootstrapswitch';
        export default BaseForm.extend({
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
                    label_yes: __('pim_enrich.form.entity.switch.yes'),
                    label_no: __('pim_enrich.form.entity.switch.no')
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
    
