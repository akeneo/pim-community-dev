'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'text!pim/template/product/tab/attribute/copy',
        'pim/product-edit-form/attributes/copyfield',
        'pim/entity-manager',
        'pim/attribute-manager',
        'pim/product-manager',
        'pim/user-context'
    ],
    function ($, _, BaseForm, template, CopyField, EntityManager, AttributeManager, ProductManager, UserContext) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'attribute-copy-actions',
            copyFields: {},
            copying: false,
            locale: null,
            scope: null,
            events: {
                'click .start-copying': 'startCopying',
                'click .stop-copying': 'stopCopying',
                'click .select-all': 'selectAll',
                'click .select-all-visible': 'selectAllVisible',
                'click .copy': 'copy'
            },
            initialize: function () {
                this.copyFields = {};

                BaseForm.prototype.initialize.apply(this, arguments);
            },
            configure: function () {
                this.locale = UserContext.get('catalogLocale');
                this.scope  = UserContext.get('catalogScope');

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
                        'copying': this.copying
                    })
                );

                if (this.copying) {
                    _.each(this.copyFields, _.bind(function (copyField, code) {
                        var field = this.getParent().visibleFields[code];
                        if (field) {
                            copyField.setField(field);
                            copyField.field.addElement('comparision', 'copy', copyField);
                            copyField.field.render();
                        }

                    }, this));

                    this.renderExtensions();
                }

                this.delegateEvents();

                return this;
            },
            generateCopyFields: function () {
                this.copyFields = {};

                $.when(
                    EntityManager.getRepository('attribute').findAll(),
                    ProductManager.getValues(this.getData())
                ).done(_.bind(function (attributes, productValues) {
                    _.each(productValues, _.bind(function (values, code) {
                        var attribute = _.findWhere(attributes, {code: code});

                        if (attribute.scopable || attribute.localizable) {
                            var valueToCopy = AttributeManager.getValue(
                                values,
                                attribute,
                                this.locale,
                                this.scope
                            );

                            var copyField;
                            if (
                                this.copyFields[code] &&
                                this.copyFields[code].locale === valueToCopy.locale &&
                                this.copyFields[code].scope === valueToCopy.scope
                            ) {
                                copyField = this.copyFields[code];
                                copyField.setSelected(this.copyFields[code].selected);
                            } else {
                                copyField = new CopyField();
                            }

                            copyField.setLocale(valueToCopy.locale);
                            copyField.setScope(valueToCopy.scope);
                            copyField.setData(valueToCopy.value);

                            this.copyFields[code] = copyField;
                        }
                    }, this));
                }, this));
            },
            copy: function () {
                _.each(this.copyFields, function (copyField) {
                    if (copyField.selected && copyField.field && copyField.field.getEditable()) {
                        copyField.field.setCurrentValue(copyField.data);
                        copyField.selected = false;
                    }
                });

                this.getParent().render();
            },
            startCopying: function () {
                this.copying = true;
                this.generateCopyFields();

                this.render();
            },
            stopCopying: function () {
                this.copying = false;

                _.each(this.copyFields, _.bind(function (copyField) {
                    if (copyField.field) {
                        copyField.field.removeElement('comparision', 'copy');
                    }
                }, this));

                this.copyFields = {};
                this.render();
            },
            setLocale: function (locale) {
                this.locale = locale;

                this.generateCopyFields();
                this.render();
            },
            setScope: function (scope) {
                this.scope = scope;

                this.generateCopyFields();
                this.render();
            },
            selectAll: function () {
                _.each(this.copyFields, function (copyField) {
                    copyField.selected = true;
                });

                this.getParent().render();
            },
            selectAllVisible: function () {
                _.each(this.copyFields, _.bind(function (copyField, attributeCode) {
                    if (this.getParent().visibleFields[attributeCode]) {
                        copyField.selected = true;
                    }
                }, this));

                this.getParent().render();
            },
            getLocale: function () {
                return this.locale;
            },
            getScope: function () {
                return this.scope;
            }
        });
    }
);
