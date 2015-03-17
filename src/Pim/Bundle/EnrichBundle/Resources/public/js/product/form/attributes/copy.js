'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/tab/attribute/copy',
        'pim/product-edit-form/attributes/copy-field'
    ],
    function(_, BaseForm, template, CopyField) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'attribute-copy-actions',
            copyFields: {},
            copying: false,
            events: {
                'click .start-copying': 'startCopying',
                'click .stop-copying':  'stopCopying'
            },
            initialize: function() {
                this.copyFields = {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (this.copying) {
                    this.getParent().$el.addClass('comparision-mode');
                } else {
                    this.getParent().$el.removeClass('comparision-mode');
                }

                this.$el.html(
                    this.template({
                        state: this.getRoot().state.toJSON(),
                        'copying': this.copying
                    })
                );
                _.each(this.copyFields, _.bind(function (copyField) {
                    copyField.render();
                    copyField.field.addInfo('comparision', 'copy', copyField.$el);
                }, this));

                this.delegateEvents();
                this.$el.appendTo(this.getParent().$('.tab-content > header'));

                return this;
            },
            startCopying: function() {
                var locale = 'fr_FR';
                var scope  = 'mobile';
                this.copying = true;

                _.each(this.getParent().renderedFields, _.bind(function (field) {
                    if (field.attribute.scopable || field.attribute.localizable) {
                        var copyField = new CopyField(field.attribute);

                        copyField.setLocale(locale);
                        copyField.setChannel(scope);
                        copyField.setField(field);
                        copyField.setData('');

                        var values = field.getData();
                        _.each(values, function(value) {
                            if (value.scope === scope && value.locale === locale) {
                                copyField.setData(value.value);
                            }
                        });

                        this.copyFields[field.attribute.code] = copyField;
                    }
                }, this));

                this.render();
            },
            stopCopying: function() {
                this.copying = false;

                _.each(this.copyFields, _.bind(function(copyField) {
                    copyField.field.removeInfo('comparision', 'copy');
                    delete this.copyFields[copyField.field.attribute.code];
                }, this));

                this.render();
            }
        });
    }
);
