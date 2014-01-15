define(
    ['jquery', 'underscore', 'backbone', 'oro/translator'],
    function($, _, Backbone, __) {
        'use strict';

        /**
         * Export action
         *
         * @export  pim/datagrid/export-action
         * @class   pim.datagrid.ExportAction
         * @extends Backbone.View
         */
        var ExportAction = Backbone.View.extend({

            label: __('Export CSV'),

            icon: 'download',

            target: 'div.grid-toolbar>.pull-left',

            originalButtonSelector: 'div.grid-toolbar .mass-actions-panel .action.btn',

            originalButtonIcon: 'download',

            originalButton: null,

            template: _.template(
                '<div class="export-actions-panel btn-group buffer-left">' +
                    '<div class="btn-group">' +
                        '<a href="javascript:void(0);" class="action btn no-hash" title="<%= label %>">' +
                            '<i class="icon-<%= icon %>"></i>' +
                            '<%= label %>' +
                        '</a>' +
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
                if (_.has(options, 'originalButtonIcon')) {
                    this.originalButtonIcon = options.originalButtonIcon;
                }

                if (!options.$gridContainer) {
                    throw new Error('Grid selector is not specified');
                }

                this.$gridContainer = options.$gridContainer;
                this.gridName = options.gridName;

                Backbone.View.prototype.initialize.apply(this, arguments);

                this.render();
            },

            render: function () {
                this.$gridContainer
                    .find(this.target)
                    .append(
                        this.template({
                            icon: this.icon,
                            label: this.label
                        })
                    )
                    .on('click', '.export-actions-panel a.btn.action', _.bind(this.execute, this));

                this.originalButton = this.$gridContainer.find(this.originalButtonSelector).find('.icon-' + this.originalButtonIcon).parent();
                this.originalButton.hide();
            },

            execute: function() {
                this.originalButton.click();
            }
        });

        ExportAction.init = function ($gridContainer, gridName) {
            var metadata = $gridContainer.data('metadata');
            var options = metadata.options || {};
            if (options.exportAction) {
                new ExportAction(_.extend({ $gridContainer: $gridContainer, gridName: gridName }, options.exportAction));
            }
        };

        return ExportAction;
    }
);
