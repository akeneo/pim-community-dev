define(
    ['jquery', 'underscore', 'routing', 'oro/navigation', 'pim/dashboard/abstract-widget', 'text!pimee/templates/dashboard/proposal-widget'],
    function ($, _, Routing, Navigation, AbstractWidget, widgetTemplate) {
        'use strict';

        return AbstractWidget.extend({
            tagName: 'table',

            id: 'proposal-widget',

            className: 'table table-condensed table-light groups unspaced',

            options: {
                contentLoaded: false
            },

            $viewAllLink: null,

            template: _.template(widgetTemplate),

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

                //if ($(e.currentTarget).data('redirecttab')) {
                //    sessionStorage.setItem('redirectTab', $(e.currentTarget).data('redirecttab'));
                //}

                console.log($(e.currentTarget).data('proposalid'));

                var gridParameters = {'|g/f' : {'author' : {'value' : {'0': 'mary'}}}, 'f' : {'product' : {'value' : {'0': $(e.currentTarget).data('proposalid')}}}};

                Navigation.getInstance().setLocation(
                    Routing.generate(
                        'pimee_workflow_proposal_index',
                        gridParameters
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

                return data;
            }
        });
    }
);
