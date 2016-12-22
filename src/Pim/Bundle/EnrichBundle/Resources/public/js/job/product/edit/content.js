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
        'oro/translator',
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
        __,
        Backbone,
        template,
        BaseForm,
        mediator,
        FetcherRegistry
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (config) {
                this.config = config.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __(this.config.tabTitle)
                });
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:validation_error', this.render.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inherit}
             */
            render: function () {
                if (!this.configured) {
                    return this;
                }

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
