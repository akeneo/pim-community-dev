'use strict';

define([
        'underscore',
        'jquery',
        'pim/form',
        'pim/template/system/group/loading-message',
        'bootstrap.bootstrapswitch'
    ],
    function (
        _,
        $,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            events: {
                'change input[type="checkbox"]': 'updateBoolean',
                'change textarea': 'updateText'
            },
            isGroup: true,
            label: _.__('oro_config.form.config.group.loading_message.title'),
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({
                    'loading_message_enabled': this.getFormData().pim_ui___loading_message_enabled.value,
                    'loading_messages': this.getFormData().pim_ui___loading_messages.value
                }));

                this.$el.find('.switch').bootstrapSwitch();

                this.delegateEvents();

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateBoolean: function (event) {
                var data = this.getFormData();
                data.pim_ui___loading_message_enabled.value = $(event.target).prop('checked') ? '1' : '0';
                this.setData(data);
            },

            /**
             * Update model after value change
             *
             * @param {Event}
             */
            updateText: function (event) {
                var data = this.getFormData();
                data.pim_ui___loading_messages.value = $(event.target).val();
                this.setData(data);
            }
        });
    }
);
