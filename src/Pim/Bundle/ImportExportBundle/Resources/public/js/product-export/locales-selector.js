'use strict';
/**
 * Dynamic locales selector in the product export builder form
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    ['jquery', 'underscore', 'pim/fetcher-registry'],
    function ($, _, FetcherRegistry) {

        /** @type {Object} JQuery object of the locales selector */
        var $target;

        return {
            /**
             * Load the locales choices depending on the channel selected
             */
            reload: function (event) {
                FetcherRegistry.getFetcher('channel')
                    .fetch(event.val)
                    .then(function (channel) {

                        $target.select2('destroy');
                        $target.empty();

                        _.each(channel.locales, function (locale) {
                            $target.append('<option value="' + locale + '">' + locale + '</option>');
                        });

                        $target.select2();
                        $target.select2('data', _.map(channel.locales, function (locale) {
                            return {id: locale, text: locale};
                        }));

                    }.bind(this));
            },

            /**
             * Initialize the behavior by listening to the event on the channel select
             *
             * @param {String} sourceSelector
             * @param {String} targetSelector
             */
            init: function (sourceSelector, targetSelector) {
                $target = $(targetSelector);

                FetcherRegistry.initialize().then(function () {
                    $(sourceSelector).on('select2-selecting', this.reload);
                }.bind(this));
            }
        };
    }
);
