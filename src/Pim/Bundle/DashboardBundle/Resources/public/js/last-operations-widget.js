define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pim/dashboard/abstract-widget'],
    function ($, _, Routing, Navigation, AbstractWidget) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'last-operations-widget',

            className: 'table table-condensed table-light groups unspaced',

            labelClasses: {
                1: 'success',
                3: 'info',
                4: 'important',
                5: 'important',
                6: 'important',
                7: 'important',
                8: 'inverse'
            },

            options: {
                contentLoaded: false
            },

            showListBtnTemplate: _.template(
                '<a class="pull-right" id ="btn-show-list" href="javascript:void(0);" style="color: #444">' +
                    '<i class="icon-tasks"></i>' +
                '</a>'
            ),

            template: _.template(
                [
                    '<% if (!_.isEmpty(data)) { %>',
                        '<thead>',
                            '<tr>',
                                '<th class="center">',
                                    '<%= _.__("pim_dashboard.widget.last_operations.date") %>',
                                '</th>',
                                '<th class="center">',
                                    '<%= _.__("pim_dashboard.widget.last_operations.type") %>',
                                '</th>',
                                '<th class="center">',
                                    '<%= _.__("pim_dashboard.widget.last_operations.profile name") %>',
                                '</th>',
                                '<th class="center">',
                                    '<%= _.__("pim_dashboard.widget.last_operations.status") %>',
                                '</th>',
                                '<th></th>',
                            '</tr>',
                        '</thead>',
                        '<tbody>',
                            '<% _.each(data, function (operation) { %>',
                                '<tr>',
                                    '<td>',
                                        '<%= operation.date %>',
                                    '</td>',
                                    '<td>',
                                        '<%= _.__("pim_dashboard.widget.last_operations.job_type."+operation.type) %>',
                                    '</td>',
                                    '<td><%= operation.label %></td>',
                                    '<td>',
                                        '<span class="label <%= operation.labelClass %> fullwidth">',
                                            '<%= operation.statusLabel %>',
                                        '</span>',
                                    '</td>',
                                    '<td>',
                                        '<a class="btn btn-mini" href="javascript:void(0);" ',
                                            'data-id="<%= operation.id %>" ',
                                            'data-operation-type="<%= operation.type %>">',
                                            '<%= _.__("pim_dashboard.widget.last_operations.details") %>',
                                        '</a>',
                                    '</td>',
                                '</tr>',
                            '<% }); %>',
                        '</tbody>',
                    '<% } else if (options.contentLoaded) {%>',
                        '<span class="label text-center buffer-small-top buffer-small-bottom fullwidth">',
                            '<%= _.__("pim_dashboard.widget.last_operations.empty") %>',
                        '</span>',
                    '<% } %>'
                ].join('')
            ),

            events: {
                'click a.btn': 'followLink',
                'click a#btn-show-list': 'showList'
            },

            followLink: function (e) {
                e.preventDefault();
                var route;
                var operationType = $(e.currentTarget).data('operation-type');

                switch (operationType) {
                    case 'mass_edit':
                    case 'quick_export':
                        route = Routing.generate(
                            'pim_enrich_job_tracker_show',
                            { id: $(e.currentTarget).data('id') }
                        );
                        break;
                    default:
                        route = Routing.generate(
                            'pim_importexport_' + operationType + '_execution_show',
                            { id: $(e.currentTarget).data('id') }
                        );
                        break;
                }

                Navigation.getInstance().setLocation(route);
            },

            setShowListBtn: function () {
                this.$showListBtn = $(this.showListBtnTemplate());

                this.$el.parent().siblings('.widget-header').append(this.$showListBtn);
                this.$showListBtn.on('click', _.bind(this.showList, this));

                return this;
            },

            showList: function (e) {
                e.preventDefault();

                Navigation.getInstance().setLocation(Routing.generate('pim_enrich_job_tracker_index'));
            },

            _processResponse: function (data) {
                this.options.contentLoaded = true;

                _.each(data, function (operation) {
                    operation.labelClass = this.labelClasses[operation.status] ?
                        'label-' + this.labelClasses[operation.status]
                        : '';
                    operation.statusLabel = operation.statusLabel.slice(0, 1).toUpperCase() +
                        operation.statusLabel.slice(1).toLowerCase();
                }, this);

                return data;
            }
        });
    }
);
