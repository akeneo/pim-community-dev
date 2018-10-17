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
        'oro/translator',
        'routing',
        'oro/messenger',
        'oro/datagrid-builder',
        'pim/form',
        'pim/user-context',
        'pimee/template/product/tab/proposals',
        'require-context'
    ],
    function (
        $,
        _,
        __,
        Routing,
        messenger,
        datagridBuilder,
        BaseForm,
        UserContext,
        template,
        requireContext
    ) {
        return BaseForm.extend({
            template: _.template(template),
            datagrid: {},

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = _.extend({}, config.config);
            },

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
                        return _.result(
                            _.result(this.getFormData(), 'meta', {}),
                            'is_owner',
                            false
                        );
                    }.bind(this),
                    label: __('pim_menu.item.proposal')
                });

                this.datagrid = {
                    name: this.config.datagridName,
                    paramName: 'entityWithValues'
                };

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Callback triggered when a proposal is successfully approved from the grid
             *
             * @param {Object} product
             */
            onPostApproveSuccess: function (product) {
                this.setData(product);
                this.getRoot().trigger('pim_enrich:form:entity:post_fetch', product);

                messenger.notify(
                    'success',
                    __('pimee_enrich.entity.product_draft.flash.approve.success')
                );
            },

            /**
             * Callback triggered when an error happens on proposal approval from the grid
             *
             * @param {string} message
             */
            onPostApproveError: function (message) {
                messenger.notify(
                    'error',
                    __('pimee_enrich.entity.product_draft.flash.approve.fail', {error: message})
                );
            },

            /**
             * Callback triggered when a proposal is rejected from the grid
             */
            onPostRejectSuccess: function () {
                messenger.notify(
                    'success',
                    __('pimee_enrich.entity.product_draft.flash.reject.success')
                );
            },

            /**
             * Callback triggered when a proposal is removed from the grid
             */
            onPostRemoveSuccess: function () {
                messenger.notify(
                    'success',
                    __('pimee_enrich.entity.product_draft.flash.remove.success')
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
                    params: {dataLocale: UserContext.get('catalog_default_locale')}
                };

                urlParams.params[this.datagrid.paramName] = this.getProductId();

                $.get(Routing.generate('pim_datagrid_load', urlParams))
                    .then(function (response) {
                        this.$('.draft-grid div').data({
                            metadata: response.metadata,
                            data: JSON.parse(response.data)
                        });

                        var resolvedModules = []
                        response.metadata.requireJSModules.forEach(function(module) {
                            resolvedModules.push(requireContext(module))
                        })

                        datagridBuilder(resolvedModules);
                    }.bind(this));
            }
        });
    }
);
