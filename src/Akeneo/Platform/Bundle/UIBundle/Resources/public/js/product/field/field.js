'use strict';
/**
 * Field abstract class
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'backbone',
        'underscore',
        'pim/template/product/field/field',
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
            className: 'AknComparableFields field-container',
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
            editable: true,
            ready: true,
            valid: true,
            locked: false,

            /**
             * Initialize this field
             *
             * @param {Object} attribute
             *
             * @returns {Object}
             */
            initialize: function (attribute) {
                this.attribute = attribute;
                this.model = new FieldModel({values: []});
                this.elements = {};
                this.context = {};

                return this;
            },

            /**
             * Render this field
             *
             * @returns {Object}
             */
            render: function () {
                this.setEditable(!this.locked);
                this.setValid(true);
                this.elements = {};
                var promises = [];
                mediator.trigger('pim_enrich:form:field:extension:add', {'field': this, 'promises': promises});

                $.when.apply($, promises)
                    .then(this.getTemplateContext.bind(this))
                    .then(function (templateContext) {
                        this.$el.html(this.template(templateContext));
                        this.$('.original-field .field-input').append(this.renderInput(templateContext));

                        this.renderElements();
                        this.postRender();
                        this.delegateEvents();
                    }.bind(this));

                return this;
            },

            /**
             * Render elements of this field in different available positions
             */
            renderElements: function () {
                _.each(this.elements, function (elements, position) {
                    var $container = 'field-input' === position ?
                        this.$('.original-field .field-input') :
                        this.$('.' + position + '-elements-container');

                    $container.empty();

                    _.each(elements, function (element) {
                        if (typeof element.render === 'function') {
                            $container.append(element.render().$el);
                        } else {
                            $container.append(element);
                        }
                    }.bind(this));

                }.bind(this));
            },

            /**
             * Render the input inside the field area
             *
             * @throws {Error} if this method is not implemented
             */
            renderInput: function () {
                throw new Error('You should implement your field template');
            },

            /**
             * Is called after rendering the input
             */
            postRender: function () {},

            /**
             * Render this input in copy mode
             *
             * @param {Object} value
             *
             * @returns {Promise}
             */
            renderCopyInput: function (value) {
                return this.getTemplateContext()
                    .then(function (context) {
                        if (undefined === value) {
                            return null;
                        }

                        var copyContext = $.extend(true, {}, context);
                        copyContext.value = value;
                        copyContext.context.locale = value.locale;
                        copyContext.context.scope = value.scope;
                        copyContext.editMode = 'view';

                        return this.renderInput(copyContext);
                    }.bind(this));
            },

            /**
             * Get the template context
             *
             * @returns {Promise}
             */
            getTemplateContext: function () {
                var deferred = $.Deferred();

                deferred.resolve({
                    type: this.attribute.field_type,
                    label: this.getLabel(),
                    value: this.getCurrentValue(),
                    fieldId: 'field-' + Math.random().toString(10).substring(2),
                    context: this.context,
                    attribute: this.attribute,
                    info: this.elements,
                    editMode: this.getEditMode(),
                    i18n: i18n,
                    locale: this.attribute.localizable ? this.context.locale : null,
                    scope: this.attribute.scopable ? this.context.scope : null
                });

                return deferred.promise();
            },

            /**
             * Update the model linked to this field
             */
            updateModel: function () {
                this.valid = true;
            },

            /**
             * Set values to the model linked to this field
             *
             * @param {Array} values
             */
            setValues: function (values) {
                if (_.isUndefined(values) || values.length === 0) {
                    console.error('Value array is empty');
                }

                this.model.set('values', values);
            },

            /**
             * Set the context of this field
             *
             * @param {Object} context
             */
            setContext: function (context) {
                this.context = context;
            },

            /**
             * Add an element to this field block
             *
             * @param {string} position 'footer', 'label' or 'comparison'
             * @param {string} code
             * @param {Object} element
             */
            addElement: function (position, code, element) {
                if (!this.elements[position]) {
                    this.elements[position] = {};
                }
                this.elements[position][code] = element;
            },

            /**
             * Remove an element of this field block, with the given position & code
             *
             * @param {string} position
             * @param {string} code
             */
            removeElement: function (position, code) {
                if (this.elements[position] && this.elements[position][code]) {
                    delete this.elements[position][code];

                    if (_.isEmpty(this.elements[position])) {
                        delete this.elements[position];
                    }
                }
            },

            /**
             * Set as valid
             *
             * @param {boolean} valid
             */
            setValid: function (valid) {
                this.valid = valid;
            },

            /**
             * Return whether is valid
             *
             * @returns {boolean}
             */
            isValid: function () {
                return this.valid;
            },

            /**
             * Set the focus on the input of this field
             */
            setFocus: function () {
                this.$('input:first').focus();
            },

            /**
             * Set this field as editable
             *
             * @param {boolean} editable
             */
            setEditable: function (editable) {
                this.editable = editable;
            },

            /**
             * Set this field as locked
             *
             * @param {boolean} locked
             */
            setLocked: function (locked) {
                this.locked = locked;
            },

            /**
             * Return whether this field is editable
             *
             * @returns {boolean}
             */
            isEditable: function () {
                return this.editable;
            },

            /**
             * Set this field as ready
             *
             * @param {boolean} ready
             */
            setReady: function (ready) {
                this.ready = ready;
            },

            /**
             * Return whether this field is ready
             *
             * @returns {boolean}
             */
            isReady: function () {
                return this.ready;
            },

            /**
             * Get the current edit mode (can be 'edit' or 'view')
             *
             * @returns {string}
             */
            getEditMode: function () {
                if (this.editable) {
                    return 'edit';
                } else {
                    return 'view';
                }
            },

            /**
             * Return whether this field can be seen
             *
             * @returns {boolean}
             */
            canBeSeen: function () {
                return true;
            },

            /**
             * Get current model value for this field, in this format:
             * {locale: 'en_US', scope: null, data: 'stuff'}
             *
             * @returns {Object}
             */
            getCurrentValue: function () {
                return AttributeManager.getValue(
                    this.model.get('values'),
                    this.attribute,
                    this.context.locale,
                    this.context.scope
                );
            },

            /**
             * Set current model value for this field
             *
             * @param {*} value
             */
            setCurrentValue: function (value) {
                var productValue = this.getCurrentValue();

                if (undefined === productValue) {
                    return;
                }

                productValue.data = value;
                mediator.trigger('pim_enrich:form:entity:update_state');
            },

            /**
             * Get the label of this field (default is code surrounded by brackets)
             *
             * @returns {string}
             */
            getLabel: function () {
                return i18n.getLabel(this.attribute.labels, this.context.uiLocale, this.attribute.code);
            }
        });
    }
);
