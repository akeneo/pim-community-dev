'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'oro/mediator',
    'pim/form',
    'pim/template/filter/filter'
], function (
    $,
    _,
    __,
    mediator,
    BaseForm,
    filterTemplate
) {
    return BaseForm.extend({
        className: 'AknFieldContainer control-group filter-item',
        elements: {},
        editable: true,
        removable: false,
        filterTemplate: _.template(filterTemplate),

        configure: function () {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode()});
            }.bind(this));

            return BaseForm.prototype.configure.apply(this, arguments);
        },

        /**
         * Sets the parentForm code on which this filter operates.
         *
         * @param {string} parentForm
         */
        setParentForm: function (parentForm) {
            this.parentForm = parentForm;
        },

        /**
         * Gets the parentForm code on which this filter operates.
         *
         * @return {string}
         */
        getParentForm: function () {
            return this.parentForm;
        },

        /**
         * Sets the filter code  (code: 'categories', field: 'categories')
         *
         * @param {string} code
         */
        setCode: function (code) {
            this.filterCode = code;
        },

        /**
         * Gets the filter code (code: 'categories', field: 'categories')
         *
         * @return {string}
         */
        getCode: function () {
            return this.filterCode;
        },

        /**
         * Sets the field code on which this filter operates. (code: 'categories', field: 'categories')
         *
         * @param {string} field
         */
        setField: function (field) {
            this.setData(
                {field: field},
                {silent: true}
            );
        },

        /**
         * Gets the field code on which this filter operates.  (code: 'categories', field: 'categories')
         *
         * @return {string}
         */
        getField: function () {
            return this.getFormData().field;
        },

        /**
         * Type setter
         *
         * @param {string} type
         */
        setType: function (type) {
            this.type = type;
        },

        /**
         * Get the type identifier
         *
         * @return {string}
         */
        getType: function () {
            return this.type;
        },

        /**
         * Set the field operator
         *
         * @param {string} operator
         */
        setOperator: function (operator) {
            this.setData(
                {operator: operator},
                {silent: true}
            );
        },

        /**
         * Gets the current operator.
         *
         * @return {string}
         */
        getOperator: function () {
            return this.getFormData().operator;
        },

        /**
         * Sets the current value.
         *
         * @param {string} value
         * @param {object} [options]
         */
        setValue: function (value, options) {
            options = options || {silent: true};

            this.setData(
                {value: value},
                options
            );
        },

        /**
         * Gets the current value.
         *
         * @return {string}
         */
        getValue: function () {
            return this.getFormData().value;
        },

        /**
         * Sets this filter as editable or not.
         *
         * @param {boolean} editable
         */
        setEditable: function (editable) {
            this.editable = Boolean(editable);
        },

        /**
         * Is this filter editable?
         *
         * @returns {boolean}
         */
        isEditable: function () {
            return this.editable;
        },

        /**
         * Returns whether this filter is empty.
         *
         * @returns {boolean}
         */
        isEmpty: function () {
            return false;
        },

        /**
         * Sets this filter as removable or not.
         *
         * @param {boolean} removable
         */
        setRemovable: function (removable) {
            this.removable = removable;
        },

        /**
         * Is this filter removable?
         *
         * @return {boolean}
         */
        isRemovable: function () {
            return this.removable;
        },

        /**
         * Triggers the filter removal event.
         */
        removeFilter: function () {
            this.trigger('filter:remove', this.getField());
        },

        /**
         * Renders the filter.
         *
         * @return {Object}
         */
        render: function () {
            var promises  = [];
            this.elements = {};
            this.setEditable(true);

            mediator.trigger('pim_enrich:form:filter:extension:add', {filter: this, promises: promises});

            $.when.apply($, promises)
                .then(this.getTemplateContext.bind(this))
                .then(function (templateContext) {
                    this.el.dataset.name = this.getField();
                    this.el.dataset.type = this.getType();

                    this.$el.html(this.filterTemplate(templateContext));
                    this.$('.remove').on('click', this.removeFilter.bind(this));
                    this.$('.filter-input').replaceWith(this.renderInput(templateContext));

                    this.renderElements();
                    this.postRender(templateContext);
                    this.delegateEvents();
                }.bind(this));

            return this;
        },

        /**
         * Gets the template context.
         *
         * @returns {Promise}
         */
        getTemplateContext: function () {
            return $.Deferred().resolve({
                label: __('pim_enrich.export.product.filter.' + this.shortname + '.title'),
                removable: this.isRemovable(),
                editable: this.isEditable()
            }).promise();
        },

        /**
         * Renders the input inside the filter area.
         *
         * @throws {Error} if this method is not implemented
         */
        renderInput: function () {
            throw new Error('You should implement your filter template');
        },

        /**
         * Renders extension elements of the filter.
         */
        renderElements: function () {
            _.each(this.elements, function (elements, position) {
                var $container = this.$('.' + position + '-elements-container');
                $container.empty();

                _.each(elements, function (element) {
                    if ('function' === typeof element.render) {
                        $container.append(element.render().$el);
                    } else {
                        $container.append(element);
                    }
                });
            }.bind(this));
        },

        /**
         * Called after rendering the input.
         */
        postRender: function () {},

        /**
         * Adds an extension element to this filter.
         *
         * @param {string} position 'label', 'after-input' or 'below-input'
         * @param {string} code
         * @param {Object} element
         */
        addElement: function (position, code, element) {
            if (!this.elements[position]) {
                this.elements[position] = {};
            }

            this.elements[position][code] = element;
        }
    });
});
