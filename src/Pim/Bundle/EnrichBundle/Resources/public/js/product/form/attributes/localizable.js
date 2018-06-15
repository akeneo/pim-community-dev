'use strict';
/**
 * Localizable field extension
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/form',
        'pim/fetcher-registry'
    ],
    function ($, _, BaseForm, FetcherRegistry) {
        return BaseForm.extend({
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Add this field extension to the given field event
             *
             * @param {Object} event
             *
             * @returns {Promise}
             */
            addFieldExtension: function (event) {
                var field = event.field;

                if (!field.attribute.localizable) {
                    return;
                }

                var retrieveChannel = FetcherRegistry.getFetcher('channel')
                    .fetch(field.context.scope)
                    .then(function (channel) {
                        if (!this.channelHasLocale(channel, field.context.locale)) {
                            this.updateFieldElements(field.context, field);
                        }
                    }.bind(this));

                event.promises.push(retrieveChannel);

                return this;
            },

            /**
             * Return whether the given channel has the given locale
             *
             * @param {string} channel
             *
             * @returns {boolean}
             */
            channelHasLocale: function (channel, locale) {
                return _.contains(_.pluck(channel.locales, 'code'), locale);
            },

            /**
             * Update the given field by adding element to it
             *
             * @param {Object} field
             */
            updateFieldElements: function (context, field) {
                var message = _.__('pim_enrich.entity.product.module.attribute.locale_unavailable', {
                    channel: context.scope,
                    locale: context.locale
                });
                var element = '<span class="AknFieldContainer-unavailable">' + message + '</span>';

                field.addElement(
                    'field-input',
                    'input_placeholder',
                    element
                );
            }
        });
    }
);
