'use strict';

define([
        'jquery',
        'underscore',
        'pim/entity-manager'
    ], function (
        $,
        _,
        EntityManager
    ) {
        return {
            getAttributesForProduct: function (product) {
                if (!product.family) {
                    return $.Deferred().resolve(_.keys(product.values));
                } else {
                    return EntityManager.getRepository('family')
                        .find(product.family)
                        .then(function (family) {
                            return _.union(_.keys(product.values), family.attributes);
                        });
                }
            },
            getOptionalAttributes: function (product) {
                return $.when(
                    EntityManager.getRepository('attribute').findAll(),
                    this.getAttributesForProduct(product)
                ).then(function (attributes, productAttributes) {
                    var optionalAttributes = _.map(
                        _.difference(_.pluck(attributes, 'code'), productAttributes),
                        function (attributeCode) {
                            return _.findWhere(attributes, {code: attributeCode});
                        }
                    );

                    return optionalAttributes;
                });
            },
            isOptional: function (attribute, product, families) {
                return 'pim_catalog_identifier' !== attribute.type &&
                    (!product.family ? true : !_.contains(families[product.family].attributes, attribute.code));
            },
            getValue: function (values, attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return _.findWhere(values, {scope: scope, locale: locale});
            },

            generateValue: function (attribute, locale, scope) {
                locale = attribute.localizable ? locale : null;
                scope  = attribute.scopable ? scope : null;

                return {
                    'locale': locale,
                    'scope':  scope,
                    'value':  attribute.empty_value
                };
            },

            /**
             * Generate all missing values for an attribute
             * @param values
             * @param attribute
             * @param locales
             * @param channels
             * @param currencies
             * @returns {*}
             */
            generateMissingValues: function (values, attribute, locales, channels, currencies) {
                _.each(locales, _.bind(function (locale) {
                    _.each(channels, _.bind(function (channel) {
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
                            newValue.value = this.generateMissingPrices(newValue.value, currencies);
                        }

                    }, this));
                }, this));

                return values;
            },

            generateMissingPrices: function (prices, currencies) {
                _.each(currencies, function (currency) {
                    var price = _.findWhere(prices, {currency: currency.code});

                    if (!price) {
                        price = {data: null, currency: currency.code};
                        prices.push(price);
                    }

                });

                return prices;
            }
        };
    }
);
