'use strict';

define([
    'underscore',
    'oro/translator',
    'backbone',
    'pim/filter/filter',
    'routing',
    'pim/filter/product/category/selector',
    'text!pim/template/filter/product/category',
    'jquery.select2'
], function (_, __, Backbone, BaseFilter, Routing, CategoryTree, template) {
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
         * Returns rendered input.
         *
         * @return {String}
         */
        renderInput: function () {
            if (undefined === this.getValue()) {
                this.setValue([]);
            }

            return this.template({
                isEditable: this.isEditable(),
                titleEdit: __('pim_connector.export.categories.selector.title'),
                labelEdit: __('pim_connector.export.categories.selector.edit'),
                labelInfo: __(
                    'pim_connector.export.categories.selector.label',
                    {count: this.getValue().length},
                    this.getValue().length
                ),
                value: this.getValue()
            });
        },

        /**
         * Resets selection after channel has been modified then re-renders the view.
         */
        channelUpdated: function () {
            this.setValue([], {silent: false});
            this.render();
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
                    'channel': this.getParentForm().getFormData().structure.scope,
                    'categories': this.getValue()
                }
            });

            tree.render();
            modal.open();

            modal.on('cancel', function () {
                modal.remove();
                tree.remove();
            });

            modal.on('ok', function () {
                this.setData({
                    field: this.getField(),
                    operator: 'IN',
                    value: tree.attributes.categories
                });

                modal.close();
                modal.remove();
                tree.remove();
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return _.isEmpty(this.getValue()) || '' === this.getOperator();
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
        }
    });
});
