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
        'routing',
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
        Routing,
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
                var root = this.getRoot();
                this.listenTo(root, 'pim_enrich:form:proposal:post_approve:success', this.onPostApproveSuccess);
                this.listenTo(root, 'pim_enrich:form:proposal:post_approve:error', this.onPostApproveError);
                this.listenTo(root, 'pim_enrich:form:proposal:post_reject:success', this.onPostRejectSuccess);
                this.listenTo(root, 'pim_enrich:form:proposal:post_remove:success', this.onPostRemoveSuccess);

                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: function () {
                        return this.getFormData().meta.is_owner;
                    }.bind(this),
                    label: _.__('pimee_enrich.entity.product.tab.proposals.title')
                });

                this.datagrid = {
                    name: 'product-draft-grid',
                    paramName: 'product'
                };

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Callback triggered when a proposal is successfully approved from the grid
             *
             * @param {Object} product
             */
            onPostApproveSuccess: function (product) {
                ProductManager.generateMissing(product)
                    .then(function (product) {
                        this.setData(product);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);

                        messenger.notificationFlashMessage(
                            'success',
                            _.__('pimee_enrich.entity.product.tab.proposals.messages.approve.success')
                        );
                    }.bind(this));
            },

            /**
             * Callback triggered when an error happens on proposal approval from the grid
             *
             * @param {string} message
             */
            onPostApproveError: function (message) {
                messenger.notificationFlashMessage(
                    'error',
                    _.__('pimee_enrich.entity.product.tab.proposals.messages.approve.error', {error: message})
                );
            },

            /**
             * Callback triggered when a proposal is rejected from the grid
             */
            onPostRejectSuccess: function () {
                messenger.notificationFlashMessage(
                    'success',
                    _.__('pimee_enrich.entity.product.tab.proposals.messages.reject.success')
                );
            },

            /**
             * Callback triggered when a proposal is removed from the grid
             */
            onPostRemoveSuccess: function () {
                messenger.notificationFlashMessage(
                    'success',
                    _.__('pimee_enrich.entity.product.tab.proposals.messages.remove.success')
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
                    .then(function (response) {
                        this.$('#grid-' + this.datagrid.name).data({
                            metadata: response.metadata,
                            data: JSON.parse(response.data)
                        });

                        require(response.metadata.requireJSModules, function () {
                            datagridBuilder(_.toArray(arguments));
                        });
                    }.bind(this));
            }
        });
    }
);
