'use strict';

/**
 * Base extension for menu
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'pim/form',
        'oro/mediator',
        'text!pim/template/menu/menu'
    ],
    function (
        _,
        BaseForm,
        mediator,
        template
    ) {
        return BaseForm.extend({
            className: 'AknHeader',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                mediator.on('pim_menu:highlight', this.highlight, this);

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.empty().append(this.template());

                return BaseForm.prototype.render.apply(this, arguments);
            },

            /**
             * This method will activate/deactivate the tabs and items from a list of routes. When elements are
             * activated, it sends to the breadcrumbs extension the list of activated breadcrumb items.
             *
             * @param {Event}         event
             * @param {string[]}      event.routes
             * @param {Backbone.View} event.origin
             */
            highlight: function (event) {
                _.each(this.extensions, function (extension) {
                    extension.setActive(event.routes);
                });

                var breadcrumbItems = _.reduce(this.extensions, function (p, extension) {
                    return _.union(p, extension.getBreadcrumbItems(event.routes));
                }, []);

                event.origin.setBreadcrumbItems(breadcrumbItems);
            }
        });
    });
