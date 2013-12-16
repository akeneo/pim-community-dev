define(
    ['oro/grid/toolbar', 'oro/grid/actions-panel'],
    function(Toolbar, ActionsPanel) {
        'use strict';

        /**
         * Datagrid toolbar widget
         *
         * @override
         * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
         *
         * @author  Romain Monceau <romain@akeneo.com>
         * @export  oro/grid/toolbar
         * @class   pim.datagrid.Toolbar
         * @extends oro.grid.Toolbar
         */
        return Toolbar.extend({
            /**
             * @override
             * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
             *
             * @property
             */
            template:_.template(
                '<div class="grid-toolbar">' +
                    '<div class="pull-left">' +
                        '<div class="mass-actions-panel btn-group icons-holder"></div>' +
                        '<div class="btn-group icons-holder" style="display: none;">' +
                            '<button class="btn"><i class="icon-edit hide-text"><%- _.__("edit") %></i></button>' +
                            '<button class="btn"><i class="icon-copy hide-text"><%- _.__("copy") %></i></button>' +
                            '<button class="btn"><i class="icon-trash hide-text"><%- _.__("remove") %></i></button>' +
                        '</div>' +
                        '<div class="export-actions-panel btn-group buffer-left"></div>' +
                        '<div class="btn-group" style="display: none;">' +
                            '<button data-toggle="dropdown" class="btn dropdown-toggle"><%- _.__("Status") %>: <strong><%- _.__("All") %></strong><span class="caret"></span></button>' +
                            '<ul class="dropdown-menu">' +
                                '<li><a href="#"><%- _.__("only short") %></a></li>' +
                                '<li><a href="#"><%- _.__("this is long text for test") %></a></li>' +
                            '</ul>' +
                        '</div>' +
                    '</div>' +
                    '<div class="pull-right">' +
                    '<div class="actions-panel pull-right form-horizontal"></div>' +
                    '<div class="page-size pull-right form-horizontal"></div>' +
                    '</div>' +
                    '<div class="pagination pagination-centered"></div>' +
                '</div>'
            ),

            /** @property */
            exportActionsPanel: ActionsPanel,

            /**
             * @override
             * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
             *
             * @param {Array} options.exportActions List of export actions
             *
             * Redefine initialize method adding exportActionsPanel
             */
            initialize: function(options) {
                options = options || {};

                Toolbar.prototype.initialize.call(this, options);

                this.exportActionsPanel = new this.exportActionsPanel();
                if (options.exportActions) {
                    this.exportActionsPanel.setActions(options.exportActions);
                }
            },

            /**
             * @override
             * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
             *
             * Enable toolbar
             */
            enable: function() {
                Toolbar.prototype.enable.call(this);
                this.exportActionsPanel.enable();

                return this;
            },

            /**
             * @override
             * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
             *
             * Disable toolbar
             */
            disable: function() {
                Toolbar.prototype.disable.call(this);
                this.exportActionsPanel.disable();

                return this;
            },

            /**
             * @override
             * @see Oro/Bundle/GridBundle/Resources/public/js/datagrid/toolbar.js
             *
             * Render toolbar with pager and other views
             * Override to add export actions panel
             */
            render: function() {
                Toolbar.prototype.render.call(this);

                this.$('.export-actions-panel').append(this.exportActionsPanel.render().$el);

                return this;
            }
        });
    }
);
