define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pim/dashboard/abstract-widget', 'moment'],
    function ($, _, Routing, Navigation, AbstractWidget, moment) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'proposal-widget',

            className: 'table table-condensed table-light groups unspaced',

            options: {
                contentLoaded: false
            },

            $viewAllLink: null,

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
                            '<% _.each(data, function (proposal) { %>',
                                '<tr>',
                                    '<td>',
                                        '<%= proposal.createdAt %>',
                                    '</td>',
                                    '<td><%= proposal.author %></td>',
                                    '<td>',
                                        '<a href="javascript:void(0);" data-id="<%= proposal.productId %>">',
                                            '<%= proposal.productLabel %>',
                                        '</a>',
                                    '</td>',
                                    '<td>',
                                        '<a class="btn btn-mini" href="javascript:void(0);" ',
                                            'data-id="<%= proposal.productId %>" data-redirecttab="#proposals">',
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

            viewAllLinkTemplate: _.template(
                [
                    '<a href="javascript:void(0);" class="btn btn-mini btn-transparent pull-right" ',
                        'style="margin-right:5px;">',
                        '<%= _.__("pimee_dashboard.widget.product_drafts.view_all") %>',
                    '</a>'
                ].join('')
            ),

            events: {
                'click a': 'followLink'
            },

            followLink: function (e) {
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

            setElement: function () {
                AbstractWidget.prototype.setElement.apply(this, arguments);

                this._createViewAllLink();

                return this;
            },

            _createViewAllLink: function () {
                if (this.$viewAllLink) {
                    this.$viewAllLink.remove();
                }

                this.$viewAllLink = $(this.viewAllLinkTemplate());
                this.$viewAllLink.on('click', _.bind(this.viewAll, this));

                this.$el.parent().siblings('.widget-header').append(this.$viewAllLink.hide());
            },

            _afterLoad: function () {
                AbstractWidget.prototype._afterLoad.apply(this, arguments);

                if (_.isEmpty(this.data)) {
                    this.$viewAllLink.hide();
                } else {
                    this.$viewAllLink.show();
                }

                return this;
            },

            viewAll: function () {
                Navigation.getInstance().setLocation(Routing.generate('pimee_workflow_proposal_index'));
            },

            _processResponse: function (data) {
                this.options.contentLoaded = true;

                _.each(data, function (proposal) {
                    if (proposal.createdAt) {
                        var date = moment(new Date(proposal.createdAt * 1000));
                        if (date.isValid()) {
                            var dateFormat = date.isSame(new Date(), 'day') ? 'HH:mm' : 'YYYY-MM-DD HH:mm';
                            proposal.createdAt = date.format(dateFormat);
                        }
                    }
                }, this);

                return data;
            }
        });
    }
);
