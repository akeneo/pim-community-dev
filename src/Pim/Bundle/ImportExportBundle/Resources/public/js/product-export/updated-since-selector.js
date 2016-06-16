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
            $exportedSinceNDays: null,
            $validationTooltip: null,
            $legend: null,

            /**
             * @param {string} container
             */
            init: function (container) {
                var $container = $(container);

                this.$exportedSinceStrategy = $container.find('select');
                this.$exportedSinceDate = $container.find('.exported-since-date-wrapper input');
                this.$exportedSinceNDays = $container.find('.exported-since-n-days-wrapper input');
                this.$dateValidationTooltip = $container.find('.exported-since-date-wrapper .validation-tooltip');
                this.$nDaysValidationTooltip = $container.find('.exported-since-n-days-wrapper .validation-tooltip');
                this.$legend = $container.find('.legend');

                this._displayDateElement();
                this._displayLegendElement();
                this._displayPeriodElement();

                this.$exportedSinceStrategy.on('change', this._displayDateElement.bind(this));
                this.$exportedSinceStrategy.on('change', this._displayLegendElement.bind(this));
                this.$exportedSinceStrategy.on('change', this._displayPeriodElement.bind(this));

                Datepicker.init(this.$exportedSinceDate.parent());
            },

            /**
             * Display or hide the datepicker depending condition time value
             *
             * @private
             */
            _displayDateElement: function () {
                if ('since_date' === this.$exportedSinceStrategy.val()) {
                    this.$exportedSinceDate.show();
                    this.$dateValidationTooltip.show();
                    this._disableElement(this.$exportedSinceDate);
                } else {
                    this.$exportedSinceDate.hide().prop('disabled', true);
                    this.$dateValidationTooltip.hide();
                }
            },

            /**
             * Display or hide the n days depending condition time value
             *
             * @private
             */
            _displayPeriodElement: function () {
                if ('since_n_days' === this.$exportedSinceStrategy.val()) {
                    this.$exportedSinceNDays.show();
                    this.$nDaysValidationTooltip.show();
                    this._disableElement(this.$exportedSinceNDays);
                } else {
                    this.$exportedSinceNDays.hide().prop('disabled', true);
                    this.$nDaysValidationTooltip.hide();
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
            },

            /**
             * Disable an filter value if the strategy is disabled
             *
             * @param {object} $element
             *
             * @private
             */
            _disableElement: function ($element) {
                if (!this.$exportedSinceStrategy.is(':disabled')) {
                    $element.prop('disabled', false);
                }
            }
        };
    }
);
