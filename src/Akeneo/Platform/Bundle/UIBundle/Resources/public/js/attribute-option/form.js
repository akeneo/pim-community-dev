'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'backbone',
        'pim/form',
        'pim/template/attribute-option/form',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, __, Backbone, BaseForm, template, UserContext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change input': 'updateModel'
            },
            updateModel: function () {
                var optionValues = {};

                _.each(this.$('input[name^="label-"]'), function (labelInput) {
                    var locale = labelInput.dataset.locale;
                    optionValues[locale] = {
                        locale: locale,
                        value: labelInput.value
                    };
                });

                this.getFormModel().set('code', this.$('input[name="code"]').val());
                this.getFormModel().set('optionValues', optionValues);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        locale: UserContext.get('catalogLocale'),
                        i18n: i18n,
                        option: this.getFormData(),
                        __
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
