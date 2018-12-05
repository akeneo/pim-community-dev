'use strict';
/**
 * Attributes structure filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/template/export/product/edit/content/structure/attributes',
        'pim/template/common/modal-centered',
        'pim/form',
        'oro/loading-mask',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/job/product/edit/content/structure/attributes-selector'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        template,
        modalTemplate,
        BaseForm,
        LoadingMask,
        fetcherRegistry,
        UserContext,
        AttributeSelector
    ) {
        return BaseForm.extend({
            className: 'AknFieldContainer attributes',
            template: _.template(template),
            modalTemplate: _.template(modalTemplate),
            events: {
                'click button': 'openSelector'
            },

            /**
             * Initializes configuration.
             *
             * @param {Object} config
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                var attributes = this.getFilters().structure.attributes || [];

                this.$el.html(
                    this.template({
                        __: __,
                        isEditable: this.isEditable(),
                        titleEdit: __('pim_enrich.entity.attribute.plural_label'),
                        labelEdit: __('pim_common.edit'),
                        labelInfo: __(
                            'pim_enrich.export.product.filter.attributes.label',
                            {count: attributes.length},
                            attributes.length
                        ),
                        errors: this.getParent().getValidationErrorsForField('attributes')
                    })
                );

                this.delegateEvents();

                this.$('[data-toggle="tooltip"]').tooltip();
                this.renderExtensions();
            },

            /**
             * Returns whether this filter is editable.
             *
             * @returns {boolean}
             */
            isEditable: function () {
                return undefined !== this.config.readOnly ?
                    !this.config.readOnly :
                    true;
            },

            openSelector: function (e) {
                e.preventDefault();
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo(this.getRoot().$el);
                loadingMask.show();
                var selectedAttributes = this.getFilters().structure.attributes || [];
                var attributeSelector = new AttributeSelector();
                attributeSelector.setSelected(selectedAttributes);

                var modal = new Backbone.BootstrapModal({
                    modalOptions: {
                        backdrop: 'static',
                        keyboard: false
                    },
                    okCloses: false,
                    title: __('pim_enrich.entity.attribute.plural_label'),
                    innerDescription: __('pim_enrich.export.product.filter.attributes_selector.description'),
                    content: '',
                    okText: __('pim_common.apply'),
                    template: this.modalTemplate,
                });

                loadingMask.hide();
                loadingMask.$el.remove();

                modal.open();
                attributeSelector.setElement('.modal-body').render();

                modal.on('ok', function () {
                    var values = attributeSelector.getSelected();
                    var data = this.getFilters();

                    data.structure.attributes = values;

                    this.setData(data);
                    modal.close();
                    this.render();
                }.bind(this));
            },

            /**
             * Get filters
             *
             * @return {object}
             */
            getFilters: function () {
                return this.getFormData().configuration.filters;
            }
        });
    }
);
