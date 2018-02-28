'use strict';

define(
    [
        'underscore',
        'pim/form',
        'oro/translator',
        'require-context'
    ],
    function (
        _,
        BaseForm,
        __,
        requireContext
    ) {
        return BaseForm.extend({
            className: 'AknCatalogVolume-axis',
            config: {
                icon: '',
                title: '',
                warning: false,
                warningText: 'Wow! You hit a record with this axis! Don\'t hesitate to contact us if you need any help with this kind of volume.',
                value: 0
            },

            initialize: function (options) {
                this.config = Object.assign({}, this.config, options.config);

                return BaseForm.prototype.initialize.apply(this, arguments);
            },
            /**
             * {@inheritdoc}
             */
            render() {
                const formData = this.getRoot().getFormData();
                const data = formData[this.config.code];
                const template = _.template(requireContext(this.config.template));

                this.$el.html(template({
                    icon: this.config.icon,
                    title: this.config.title,
                    warning: data.warning,
                    warningText: this.config.warningText,
                    value: data.value
                }));
            }
        });
    }
);
