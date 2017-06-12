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
        'underscore',
        'pim/form',
        'pim/template/common/breadcrumbs',
        'oro/mediator',
        'pim/router'
    ],
    function (
        _,
        BaseForm,
        template,
        mediator,
        router
    ) {
        return BaseForm.extend({
            className: 'AknBreadcrumb',
            template: _.template(template),
            breadcrumbItems: [],
            events: {
                'click .breadcrumbItems-item': 'redirect'
            },

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                mediator.trigger('pim_menu:highlight', {
                    routes: this.config.path,
                    origin: this
                });
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template({
                    breadcrumbItems: this.breadcrumbItems
                }));
            },

            /**
             * Set the current breadcrumbItems information.
             * Event breadcrumb item contains code, route and label.
             *
             * @param {Array} breadcrumbItems
             */
            setBreadcrumbItems: function (breadcrumbItems) {
                this.breadcrumbItems = breadcrumbItems;
            },

            /**
             * Redirects to the linked route
             *
             * @param {Event} event
             */
            redirect: function (event) {
                var code = event.currentTarget.dataset.code;
                var breadcrumb = _.findWhere(this.breadcrumbItems, { code: code });
                if (undefined !== breadcrumb.route) {
                    router.redirectToRoute(breadcrumb.route);
                }
            }
        });
    });
