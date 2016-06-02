'use strict';
/**
 * Content form
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'module',
        'underscore',
        'backbone',
        'text!pim/template/export/product/edit/content',
        'pim/form',
        'oro/mediator',
        'pim/fetcher-registry',
        'pim/field-manager'
    ],
    function (
        module,
        _,
        Backbone,
        template,
        BaseForm,
        mediator,
        FetcherRegistry,
        FieldManager
    ) {
        return BaseForm.extend({
            template: _.template(template),
            configure: function () {
                mediator.clear('pim_enrich:form');
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.getRoot().trigger('pim_enrich:form:render:before');

                this.$el.html(
                    this.template({})
                );

                this.renderExtensions();

                this.getRoot().trigger('pim_enrich:form:render:after');
            },
            unbindEvents: function () {
                mediator.clear('pim_enrich:form');
            },
            clearCache: function () {
                FetcherRegistry.clearAll();
                this.render();
            }
        });
    }
);
