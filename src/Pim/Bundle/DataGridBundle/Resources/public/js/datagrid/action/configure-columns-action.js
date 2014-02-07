define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/loading-mask', 'backbone/bootstrap-modal'],
    function($, _, Backbone, __, Routing, LoadingMask) {
        'use strict';

        /**
         * Export action
         *
         * @export  pim/datagrid/export-action
         * @class   pim.datagrid.ConfigureColumnsAction
         * @extends Backbone.View
         */
        var ConfigureColumnsAction = Backbone.View.extend({

            locale: null,

            label: __('Columns'),

            icon: 'th',

            target: 'div.grid-toolbar .actions-panel .btn-group',

            template: _.template(
                '<a href="javascript:void(0);" class="action btn" title="<%= label %>" id="configure-columns">' +
                    '<i class="icon-<%= icon %>"></i>' +
                    '<%= label %>' +
                '</a>'
            ),

            initialize: function (options) {
                if (_.has(options, 'label')) {
                    this.label = __(options.label);
                }
                if (_.has(options, 'icon')) {
                    this.icon = options.icon;
                }

                if (!options.$gridContainer) {
                    throw new Error('Grid selector is not specified');
                }

                this.$gridContainer = options.$gridContainer;
                this.gridName = options.gridName;
                this.locale = decodeURIComponent(options.url).split('dataLocale]=').pop();

                Backbone.View.prototype.initialize.apply(this, arguments);

                this.render();
            },

            render: function() {
                this.$gridContainer
                    .find(this.target)
                    .append(
                        this.template({
                            icon: this.icon,
                            label: this.label
                        })
                    );
                this.subscribe();
            },

            subscribe: function() {
                $('#configure-columns').one('click', this.execute.bind(this));
            },

            execute: function(event) {
                event.preventDefault();
                var url = Routing.generate('pim_catalog_datagrid_edit', { alias: this.gridName, dataLocale: this.locale });
                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();

                $.get(url, _.bind(function (content) {
                    var modal = new Backbone.BootstrapModal({
                        allowCancel: true,
                        cancelText: __('Cancel'),
                        title: __('Datagrid Configuration'),
                        content: content,
                        okText: __('Apply')
                    });

                    loadingMask.hide();
                    loadingMask.$el.remove();

                    modal.open();
                    modal.$el.css({
                        'width': '700px',
                        'margin-left': '-350px'
                    });

                    modal.on('cancel', this.subscribe.bind(this));
                    modal.on('ok', function() {
                        modal.$el.find('form').submit();
                    });
                }, this));
            }
        });

        ConfigureColumnsAction.init = function ($gridContainer, gridName) {
            var metadata = $gridContainer.data('metadata');
            var options = metadata.options || {};
            new ConfigureColumnsAction(
                _.extend({ $gridContainer: $gridContainer, gridName: gridName, url: options.url }, options.configureColumns)
            );
        };

        return ConfigureColumnsAction;
    }
);
