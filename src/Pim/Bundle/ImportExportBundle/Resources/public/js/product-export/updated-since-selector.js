'use strict';
/**
 * "Update time condition" filter in the product export builder form
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['jquery', 'datepicker'],
    function ($, Datepicker) {
        return {
            $exportedSinceStrategy: null,
            $exportedSinceDate: null,
            $validationTooltip: null,
            $legend: null,

            /**
             * @param {string} container
             */
            init: function (container) {
                var $container = $(container);

                this.$exportedSinceStrategy = $container.find('select');
                this.$exportedSinceDate = $container.find('input.datepicker');
                this.$validationTooltip = $container.find('.validation-tooltip');
                this.$legend = $container.find('.legend');

                this._displayDateElement();
                this._displayLegendElement();

                this.$exportedSinceStrategy.on('change', this._displayDateElement.bind(this));
                this.$exportedSinceStrategy.on('change', this._displayLegendElement.bind(this));

                Datepicker.init(this.$exportedSinceDate.parent());
            },

            /**
             * Display or hide the datepicker depending condition time value
             *
             * @private
             */
            _displayDateElement: function () {
                if ('since_date' === this.$exportedSinceStrategy.val()) {
                    this.$exportedSinceDate.show().prop('disabled', false);
                    this.$validationTooltip.show();
                } else {
                    this.$exportedSinceDate.hide().prop('disabled', true);
                    this.$validationTooltip.hide();
                }
            },

            /**
             * Display or hide the legend depending condition time value
             *
             * @private
             */
            _displayLegendElement: function () {
                if ('last_export' === this.$exportedSinceStrategy.val()) {
                    this.$legend.show();
                } else {
                    this.$legend.hide();
                }
            }
        };
    }
);
