define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'routing', 'oro/loading-mask', 'pim/datagrid/state', 'oro/messenger', 'backbone/bootstrap-modal', 'jquery-ui-full'],
    function($, _, Backbone, __, Routing, LoadingMask, DatagridState, messenger) {
        'use strict';

        /**
         * Configure columns action
         *
         * @export  pim/datagrid/configure-columns-action
         * @class   pim.datagrid.ConfigureColumnsAction
         * @extends Backbone.View
         */
        var ConfigureColumnsAction = Backbone.View.extend({

            locale: null,

            label: __('Columns'),

            availableColumnsLabel: __('Available Columns'),

            displayedColumnsLabel: __('Displayed Columns'),

            icon: 'th',

            target: 'div.grid-toolbar .actions-panel .btn-group',

            template: _.template(
                '<a href="javascript:void(0);" class="action btn" title="<%= label %>" id="configure-columns">' +
                    '<i class="icon-<%= icon %>"></i>' +
                    '<%= label %>' +
                '</a>'
            ),

            configureTemplate: _.template(
                '<div class="row-fluid">' +
                    '<div class="span6">' +
                        '<h4><%= availableColumnsLabel %></h4>' +
                        '<ul id="bucket" class="connectedSortable">' +
                            '<% _.each(availableColumns, function(label, code) { %>' +
                                '<li data-value="<%= code %>">' +
                                    '<i class="icon-reorder"></i><%= label %>' +
                                '</li>' +
                            '<% }); %>' +
                        '</ul>' +
                    '</div>' +
                    '<div class="span6">' +
                        '<h4><%= displayedColumnsLabel %></h4>' +
                        '<ul id="columns" class="connectedSortable">' +
                            '<% _.each(displayedColumns, function(label, code) { %>' +
                                '<li data-value="<%= code %>">' +
                                    '<i class="icon-reorder"></i><%= label %>' +
                                '</li>' +
                            '<% }); %>' +
                        '</ul>' +
                    '</div>' +
                '</div>'
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

            execute: function(e) {
                e.preventDefault();
                var url = Routing.generate('pim_datagrid_view_list_columns', { alias: this.gridName, dataLocale: this.locale });

                var loadingMask = new LoadingMask();
                loadingMask.render().$el.appendTo($('#container'));
                loadingMask.show();

                $.get(url, _.bind(function (availableColumns) {
                    var displayedColumns = {};
                    var displayedCodes = DatagridState.get(this.gridName, 'columns');

                    if (displayedCodes) {
                        _.each(displayedCodes.split(','), function(code) {
                            if (availableColumns[code]) {
                                displayedColumns[code] = availableColumns[code];
                                delete availableColumns[code];
                            }
                        });
                    }

                    var content = this.configureTemplate({
                        availableColumnsLabel: this.availableColumnsLabel,
                        displayedColumnsLabel: this.displayedColumnsLabel,
                        availableColumns:      availableColumns,
                        displayedColumns:      displayedColumns
                    });

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

                    $('#columns, #bucket').sortable({
                        connectWith: '.connectedSortable',
                        containment: $('#columns').closest('.row-fluid'),
                        tolerance: 'pointer',
                        cursor: 'move'
                    }).disableSelection();

                    modal.on('cancel', this.subscribe.bind(this));
                    modal.on('ok', _.bind(function() {
                        var values = _.map($('#columns li'), function (el) {
                            return $(el).data('value');
                        });
                        if (values.length) {
                            DatagridState.set(this.gridName, 'columns', values.join(','));
                            var url = window.location.hash;
                            Backbone.history.navigate(url.substr(0, url.length -1));
                            Backbone.history.navigate(url, true);
                        } else {
                            messenger.notificationFlashMessage('error', __('datagrid_view.columns.min_message'));
                            this.subscribe();
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
