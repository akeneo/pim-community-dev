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
        FetcherRegistry
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inherit}
             */
            configure: function () {
                this.unbindEvents();
                Backbone.Router.prototype.once('route', this.unbindEvents);

                if (_.has(module.config(), 'forwarded-events')) {
                    this.forwardMediatorEvents(module.config()['forwarded-events']);
                }

                this.listenTo(this.getRoot(), 'pim_enrich:form:export:set_code', this.setJobCode.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }
                this.getRoot().trigger('pim_enrich:form:render:before');

                this.$el.html(
                    this.template({})
                );

                this.renderExtensions();
            },

            setJobCode: function (jobCode) {
                this.setData(
                    {jobCode: jobCode},
                    {silent: true}
                );
            },

            /**
             * Remove all events binded to this form.
             */
            unbindEvents: function () {
                mediator.clear('pim_enrich:form');
            },

            /**
             * Clear the fetcher registry
             */
            clearCache: function () {
                FetcherRegistry.clearAll();
                this.render();
            }
        });
    }
);
