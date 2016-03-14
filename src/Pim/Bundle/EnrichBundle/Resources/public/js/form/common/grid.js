'use strict';

define([
        'oro/datagrid-builder',
        'routing',
        'oro/mediator',
        'text!pim/template/form/grid',
    ],
    function(
        datagridBuilder,
        Routing,
        mediator,
        template
    ) {
        return Backbone.View.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (alias, options) {
                this.alias     = alias;
                this.selection = options.selection || [];
                this.options   = options;

                mediator.on('datagrid:selectModel:' + this.alias, function (model) {
                    this.addElement(model.get('id'));
                }.bind(this));

                mediator.on('datagrid:unselectModel:' + this.alias, function (model) {
                    this.removeElement(model.get('id'));
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                this.renderGrid(this.alias, this.options);

                return this;
            },

            /**
             * Render the given grid
             *
             * @param {String} alias
             * @param {Object} params
             */
            renderGrid: function (alias, params) {
                var urlParams    = params;
                urlParams.alias  = alias;
                urlParams.params = _.clone(params);

                $.get(Routing.generate('pim_datagrid_load', urlParams)).then(function (response) {
                    this.$el.find('.grid-drop').data({
                        metadata: response.metadata,
                        data: JSON.parse(response.data)
                    });

                    require(response.metadata.requireJSModules, function () {
                        datagridBuilder(_.toArray(arguments));
                    });
                }.bind(this));
            },

            getSelection: function () {
                return this.selection;
            },

            addElement: function (element) {
                this.selection = _.union(this.selection, [parseInt(element)]);
                this.trigger('grid:selection:updated', this.selection);
            },

            removeElement: function (element) {
                this.selection = _.without(this.selection, parseInt(element));
                this.trigger('grid:selection:updated', this.selection);
            },

            /**
             * Ask for a refresh of the grid (aware that we should not call the mediator for that but we don't have
             * the choice for now)
             */
            refresh: function () {
                mediator.trigger('datagrid:doRefresh:' + this.alias);
            }
        });
    }
);
