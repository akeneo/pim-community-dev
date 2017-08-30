define(
    [
        'oro/pageable-collection',
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'routing',
        'oro/loading-mask',
        'pim/datagrid/state',
        'pim/common/column-list-view',
        'bootstrap-modal',
        'jquery-ui'
    ],
    function(
        PageableCollection,
        $,
        _,
        __,
        Backbone,
        Routing,
        LoadingMask,
        DatagridState,
        ColumnListView
    ) {
        var Column = Backbone.Model.extend({
            defaults: {
                removable: true,
                label: '',
                displayed: false,
                group: __('system_filter_group')
            }
        });

        var ColumnList = Backbone.Collection.extend({ model: Column });

        /**
         * Configure columns action
         *
         * @export  pim/datagrid/configure-columns-action
         * @class   pim.datagrid.ConfigureColumnsAction
         * @extends Backbone.View
         */
        var ConfigureColumnsAction = Backbone.View.extend({

            locale: null,

            label: _.__('pim_datagrid.column_configurator.label'),

            icon: 'th',

            target: '.AknGridToolbar .actions-panel',

            template: _.template(
                '<div class="AknGridToolbar-actionButton">' +
                    '<a href="javascript:void(0);" class="AknActionButton" title="<%= label %>" id="configure-columns">' +
                        '<i class="icon-<%= icon %>"></i>' +
                        '<%= label %>' +
                    '</a>' +
                '</div>'
            ),

            configuratorTemplate: _.template(
                '<div id="column-configurator" class="AknColumnConfigurator"></div>'
            ),

            initialize: function (options) {
                if (_.has(options, 'label')) {
                    this.label = _.__(options.label);
                }
                if (_.has(options, 'icon')) {
                    this.icon = options.icon;
                }

                if (!options.$gridContainer) {
                    throw new Error('Grid selector is not specified');
                }

                this.$gridContainer = options.$gridContainer;
                this.gridName = options.gridName;

                const filters = PageableCollection.prototype.decodeStateData(options.url.split('?')[1]);
                const gridFilters = filters[this.gridName] || {};

                this.locale = filters.dataLocale || gridFilters.dataLocale;

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

            execute: function(e) {
                e.preventDefault();
                var url = Routing.generate('pim_datagrid_view_list_available_columns', {
                    alias: this.gridName,
                    dataLocale: this.locale
                });

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();


                $.get(url, _.bind(function (columns) {
                    var displayedCodes = DatagridState.get(this.gridName, 'columns');

                    if (displayedCodes) {
                        displayedCodes = displayedCodes.split(',');
                    } else {
                        displayedCodes = _.pluck(this.$gridContainer.data('metadata').columns, 'name');
                    }

                    displayedCodes = _.map(displayedCodes, function(displayedCode, index) {
                        return {
                            code: displayedCode,
                            position: index
                        }
                    });

                    var columnList = new ColumnList();
                    _.each(columns, function(column) {
                        var displayedCode = _.findWhere(displayedCodes, {code: column.code});
                        if (!_.isUndefined(displayedCode)) {
                            column.displayed = true;
                            column.position = displayedCode.position;
                        }

                        columnList.add(column);
                    });

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
                        content: this.configuratorTemplate(),
                        okText: _.__('pim_datagrid.column_configurator.apply')
                    });

                    loadingMask.hide();
                    loadingMask.$el.remove();

                    modal.open();
                    columnListView.setElement('#column-configurator').render();

                    modal.on('cancel', this.subscribe.bind(this));
                    modal.on('ok', _.bind(function() {
                        var values = columnListView.getDisplayed();
                        if (!values.length) {
                            return;
                        } else {
                            DatagridState.set(this.gridName, 'columns', values.join(','));
                            modal.close();
                            var url = window.location.hash;
                            Backbone.history.fragment = new Date().getTime();
                            Backbone.history.navigate(url, true);
                        }
                    }, this));
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
