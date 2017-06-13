'use strict';

/**
 * Extension to display breadcrumbItems on every page
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/common/breadcrumbs',
        'oro/mediator',
        'pim/form-registry'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        mediator,
        FormRegistry
    ) {
        return BaseForm.extend({
            className: 'AknBreadcrumb',
            template: _.template(template),
            events: {
                'click .breadcrumb-tab': 'redirectTab',
                'click .breadcrumb-item': 'redirectItem'
            },
            breadcrumbTab: null,
            breadcrumbItem: null,

            /**
             * {@inheritdoc}
             *
             * @param {string} config.tab The main tab to highlight
             * @param {string} [config.item] The sub item to highlight (optional)
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.trigger('pim_menu:highlight:tab', { extension: this.config.tab });
                mediator.trigger('pim_menu:highlight:item', { extension: this.config.item });

                $.when(
                    FormRegistry.getFormMeta(this.config.tab),
                    FormRegistry.getFormMeta(this.config.item)
                ).then(function (metaTab, metaItem) {
                    this.breadcrumbTab = {
                        code: this.config.tab,
                        label: __(metaTab.config.title)
                    };
                    if (undefined !== metaItem) {
                        this.breadcrumbItem = {
                            code: this.config.item,
                            label: __(metaItem.config.title)
                        };
                    }
                }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template({
                    breadcrumbTab: this.breadcrumbTab,
                    breadcrumbItem: this.breadcrumbItem
                }));
            },

            /**
             * Redirects to the linked route
             *
             * @param {Event} event
             */
            redirectTab: function () {
                mediator.trigger('pim_menu:redirect:tab', {extension: this.config.tab});
            },

            /**
             * Redirects to the linked route
             *
             * @param {Event} event
             */
            redirectItem: function () {
                mediator.trigger('pim_menu:redirect:item', {extension: this.config.item});
            }
        });
    });
