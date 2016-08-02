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
        'text!pim/template/export/product/edit/content/structure/attributes',
        'pim/form',
        'oro/loading-mask',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/export/product/edit/content/structure/attributes-selector'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        template,
        BaseForm,
        LoadingMask,
        fetcherRegistry,
        UserContext,
        AttributeSelector
    ) {
        return BaseForm.extend({
            className: 'control-group attributes',
            template: _.template(template),
            validationErrors: [],
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

                this.validationErrors = _.where(this.parent.errors, {
                    field: 'attributes'
                });

                var attributes = this.getFormData().structure.attributes || [];

                this.$el.html(
                    this.template({
                        __: __,
                        isEditable: this.isEditable(),
                        titleEdit: __('pim_enrich.export.product.filter.attributes.title'),
                        labelEdit: __('pim_enrich.export.product.filter.attributes.edit'),
                        labelInfo: __(
                            'pim_enrich.export.product.filter.attributes.label',
                            {count: attributes.length},
                            attributes.length
                        ),
                        errors: this.validationErrors
                    })
                );

                this.delegateEvents();

                this.renderExtensions();
            },

            /**
             * Returns whether this filter is editable.
             *
             * @returns {boolean}
             */
            isEditable: function () {
                return undefined !== this.config.isEditable ?
                    this.config.isEditable :
                    true;
            },

            openSelector: function (e) {
                e.preventDefault();
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();
                var selectedAttributes = this.getFormData().structure.attributes || [];
                var attributeSelector = new AttributeSelector();
                attributeSelector.setSelected(selectedAttributes);

                var modal = new Backbone.BootstrapModal({
                    className: 'modal modal-large column-configurator-modal',
                    modalOptions: {
                        backdrop: 'static',
                        keyboard: false
                    },
                    allowCancel: true,
                    okCloses: false,
                    cancelText: _.__('pim_enrich.export.product.filter.attributes.modal.cancel'),
                    title: _.__('pim_enrich.export.product.filter.attributes.modal.title'),
                    content: '<div class="attribute-selector"></div>',
                    okText: _.__('pim_enrich.export.product.filter.attributes.modal.apply')
                });

                loadingMask.hide();
                loadingMask.$el.remove();

                modal.open();
                attributeSelector.setElement('.attribute-selector').render();

                modal.on('ok', function () {
                    var values = attributeSelector.getSelected();
                    var data = this.getFormData();

                    data.structure.attributes = values;

                    this.setData(data);
                    modal.close();
                    this.render();
                }.bind(this));
            }
        });
    }
);
