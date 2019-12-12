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
    'pim/template/filter/product/category',
    'jquery.select2'
], function ($, _, __, Backbone, BaseFilter, Routing, CategoryTree, fetcherRegistry, template) {
    var TreeModal = Backbone.BootstrapModal.extend({});

    return BaseFilter.extend({
        shortname: 'category',
        template: _.template(template),
        className: 'AknFieldContainer control-group filter-item category-filter',
        events: {
            'click button': 'openSelector'
        },

        /**
         * {@inherit}
         */
        configure: function () {
            this.listenTo(this, 'channel:update:after', this.channelUpdated.bind(this));
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_update', function (data) {
                _.defaults(data, {field: this.getCode(), operator: 'IN CHILDREN', value: []});
            }.bind(this));

            return BaseFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * Returns rendered input.
         *
         * @return {String}
         */
        renderInput: function () {
            var categoryCount = 'IN CHILDREN' === this.getOperator() ? 0 : this.getValue().length;

            return this.template({
                isEditable: this.isEditable(),
                titleEdit: __('pim_connector.export.categories.selector.title'),
                labelEdit: __('pim_common.edit'),
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
                this.render();
            }.bind(this));
        },

        /**
         * {@inherit}
         */
        getTemplateContext: function () {
            return $.when(
                BaseFilter.prototype.getTemplateContext.apply(this, arguments),
                this.getCurrentChannel()
            ).then(function (templateContext, channel) {
                if ('IN CHILDREN' === this.getOperator()) {
                    this.setDefaultValues(channel);
                }

                return templateContext;
            }.bind(this));
        },

        /**
         * Open the selector popin
         */
        openSelector: function () {
            var modal = new TreeModal({
                title: __('pim_connector.export.categories.selector.modal.title'),
                cancelText: __('pim_common.cancel'),
                okText: __('pim_common.confirm'),
                content: '',
                illustrationClass: 'categories',
            });

            modal.render();

            var tree = new CategoryTree({
                el: modal.$el.find('.modal-body'),
                attributes: {
                    channel: this.getParentForm().getFilters().structure.scope,
                    categories: 'IN CHILDREN' === this.getOperator() ? [] : [...this.getValue()]
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
                this.render();
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return false;
        },

        /**
         * Get the current selected channel
         *
         * @return {Promise}
         */
        getCurrentChannel: function () {
            return fetcherRegistry.getFetcher('channel')
                .fetch(this.getParentForm().getFilters().structure.scope);
        },

        /**
         * Set the default values for the filter
         *
         * @param {object} channel
         */
        setDefaultValues: function (channel) {
            if (this.getOperator() === 'IN CHILDREN' && _.isEqual(this.getValue(), [channel.category_tree])) {
                return;
            }

            this.setData({
                field: this.getField(),
                operator: 'IN CHILDREN',
                value: [channel.category_tree]
            });
        }
    });
});
