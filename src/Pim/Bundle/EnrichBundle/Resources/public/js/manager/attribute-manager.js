'use strict';

define(
    [
        'jquery',
        'underscore',
        'pim/fetcher-registry'
    ], function (
        $,
        _,
        FetcherRegistry
    ) {
        return {
            /**
             * Check if an attribute is optional
             *
             * @param {Object} attribute
             * @param {Object} product
             *
             * @return {Promise}
             */
            isOptional: function (attribute, product) {
                var promise = new $.Deferred();

                if ('pim_catalog_identifier' === attribute.type) {
                    promise.resolve(false);
                } else if (undefined !== product.family && null !== product.family) {
                    promise = FetcherRegistry.getFetcher('family').fetch(product.family).then(function (family) {
                        return !_.contains(_.pluck(family.attributes, 'code'), attribute.code);
                    });
                } else {
                    promise.resolve(true);
                }

                return promise;
            },

            /**
             * Get the value in the given collection for the given locale and scope
             *
             * @param {Array}  values
             * @param {Object} attribute
             * @param {string} locale
             * @param {string} scope
             *
             * @return {Object}
             */
            getValue: function (values, attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return _.findWhere(values, { scope: scope, locale: locale });
            },

            /**
             * Generate a single value for the given attribute, scope and locale
             *
             * @param {Object} attribute
             * @param {string} locale
             * @param {string} scope
             *
             * @return {Object}
             */
            generateValue: function (attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return {
                    'locale': locale,
                    'scope':  scope,
                    'data':   attribute.empty_value
                };
            },

            /**
             * Generate all missing values for an attribute
             *
             * @param {Array}  values
             * @param {Object} attribute
             * @param {Array}  locales
             * @param {Array}  channels
             * @param {Array}  currencies
             *
             * @return {Array}
             */
            generateMissingValues: function (values, attribute, locales, channels, currencies) {
                _.each(locales, function (locale) {
                    _.each(channels, function (channel) {
                        var newValue = this.getValue(
                            values,
                            attribute,
                            locale.code,
                            channel.code
                        );

                        if (!newValue) {
                            newValue = this.generateValue(attribute, locale.code, channel.code);
                            values.push(newValue);
                        }

                        if ('pim_catalog_price_collection' === attribute.type) {
                            newValue.data = this.generateMissingPrices(newValue.data, currencies);
                        }
                    }.bind(this));
                }.bind(this));

                return values;
            },

            /**
             * Generate missing prices in the given collection for the given currencies
             *
             * @param {Array} prices
             * @param {Array} currencies
             *
             * @return {Array}
             */
            generateMissingPrices: function (prices, currencies) {
                var generatedPrices = [];
                _.each(currencies, function (currency) {
                    var price = _.findWhere(prices, { currency: currency.code });

                    if (!price) {
                        price = { amount: null, currency: currency.code };
                    }

                    generatedPrices.push(price);
                });

                return _.sortBy(generatedPrices, 'currency');
            }
        };
    }
);
