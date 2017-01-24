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
            notFoundLabel: __('pimee_enrich.asset_transformation.not_found'),

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

                if (!_.has(this.getFormData().meta, 'id')) {
                    this.$el.html(
                        '<div class="no-data AknMessageBox AknMessageBox--centered">' +
                    __('pimee_enrich.asset_transformation.not_found') + '</div>'
                    );
                }
                if (!this.transformationTable && _.has(this.getFormData().meta, 'id')) {
                    $.get(
                        Routing.generate(
                            this.config.url,
                            {id: this.getFormData().meta.id}
                        )
                    ).then(function (table) {
                        this.transformationTable = table;
                        this.$el.html(this.transformationTable);
                    }.bind(this));

                    return this;
                }

                this.$el.html(this.transformationTable);

                return this;
            }
        });
    }
);
