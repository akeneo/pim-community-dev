'use strict';

/**
 * Asset transformation tab for channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
define([
        'jquery',
        'underscore',
        'pim/form',
        'routing',
        'text!pimee/template/channel/tab/asset-transformation',
        'oro/translator'
    ],
    function (
        $,
        _,
        BaseForm,
        Routing,
        template,
        __
    ) {
        return BaseForm.extend({
            className: 'AknTabContainer-content asset-transformation',
            transformationTable: null,
            template: _.template(template),

            /**
             * @param {Object} config
             */
            initialize: function (config) {
                this.config = _.extend({}, config.config);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {

                this.trigger('tab:register', {
                    code: this.code,
                    label: __(this.config.title)
                });

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                var hasId = _.has(this.getFormData().meta, 'id');
                var notFoundLabel = __('pimee_enrich.asset_transformation.not_found');
                var transformationLabel = __('pimee_enrich.asset_transformation.title.transformation');
                var optionsLabel = __('pimee_enrich.asset_transformation.title.options');
                var configuration = {};

                if (!hasId) {
                    this.$el.html(this.template({
                        hasId: hasId,
                        notFoundLabel: notFoundLabel,
                        transformationLabel: transformationLabel,
                        optionsLabel: optionsLabel,
                        configuration: configuration,
                        __: __
                    }));

                    return this;
                }

                $.get(
                    Routing.generate(
                        this.config.url,
                        {id: this.getFormData().meta.id}
                    )
                ).then(function (configuration) {
                    this.$el.html(this.template({
                        hasId: hasId,
                        notFoundLabel: notFoundLabel,
                        transformationLabel: transformationLabel,
                        optionsLabel: optionsLabel,
                        configuration: configuration,
                        __: __
                    }));
                }.bind(this));

                return this;
            }
        });
    }
);
