'use strict';

define(
    [
        'underscore',
        'pim/form',
        'text!pim/template/product/tab/attribute/copy',
        'pim/product-edit-form/attributes/copyfield'
    ],
    function(_, BaseForm, template, CopyField) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'attribute-copy-actions',
            copyFields: {},
            copying: false,
            events: {
                'click .start-copying': 'startCopying',
                'click .stop-copying':  'stopCopying',
                'click .copy':  'copy'
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
                var locale = 'fr_FR';
                var scope  = 'mobile';

                if (this.copying) {
                    //This should be moved to somewhere else (can be done outside of render)
                    _.each(this.getData().values, _.bind(function (values, code) {
                        _.each(values, _.bind(function(value) {
                            if (
                                (
                                    value.scope === scope &&
                                    value.locale === locale
                                ) ||
                                (
                                    value.scope === scope &&
                                    value.locale === null
                                ) ||
                                (
                                    value.scope === null &&
                                    value.locale === locale
                                )
                            ) {
                                var copyField = new CopyField();

                                if (
                                    this.copyFields[code] &&
                                    this.copyFields[code].locale === value.locale &&
                                    this.copyFields[code].scope === value.scope
                                ) {
                                    copyField = this.copyFields[code];
                                    copyField.setSelected(this.copyFields[code].selected);
                                }

                                copyField.setLocale(value.locale);
                                copyField.setScope(value.scope);
                                copyField.setData(value.value);

                                this.copyFields[code] = copyField;
                            }
                        }, this));

                    }, this));

                    _.each(this.copyFields, _.bind(function (copyField, code) {
                        var field = this.getParent().renderedFields[code];
                        if (field) {
                            copyField.setField(field);
                            copyField.field.addElement('comparision', 'copy', copyField.render().$el);
                        }

                    }, this));
                }

                this.delegateEvents();
                this.$el.appendTo(this.getParent().$('.tab-content > header'));

                return this;
            },
            copy: function(){
                _.each(this.copyFields, function (copyField) {
                    if (copyField.field) {
                        copyField.field.setCurrentValue(copyField.data);
                    }
                });
            },
            startCopying: function() {
                this.copying = true;

                this.render();
            },
            stopCopying: function() {
                this.copying = false;

                _.each(this.copyFields, _.bind(function(copyField) {
                    if (copyField.field) {
                        copyField.field.removeElement('comparision', 'copy');
                    }
                }, this));

                this.copyFields = {};
                this.render();
            }
        });
    }
);
