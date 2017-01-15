'use strict';

/**
 * Asset transformation tab for channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
define([
        'underscore',
        'pim/form',
        'oro/translator'
    ],
    function (
        _,
        BaseForm,
        __
    ) {
        return BaseForm.extend({
            className: 'AknTabContainer-content tabbable tabs-left asset-transformation',
            transformationTable: null,

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
                if (!this.transformationTable) {
                    this.transformationTable = new Table(
                        'asset-transformation',
                        {
                            channel_id: this.getFormData().meta.id
                        }
                    );
                }

                this.$el.empty().append(this.transformationTable.render().$el);

                return this;
            }
        });
    }
);
