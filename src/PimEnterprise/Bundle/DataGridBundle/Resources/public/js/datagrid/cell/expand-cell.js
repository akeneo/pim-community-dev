define(
    ['jquery', 'underscore', 'oro/translator', 'oro/datagrid/html-cell'],
    function ($, _, __, HtmlCell) {
        'use strict';

        return HtmlCell.extend({
            template: _.template(
                '<div class="proposal-changes" data-collapsed="<%= collapsed ? "true" : "false" %>">' +
                    '<div class="details"><%= changes %></div>' +
                    '<button class="btn btn-mini btn-more pull-right toggle">...</button>' +
                    '<div class="mask"></div>' +
                '</div>'
            ),
            events: {
                'click button.toggle': 'toggle',
                'click .mask': 'toggle'
            },
            collapsed: true,
            expandText: __('pimee_datagrid.cell.expand.expandText'),
            collapseText: __('pimee_datagrid.cell.expand.collapseText'),
            render: function () {
                this.$el.html(this.template({
                    'changes': this.model.get(this.column.get('name')),
                    'collapsed': this.collapsed
                }));

                return this;
            },
            toggle: function () {
                this.collapsed = !this.collapsed;
                this.$el.children().attr('data-collapsed', this.collapsed ? 'true' : 'false');
            },
            initialize: function () {
                return HtmlCell.prototype.initialize.apply(this, arguments);
            }
        });
    }
);
