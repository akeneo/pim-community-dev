'use strict';
/**
 * Proposals tab extension
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/datagrid-builder',
        'pim/form',
        'pim/user-context',
        'text!pimee/template/product/tab/proposals'
    ],
    function (
        $,
        _,
        datagridBuilder,
        BaseForm,
        UserContext,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            datagrid: {},

            /**
             * Configure this extension
             *
             * @return {Promise}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    displayCondition: _.bind(function () { return this.getFormData().meta.is_owner }, this),
                    label: _.__('pimee_enrich.entity.product.tab.proposals.title')
                });

                this.datagrid = {
                    name: 'product-draft-grid',
                    paramName: 'product'
                };

                //mediator.on('datagrid:selectModel:' + this.datagrid.name, _.bind(this.selectModel, this));
                //mediator.on('datagrid:unselectModel:' + this.datagrid.name, _.bind(this.unselectModel, this));
                //mediator.on('datagrid_collection_set_after', _.bind(this.updateChecked, this));
                //mediator.on('datagrid_collection_set_after', _.bind(this.setDatagrid, this));
                //mediator.on('grid_load:complete', _.bind(this.updateChecked, this));
                //mediator.once('column_form_listener:initialized', _.bind(function onColumnListenerReady(gridName) {
                //    if (!this.configured) {
                //        mediator.trigger(
                //            'column_form_listener:set_selectors:' + gridName,
                //            { included: '#asset-appendfield' }
                //        );
                //    }
                //}, this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Return the current productId
             *
             * @return {number}
             */
            getProductId: function () {
                return this.getFormData().meta.id;
            },

            /**
             * Render the main template
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

                this.$el.html(this.template());
                this.renderGrid(this.datagrid);

                this.renderExtensions();
            },

            /**
             * Build the grid and render it inside the template
             */
            renderGrid: function () {
                var urlParams = {
                    alias: this.datagrid.name,
                    params: {dataLocale: UserContext.get('catalogLocale')}
                };

                urlParams.params[this.datagrid.paramName] = this.getProductId();

                $.get(Routing.generate('pim_datagrid_load', urlParams))
                    .then(_.bind(function (response) {
                        this.$('#grid-' + this.datagrid.name).data({
                            metadata: response.metadata,
                            data: JSON.parse(response.data)
                        });

                        require(response.metadata.requireJSModules, function () {
                            datagridBuilder(_.toArray(arguments));
                        });
                    }, this));
            }
        });
    }
);
