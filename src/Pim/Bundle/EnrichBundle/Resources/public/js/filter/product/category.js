'use strict';

define([
    'jquery',
    'underscore',
    'oro/translator',
    'backbone',
    'pim/filter/filter',
    'routing',
    'pim/filter/product/category/selector',
    'pim/fetcher-registry',
    'text!pim/template/filter/product/category',
    'jquery.select2'
], function ($, _, __, Backbone, BaseFilter, Routing, CategoryTree, fetcherRegistry, template) {
    var TreeModal = Backbone.BootstrapModal.extend({
        className: 'modal jstree-modal'
    });

    return BaseFilter.extend({
        shortname: 'category',
        template: _.template(template),
        className: 'control-group filter-item category-filter',
        events: {
            'click button': 'openSelector'
        },

        /**
         * {@inherit}
         */
        configure: function () {
            this.on('channel:update:after', this.channelUpdated.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                this.getCurrentChannel()
            ).then(function (templateContext, channel) {
                return _.extend({}, templateContext, {
                    channel: channel
                });
            }.bind(this));
        },

        /**
         * Returns rendered input.
         *
         * @return {String}
         */
        renderInput: function (templateContext) {
            if (undefined === this.getValue() ||
                'IN CHILDREN' === this.getOperator()) {
                this.setDefaultValues(templateContext.channel);
            }

            var categoryCount = 'IN CHILDREN' === this.getOperator() ? 0 : this.getValue().length;
            return this.template({
                isEditable: this.isEditable(),
                titleEdit: __('pim_connector.export.categories.selector.title'),
                labelEdit: __('pim_connector.export.categories.selector.edit'),
                labelInfo: __(
                    'pim_connector.export.categories.selector.label',
                    {count: categoryCount},
                    categoryCount
                ),
                value: this.getValue()
            });
        },

        /**
         * Resets selection after channel has been modified then re-renders the view.
         */
        channelUpdated: function () {
            this.getCurrentChannel().then(function (channel) {
                this.setDefaultValues(channel);
            }.bind(this));
        },

        /**
         * Open the selector popin
         */
        openSelector: function () {
            var modal = new TreeModal({
                title: __('pim_connector.export.categories.selector.modal.title'),
                cancelText: __('pim_connector.export.categories.selector.modal.cancel'),
                okText: __('pim_connector.export.categories.selector.modal.confirm'),
                content: ''
            });

            modal.render();

            var tree = new CategoryTree({
                el: modal.$el.find('.modal-body'),
                attributes: {
                    channel: this.getParentForm().getFormData().structure.scope,
                    categories: 'IN CHILDREN' === this.getOperator() ? [] : this.getValue()
                }
            });

            tree.render();
            modal.open();

            modal.on('cancel', function () {
                modal.remove();
                tree.remove();
            });

            modal.on('ok', function () {
                if (_.isEmpty(tree.attributes.categories)) {
                    this.getCurrentChannel().then(function (channel) {
                        this.setDefaultValues(channel);
                    }.bind(this));
                } else {
                    this.setData({
                        field: this.getField(),
                        operator: 'IN',
                        value: tree.attributes.categories
                    });
                }

                modal.close();
                modal.remove();
                tree.remove();
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return false;
        },

        /**
         * {@inheritdoc}
         */
        getField: function () {
            var fieldName = BaseFilter.prototype.getField.apply(this, arguments);

            if (-1 === fieldName.indexOf('.code')) {
                fieldName += '.code';
            }

            return fieldName;
        },

        /**
         * Get the current selected channel
         *
         * @return {Promise}
         */
        getCurrentChannel: function () {
            return fetcherRegistry.getFetcher('channel').fetch(this.getParentForm().getFormData().structure.scope);
        },

        /**
         * Set the default values for the filter
         *
         * @param {object} channel
         */
        setDefaultValues: function (channel) {
            var silent = this.getOperator() === 'IN CHILDREN' && _.isEqual(this.getValue(), [channel.category.code]);

            this.setData({
                field: this.getField(),
                operator: 'IN CHILDREN',
                value: [channel.category.code]
            }, {silent: silent});
        }
    });
});
