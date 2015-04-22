define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pim/dashboard/abstract-widget', 'moment'],
    function ($, _, Routing, Navigation, AbstractWidget, moment) {
        'use strict';

        var LastOperationsWidget = AbstractWidget.extend({
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
                                        '<%= operation.date %>',
                                    '</td>',
                                    '<td><%= _.__("pim_dashboard.widget.last_operations.job_type." + operation.type) %></td>',
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
                'click a.btn': 'followLink'
            },

            followLink: function(e) {
                e.preventDefault();
                var routing;
                var operationType = $(e.currentTarget).data('operation-type');

                if ('mass_edit' === operationType) {
                    routing = Routing.generate(
                        'pim_enrich_job_tracker_show',
                        { id: $(e.currentTarget).data('id') }
                    );
                } else {
                    routing = Routing.generate(
                        'pim_importexport_' + operationType + '_execution_show',
                        { id: $(e.currentTarget).data('id') }
                    );
                }

                Navigation.getInstance().setLocation(routing);
            },

            _processResponse: function(data) {
                this.options.contentLoaded = true;

                _.each(data, function(operation) {
                    operation.labelClass = this.labelClasses[operation.status] ?
                        'label-' + this.labelClasses[operation.status]
                        : '';
                    operation.statusLabel = operation.statusLabel.slice(0, 1).toUpperCase() +
                        operation.statusLabel.slice(1).toLowerCase();

                    if (operation.date) {
                        var date = moment(new Date(operation.date * 1000));
                        if (date.isValid()) {
                            var dateFormat = date.isSame(new Date(), 'day') ? 'HH:mm' : 'YYYY-MM-DD HH:mm';
                            operation.date = date.format(dateFormat);
                        }
                    }
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
