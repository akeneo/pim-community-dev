define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pimdashboard/js/abstract-widget', 'moment'],
    function ($, _, Routing, Navigation, AbstractWidget, moment) {
        'use strict';

        var ProductDraftsWidget = AbstractWidget.extend({
            tagName: 'table',

            id: 'product-drafts-widget',

            className: 'table table-condensed table-light groups unspaced',

            options: {
                contentLoaded: false
            },

            template: _.template(
                [
                    '<% if (!_.isEmpty(data)) { %>',
                        '<thead>',
                            '<tr>',
                                '<th class="center"><%= _.__("pimee_dashboard.widget.product_drafts.date") %></th>',
                                '<th class="center"><%= _.__("pimee_dashboard.widget.product_drafts.author") %></th>',
                                '<th class="center"><%= _.__("pimee_dashboard.widget.product_drafts.product") %></th>',
                                '<th></th>',
                            '</tr>',
                        '</thead>',
                        '<tbody>',
                            '<% _.each(data, function(productDraft) { %>',
                                '<tr>',
                                    '<td>',
                                        '<%= productDraft.createdAt %>',
                                    '</td>',
                                    '<td><%= productDraft.author %></td>',
                                    '<td>',
                                        '<a href="javascript:void(0);" data-id="<%= productDraft.productId %>">',
                                            '<%= productDraft.productLabel %>',
                                        '</a>',
                                    '</td>',
                                    '<td>',
                                        '<a class="btn btn-mini" href="javascript:void(0);" data-id="<%= productDraft.productId %>" data-redirecttab="#proposals">',
                                            '<%= _.__("pimee_dashboard.widget.product_drafts.review") %>',
                                        '</a>',
                                    '</td>',
                                '</tr>',
                            '<% }); %>',
                        '</tbody>',
                    '<% } else if (options.contentLoaded) {%>',
                        '<span class="label text-center buffer-small-top buffer-small-bottom fullwidth">',
                            '<%= _.__("pimee_dashboard.widget.product_drafts.empty") %>',
                        '</span>',
                    '<% } %>'
                ].join('')
            ),

            events: {
                'click a': 'followLink'
            },

            followLink: function(e) {
                e.preventDefault();

                if ($(e.currentTarget).data('redirecttab')) {
                    sessionStorage.setItem('redirectTab', $(e.currentTarget).data('redirecttab'));
                }

                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pim_enrich_product_edit',
                        { id: $(e.currentTarget).data('id') }
                    )
                );
            },

            _processResponse: function(data) {
                this.options.contentLoaded = true;

                _.each(data, function(productDraft) {
                    if (productDraft.createdAt) {
                        var date = moment(new Date(productDraft.createdAt * 1000));
                        if (date.isValid()) {
                            var dateFormat = date.isSame(new Date(), 'day') ? 'HH:mm' : 'YYYY-MM-DD HH:mm';
                            productDraft.createdAt = date.format(dateFormat);
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
                    instance = new ProductDraftsWidget(options);
                } else if (_.has(options, 'el')) {
                    instance.setElement(options.el);
                }
                instance.render().delayedLoad();
            }
        };
    }
);
