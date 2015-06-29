'use strict';
/**
 * Field abstract class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'jquery',
        'backbone',
        'underscore',
        'text!pim/template/product/field/field',
        'pim/attribute-manager',
        'pim/i18n',
        'oro/mediator'
    ],
    function ($, Backbone, _, fieldTemplate, AttributeManager, i18n, mediator) {
        var FieldModel = Backbone.Model.extend({
            values: []
        });

        return Backbone.View.extend({
            tagName: 'div',
            className: 'field-container',
            options: {},
            attributes: function () {
                return {
                    'data-attribute': this.options ? this.options.code : null
                };
            },
            attribute: null,
            context: {},
            model: FieldModel,
            template: _.template(fieldTemplate),
            elements: {},
            htmlClasses: [],
            editable: true,
            ready: true,
            valid: true,
            initialize: function (attribute) {
                this.attribute   = attribute;
                this.model       = new FieldModel({values: []});
                this.elements    = {};
                this.context     = {};
                this.htmlClasses = [];

                return this;
            },
            render: function () {
                this.setEditable(true);
                var promises = [];
                mediator.trigger('field:extension:add', {'field': this, 'promises': promises});

                $.when.apply($, promises)
                    .then(_.bind(function () {
                        return this.getTemplateContext();
                    }, this))
                    .then(_.bind(function (templateContext) {
                        this.$el.html(this.template(templateContext));
                        this.$('.form-field .field-input').append(this.renderInput(templateContext));

                        _.each(this.elements, _.bind(function (elements, position) {
                            var $container = this.$('.' + position + '-elements-container');
                            $container.empty();
                            _.each(elements, _.bind(function (element) {
                                if (typeof element.render === 'function') {
                                    $container.append(element.render().$el);
                                } else {
                                    $container.append(element);
                                }
                            }, this));
                        }, this));
                        this.postRender();
                        this.delegateEvents();
                    }, this));

                return this;
            },
            renderInput: function () {
                throw new Error('You should implement your field template');
            },
            postRender: function () {},
            renderCopyInput: function (context, locale, scope) {
                context.value = AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    locale,
                    scope
                );
                context.editMode = 'view';

                return this.renderInput(context);
            },
            getTemplateContext: function () {
                var deferred = $.Deferred();

                deferred.resolve({
                    classAttr: this.renderClassAttr(),
                    type: this.attribute.field_type,
                    label: this.attribute.label[this.context.uiLocale] ?
                        this.attribute.label[this.context.uiLocale] :
                        this.attribute.code,
                    value: this.getCurrentValue(),
                    context: this.context,
                    attribute: this.attribute,
                    info: this.elements,
                    editMode: this.getEditMode(),
                    i18n: i18n
                });

                return deferred.promise();
            },
            updateModel: function () {
                this.valid = true;
            },
            setValues: function (values) {
                if (values.length === 0) {
                    /*global alert: true */
                    alert('value array is empty');
                }

                this.model.set('values', values);
            },
            setContext: function (context) {
                this.context = context;
            },
            addElement: function (position, code, element) {
                if (!this.elements[position]) {
                    this.elements[position] = {};
                }
                this.elements[position][code] = element;
            },
            removeElement: function (position, code) {
                if (this.elements[position] && this.elements[position][code]) {
                    delete this.elements[position][code];
                }
            },
            setValid: function (valid) {
                this.valid = valid;
            },
            isValid: function () {
                return this.valid;
            },
            setFocus: function () {
                this.$('input:first').focus();
            },
            setEditable: function (editable) {
                this.editable = editable;
            },
            isEditable: function () {
                return this.editable;
            },
            setReady: function (ready) {
                this.ready = ready;
            },
            isReady: function () {
                return this.ready;
            },
            getEditMode: function () {
                if (this.editable) {
                    return 'edit';
                } else {
                    return 'view';
                }
            },
            getCurrentValue: function () {
                return AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    this.context.locale,
                    this.context.scope
                );
            },
            setCurrentValue: function (value) {
                var productValue = this.getCurrentValue();

                productValue.data = value;
                mediator.trigger('entity:form:edit:update_state');
            },
            addHtmlClass: function (className) {
                if (-1 === this.htmlClasses.indexOf(className)) {
                    this.htmlClasses.push(className);
                }
            },
            renderClassAttr: function () {
                var classes = this.htmlClasses;
                classes.push(this.attribute.field_type);
                classes.push(this.getEditMode());

                return _.uniq(classes).join(' ');
            }
        });
    }
);
