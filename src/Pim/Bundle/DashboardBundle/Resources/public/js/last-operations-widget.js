define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pimdashboard/js/abstract-widget'],
    function ($, _, Routing, Navigation, AbstractWidget) {
        'use strict';

        var LastOperationsWidget = AbstractWidget.extend({
            tagName: 'table',

            id: 'last-operations-widget',

            className: 'table table-condensed table-light groups unspaced',

            statusLabels: {
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

            template: _.template(
                [
                    '<% if (!_.isEmpty(data)) { %>',
                        '<thead>',
                            '<tr>',
                                '<th class="center"><%= _.__("pim_dashboard.widget.last_operations.date") %></th>',
                                '<th class="center"><%= _.__("pim_dashboard.widget.last_operations.type") %></th>',
                                '<th class="center"><%= _.__("pim_dashboard.widget.last_operations.profile name") %></th>',
                                '<th class="center"><%= _.__("pim_dashboard.widget.last_operations.status") %></th>',
                                '<th></th>',
                            '</tr>',
                        '</thead>',
                        '<tbody>',
                            '<% _.each(data, function(operation) { %>',
                                '<tr>',
                                    '<td>',
                                        // TODO: if the date is today, format it as 'H:i', otherwise 'Y-m-d H:i'
                                        '<%= operation.date %>',
                                    '</td>',
                                    '<td><%= _.__("pim_dashboard.widget.last_operations.job_type." + operation.type) %></td>',
                                    '<td><%= operation.label %></td>',
                                    '<td>',
                                        '<span class="label<%= operation.statusLabel %> fullwidth">',
                                            '<%= operation.status %>',
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
                'click a.btn': 'followLink'
            },

            followLink: function(e) {
                e.preventDefault();

                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pim_importexport_' + $(e.currentTarget).data('operation-type') + '_execution_show',
                        { id: $(e.currentTarget).data('id') }
                    )
                );
            },

            _processResponse: function(data) {
                this.options.contentLoaded = true;

                _.each(data, function(operation) {
                    operation.labelClass = this.statusLabels[operation.status] ?
                        'label-' + this.statusLabels[operation.status]
                        : '';
                    operation.status = operation.status.slice(0, 1).toUpperCase() +
                        operation.status.slice(1).toLowerCase();
                }, this);

                return data;
            }
        });

        var instance = null;

        return {
            init: function(options) {
                if (!instance) {
                    instance = new LastOperationsWidget(options);
                } else if (_.has(options, 'el')) {
                    instance.setElement(options.el);
                }
                instance.render().delayedLoad();
            }
        };
    }
);
