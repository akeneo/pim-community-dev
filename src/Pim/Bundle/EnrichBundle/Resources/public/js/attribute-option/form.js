'use strict';

define(
    [
        'underscore',
        'backbone',
        'pim/form',
        'text!pim/template/attribute-option/form',
        'pim/user-context',
        'pim/i18n'
    ],
    function (_, Backbone, BaseForm, template, UserContext, i18n) {
        return BaseForm.extend({
            template: _.template(template),
            events: {
                'change input': 'updateModel'
            },
            initialize: function () {
                this.model = new Backbone.Model();

                BaseForm.prototype.initialize.apply(this, arguments);
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

                this.model.set('code', this.$('input[name="code"]').val());
                this.model.set('optionValues', optionValues);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(
                    this.template({
                        locale: UserContext.get('catalogLocale'),
                        i18n: i18n,
                        option: this.getFormData()
                    })
                );

                return this.renderExtensions();
            }
        });
    }
);
