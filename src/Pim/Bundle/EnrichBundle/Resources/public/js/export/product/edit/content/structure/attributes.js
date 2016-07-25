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
        'pim/export/product/edit/content/structure/attributes-selector',
        'pim/i18n'
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
        ColumnListView,
        i18n
    ) {
        var Column = Backbone.Model.extend({
            defaults: {
                label: '',
                displayed: false,
                group: __('system_filter_group')
            }
        });

        var ColumnList = Backbone.Collection.extend({ model: Column });

        return BaseForm.extend({
            className: 'control-group attribute-selector',
            template: _.template(template),
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
                        )
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

                // Not scallable at all, we should not release that without proper warning at least
                fetcherRegistry.getFetcher('attribute').fetchAll().then(function (attributes) {
                    var selectedAttributes = this.getFormData().structure.attributes || [];

                    attributes = _.filter(attributes, function (attribute) {
                        return 'pim_catalog_identifier' !== attribute.type;
                    });
                    var columns = _.map(attributes, function (attribute) {
                        return {
                            label: i18n.getLabel(attribute.labels, UserContext.get('uiLocale'), attribute.code),
                            code: attribute.code,
                            displayed: _.contains(selectedAttributes, attribute.code) ||
                                'pim_catalog_identifier' === attribute.type,
                            group: i18n.getLabel(
                                attribute.group.labels,
                                UserContext.get('uiLocale'),
                                attribute.group.code
                            ),
                            removable: 'pim_catalog_identifier' !== attribute.type
                        };
                    });

                    var columnList     = new ColumnList(columns);
                    var columnListView = new ColumnListView({collection: columnList});

                    var modal = new Backbone.BootstrapModal({
                        className: 'modal modal-large column-configurator-modal',
                        modalOptions: {
                            backdrop: 'static',
                            keyboard: false
                        },
                        allowCancel: true,
                        okCloses: false,
                        cancelText: _.__('pim_datagrid.column_configurator.cancel'),
                        title: _.__('pim_datagrid.column_configurator.title'),
                        content: '<div id="column-configurator" class="row-fluid"></div>',
                        okText: _.__('pim_datagrid.column_configurator.apply')
                    });

                    loadingMask.hide();
                    loadingMask.$el.remove();

                    modal.open();
                    columnListView.setElement('#column-configurator').render();

                    modal.on('ok', function () {
                        var values = columnListView.getDisplayed();
                        var data = this.getFormData();

                        data.structure.attributes = values;

                        this.setData(data);
                        modal.close();
                        this.render();
                    }.bind(this));
                }.bind(this));
            }
        });
    }
);
