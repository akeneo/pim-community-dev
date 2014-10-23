define(
    ['jquery', 'underscore', 'backbone', 'routing', 'oro/loading-mask', 'pim/datagrid/state', 'backbone/bootstrap-modal', 'jquery-ui-full'],
    function($, _, Backbone, Routing, LoadingMask, DatagridState) {
        'use strict';

        var Column = Backbone.Model.extend({
            defaults: {
                label: '',
                displayed: false,
                group: _.__('system_filter_group')
            }
        });

        var ColumnList = Backbone.Collection.extend({ model: Column });

        var ColumnListView = Backbone.View.extend({
            collection: ColumnList,

            template: _.template(
                '<div class="span4">' +
                    '<h4></h4>' +
                    '<ul id="column-groups" class="nav nav-list">' +
                        '<li class="tab active">' +
                            '<%= _.__("pim_datagrid.column_configurator.all_groups") %>' +
                            '<span class="badge badge-transparent pull-right"><%= columns.length %></span>' +
                        '</li>' +
                        '<% _.each(groups, function(group) { %>' +
                            '<li class="tab" data-value="<%= group.name %>">' +
                                '<%= group.name %>' +
                                '<span class="badge badge-transparent pull-right"><%= group.itemCount %></span>' +
                            '</li>' +
                        '<% }); %>' +
                    '</ul>' +
                '</div>' +
                '<div class="span4">' +
                    '<h4>' +
                        '<i class="icon-search"></i>' +
                        '<input type="search" placeholder="<%= _.__("pim_datagrid.column_configurator.search") %>"/>' +
                    '</h4>' +
                    '<ul id="bucket" class="connected-sortable">' +
                        '<% _.each(_.where(columns, {displayed: false}), function(column) { %>' +
                            '<li data-value="<%= column.code %>" data-group="<%= column.group %>">' +
                                '<i class="icon-th"></i><%= column.label %>' +
                            '</li>' +
                        '<% }); %>' +
                    '</ul>' +
                '</div>' +
                '<div class="span4">' +
                    '<h4><%= _.__("Displayed Columns") %></h4>' +
                    '<ul id="columns" class="connected-sortable">' +
                        '<% _.each(_.where(columns, {displayed: true}), function(column) { %>' +
                            '<li data-value="<%= column.code %>" data-group="<%= column.group %>">' +
                                '<i class="icon-th"></i><%= column.label %>' +
                            '</li>' +
                        '<% }); %>' +
                        '<div class="alert alert-error hide"><%= _.__("datagrid_view.columns.min_message") %></div>' +
                    '</ul>' +
                '</div>'
            ),

            events: {
                'input input[type="search"]': 'search',
                'click #column-groups li': 'filter'
            },

            search: function(e) {
                var search = $(e.currentTarget).val();

                var matchesSearch = function(text) {
                    return (''+text).toUpperCase().indexOf((''+search).toUpperCase()) >= 0;
                };

                this.$('#bucket').find('li').each(function() {
                    if (matchesSearch($(this).data('value')) || matchesSearch($(this).text())) {
                        $(this).removeClass('hide');
                    } else {
                        $(this).addClass('hide');
                    }
                });
            },

            filter: function(e) {
                var filter = $(e.currentTarget).data('value');

                $(e.currentTarget).addClass('active').siblings('.active').removeClass('active');

                if (_.isUndefined(filter)) {
                    this.$('#bucket li').removeClass('filtered');
                } else {
                    this.$('#bucket').find('li').each(function() {
                        if (filter === $(this).data('group')) {
                            $(this).removeClass('filtered');
                        } else {
                            $(this).addClass('filtered');
                        }
                    });
                }
            },

            render: function() {
                var groups = [{ position: 0, name: _.__('system_filter_group'), itemCount: 0 }];

                _.each(this.collection.toJSON(), function(column) {
                    if (_.isEmpty(_.where(groups, {name: column.group}))) {
                        var position = parseInt(column.groupOrder, 10);
                        if (!_.isNumber(position) || !_.isEmpty(_.where(groups, {position: position}))) {
                            position = _.max(groups, function(group) { return group.position; }) + 1;
                        }

                        groups.push({
                            position:  position,
                            name:      column.group,
                            itemCount: 1
                        });
                    } else {
                        _.first(_.where(groups, {name: column.group})).itemCount += 1;
                    }
                });

                groups = _.sortBy(groups, function(group) { return group.position; });

                this.$el.html(
                    this.template({
                        groups:  groups,
                        columns: this.collection.toJSON()
                    })
                );

                this.$('#columns, #bucket').sortable({
                    connectWith: '.connected-sortable',
                    containment: this.$el,
                    tolerance: 'pointer',
                    cursor: 'move',
                    cancel: 'div.alert',
                    receive: _.bind(function(event, ui) {
                        var model = _.first(this.collection.where({code: ui.item.data('value')}));
                        model.set('displayed', ui.sender.is('#bucket'));
                        if (this.collection.where({displayed: true}).length) {
                            this.$('.alert').hide();
                            this.$el.closest('.modal').find('.btn.ok:not(.btn-primary)').addClass('btn-primary').attr('disabled', false);
                        } else {
                            this.$('.alert').show();
                            this.$el.closest('.modal').find('.btn.ok.btn-primary').removeClass('btn-primary').attr('disabled', true);
                        }
                    }, this)
                }).disableSelection();

                this.$('ul').css('height', $(window).height() * 0.7);

                return this;
            },

            getDisplayed: function() {
                return _.map(this.$('#columns li'), function (el) {
                    return $(el).data('value');
                });
            }
        });

        /**
         * Configure columns action
         *
         * @export  pim/datagrid/configure-columns-action
         * @class   pim.datagrid.ConfigureColumnsAction
         * @extends Backbone.View
         */
        var ConfigureColumnsAction = Backbone.View.extend({

            locale: null,

            label: _.__('Columns'),

            icon: 'th',

            target: 'div.grid-toolbar .actions-panel',

            template: _.template(
                '<div class="btn-group">' +
                    '<a href="javascript:void(0);" class="action btn" title="<%= label %>" id="configure-columns">' +
                        '<i class="icon-<%= icon %>"></i>' +
                        '<%= label %>' +
                    '</a>' +
                '</div>'
            ),

            configuratorTemplate: _.template(
                '<div id="column-configurator" class="row-fluid"></div>'
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


                $.get(url, _.bind(function (columns) {
                    var displayedCodes = DatagridState.get(this.gridName, 'columns');
                    if (displayedCodes) {
                        displayedCodes = displayedCodes.split(',');
                    } else {
                        displayedCodes = _.pluck(this.$gridContainer.data('metadata').columns, 'name');
                    }

                    var columnList = new ColumnList();
                    _.each(columns, function(column) {
                        column.displayed = _.indexOf(displayedCodes, column.code) !== -1;
                        columnList.add(column);
                    });

                    var columnListView = new ColumnListView({collection: columnList});

                    var modal = new Backbone.BootstrapModal({
                        allowCancel: true,
                        okCloses: false,
                        cancelText: _.__('Cancel'),
                        title: _.__('Column Selection'),
                        content: this.configuratorTemplate(),
                        okText: _.__('Apply')
                    });

                    loadingMask.hide();
                    loadingMask.$el.remove();

                    modal.open();
                    modal.$el.addClass('column-configurator-modal');

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
                            Backbone.history.navigate(url.substr(0, url.length -1));
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
