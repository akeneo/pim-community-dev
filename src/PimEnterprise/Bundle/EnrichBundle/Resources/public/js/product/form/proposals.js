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
        'oro/mediator',
        'oro/messenger',
        'oro/datagrid-builder',
        'pim/form',
        'pim/product-manager',
        'pim/user-context',
        'text!pimee/template/product/tab/proposals'
    ],
    function (
        $,
        _,
        mediator,
        messenger,
        datagridBuilder,
        BaseForm,
        ProductManager,
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
                this.listenTo(mediator, 'product:action:proposal:post_approve:success', this.onPostApproveSuccess);
                this.listenTo(mediator, 'product:action:proposal:post_approve:error', this.onPostApproveError);
                this.listenTo(mediator, 'product:action:proposal:post_reject:success', this.onPostRejectSuccess);

                this.trigger('tab:register', {
                    code: this.code,
                    displayCondition: _.bind(function () { return this.getFormData().meta.is_owner }, this),
                    label: _.__('pimee_enrich.entity.product.tab.proposals.title')
                });

                this.datagrid = {
                    name: 'product-draft-grid',
                    paramName: 'product'
                };

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            onPostApproveSuccess: function (product) {
                ProductManager.generateMissing(product)
                    .then(_.bind(function (product) {
                        this.setData(product, {silent: true});
                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_enrich.entity.product.tab.proposals.messages.approve.success')
                        );
                    }, this));
            },

            onPostApproveError: function (message) {
                messenger.notificationFlashMessage(
                    'error',
                    _.__('pimee_enrich.entity.product.tab.proposals.messages.approve.error', {error: message})
                );
            },

            onPostRejectSuccess: function () {
                messenger.notificationFlashMessage(
                    'success',
                    _.__('pimee_enrich.entity.product.tab.proposals.messages.reject.success')
                );
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
