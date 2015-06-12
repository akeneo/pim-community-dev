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
                    (!product.family ? true : !_.contains(families[product.family].attributes, attribute));
            },
            getEmptyValue: function (attribute) {
                switch (attribute.type) {
                    case 'pim_catalog_date':
                    case 'pim_catalog_file':
                    case 'pim_catalog_image':
                    case 'pim_catalog_simpleselect':
                    case 'pim_reference_data_simpleselect':
                    case 'pim_catalog_identifier':
                    case 'pim_catalog_number':
                    case 'pim_catalog_textarea':
                        return null;
                    case 'pim_catalog_metric':
                        return {
                            'data': null,
                            'unit': attribute.default_metric_unit
                        };
                    case 'pim_catalog_multiselect':
                    case 'pim_reference_data_multiselect':
                        return [];
                    case 'pim_catalog_text':
                        return '';
                    case 'pim_catalog_boolean':
                        return false;
                    case 'pim_catalog_price_collection':
                        return [];
                    default:
                        throw new Error(JSON.stringify(attribute));
                }
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
                    'value':  this.getEmptyValue(attribute)
                };
            },
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
