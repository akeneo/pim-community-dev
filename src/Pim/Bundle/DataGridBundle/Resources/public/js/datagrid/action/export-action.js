define(
    ['jquery', 'underscore', 'backbone', 'oro/translator', 'oro/mediator'],
    function($, _, Backbone, __, mediator) {
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

            url: null,

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
                console.log('initializing');
                if (_.has(options, 'label')) {
                    this.label = __(options.label);
                }
                if (_.has(options, 'icon')) {
                    this.icon = options.icon;
                }
                if (_.has(options, 'url')) {
                    this.url = options.url;
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

                mediator.on('grid_load:complete', _.bind(this.updateUrl, this));
            },

            updateUrl: function(collection) {
                console.log(collection);
            },

            execute: function() {
                console.log('executing');
                console.log(this.url);
                console.log(this.datagrid);
                return;
                var selectionState = this.datagrid.getSelectionState();
                if (_.isEmpty(selectionState.selectedModels) && selectionState.inset) {
                    messenger.notificationFlashMessage('warning', this.messages.empty_selection);
                } else {
                    AbstractAction.prototype.execute.call(this);
                }
            },

            /**
             * Get action parameters
             *
             * @returns {Object}
             * @private
             */
            getActionParameters: function() {
                var selectionState = this.datagrid.getSelectionState();
                var collection = this.datagrid.collection;
                var idValues = _.map(selectionState.selectedModels, function(model) {
                    return model.get(this.identifierFieldName)
                }, this);

                var params = {
                    inset: selectionState.inset ? 1 : 0,
                    values: idValues.join(',')
                };

                params = collection.processFiltersParams(params, null, 'filters');

                return params;
            },

            _onAjaxSuccess: function(data, textStatus, jqXHR) {
                this.datagrid.resetSelectionState();
                AbstractAction.prototype._onAjaxSuccess.apply(this, arguments);
            }
        });

        console.log('loaded');

        ExportAction.init = function ($gridContainer, gridName) {
            var metadata = $gridContainer.data('metadata');
            var options = metadata.options || {};
            if (options.exportAction) {
                new ExportAction(_.extend({ $gridContainer: $gridContainer, gridName: gridName, url: options.url }, options.exportAction));
            }
        };

        return ExportAction;
    }
);
