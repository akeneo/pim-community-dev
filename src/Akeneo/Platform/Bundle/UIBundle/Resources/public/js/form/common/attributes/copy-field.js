'use strict';
/**
 * Copy field extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'backbone',
        'underscore',
        'pim/field',
        'pim/template/form/tab/attribute/copy-field',
        'pim/i18n',
        'oro/mediator'
    ],
    function (Backbone, _, Field, template, i18n, mediator) {
        return Field.extend({
            tagName: 'div',
            field: null,
            template: _.template(template),
            selected: false,
            events: {
                'click': 'onSelect'
            },

            /**
             * Initialize the view
             */
            initialize: function () {
                this.selected = false;
                this.field    = null;

                Field.prototype.initialize.apply(this, arguments);
            },

            /**
             * Render the copy field view
             * Delegates the render of the input itself to the Field.renderCopyInput() method
             *
             * @returns {Object}
             */
            render: function () {
                this.$el.empty();

                var templateContext = {
                    type: this.field.attribute.field_type,
                    label: this.field.getLabel(),
                    config: this.field.config,
                    attribute: this.field.attribute,
                    selected: this.selected,
                    context: this.context,
                    i18n: i18n
                };

                mediator.trigger('pim_enrich:form:field:extension:add', {'field': this, 'promises': []});

                this.$el.html(this.template(templateContext));
                this.field.renderCopyInput(this.getCurrentValue())
                    .then(function (render) {
                        this.$('.field-input').html(render);
                        this.renderElements();
                        this.field.postRender();
                    }.bind(this));

                this.delegateEvents();

                return this;
            },

            /**
             * Render elements of this field in different available positions.
             * In the copy case, only implements extension on input position.
             */
            renderElements: function () {
                _.each(this.elements, function (elements, position) {
                    if ('field-input' === position) {
                        var $container = this.$('.field-input');
                        $container.empty();

                        _.each(elements, function (element) {
                            if (typeof element.render === 'function') {
                                $container.append(element.render().$el);
                            } else {
                                $container.append(element);
                            }
                        }.bind(this));
                    }
                }.bind(this));
            },

            /**
             * Bound this copy field to the original field
             *
             * @param {Field} field
             */
            setField: function (field) {
                this.field = field;
            },

            /**
             * Callback called when the copy field is clicked, toggle the select checkbox state
             */
            onSelect: function () {
                this.selected = !this.selected;
                this.$('.copy-field-selector').prop('checked', this.selected);
            },

            /**
             * Mark this copy field as selected or not
             *
             * @param {boolean} selected
             */
            setSelected: function (selected) {
                this.selected = selected;
            }
        });
    }
);
