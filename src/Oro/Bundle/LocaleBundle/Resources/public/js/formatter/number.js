/* global define */
define(['numeral', 'oro/locale-settings'],
function(numeral, localeSettings) {
    'use strict';

    /**
     * Number Formatter
     *
     * @export oro/formatter/number
     * @name   oro.formatter.number
     */
    var NumberFormatter = function() {
        var createFormat = function(options) {
            var format = !options.grouping_used ? '0' : '0,0';

            if (options.max_fraction_digits > 0) {
                format += '.';
                for (var i = 0; i < options.max_fraction_digits; ++i) {
                    if (options.min_fraction_digits == i) {
                        format += '[';
                    }
                    format += '0';
                }
                if (-1 !== format.indexOf('[')) {
                    format += ']'
                }
            }

            if (options.style == 'percent') {
                format += '%';
            }

            return format;
        };

        var formatters = {
            numeralFormat: function(value, options) {
                var originLanguage = numeral.language();
                numeral.language('en');
                var result = numeral(value).format(createFormat(options));
                if (result === '0') {
                    result = options.zero_digit_symbol;
                }
                numeral.language('en');
                return result;
            },
            addPrefixSuffix: function(formattedNumber, options, originalNumber) {
                var prefix = '', suffix  = '';
                if (originalNumber > 0) {
                    prefix = options.positive_prefix;
                    suffix = options.positive_suffix;
                } else if (originalNumber < 0)  {
                    formattedNumber = formattedNumber.replace('-', '');
                    prefix = options.negative_prefix;
                    suffix = options.negative_suffix;
                }
                return prefix + formattedNumber + suffix;
            },
            replaceSeparator: function(formattedNumber, options) {
                var defaultGroupingSeparator = ',', defaultDecimalSeparator = '.';
                var groupingSeparator, decimalSeparator;
                if (defaultGroupingSeparator != options.grouping_separator_symbol) {
                    formattedNumber = formattedNumber.replace(defaultGroupingSeparator, options.grouping_separator_symbol);
                }
                if (defaultDecimalSeparator != options.decimal_separator_symbol) {
                    formattedNumber = formattedNumber.replace(defaultDecimalSeparator, options.decimal_separator_symbol);
                }
                return formattedNumber;
            },
            replaceMonetarySeparator: function(formattedNumber, options) {
                var defaultGroupingSeparator = ',', defaultDecimalSeparator = '.';
                if (defaultGroupingSeparator != options.monetary_grouping_separator_symbol) {
                    formattedNumber = formattedNumber.replace(defaultGroupingSeparator, options.monetary_grouping_separator_symbol);
                }
                if (defaultDecimalSeparator != options.monetary_separator_symbol) {
                    formattedNumber = formattedNumber.replace(defaultDecimalSeparator, options.monetary_separator_symbol);
                }
                return formattedNumber;
            },
            clearPercent: function(formattedNumber) {
                return formattedNumber.replace('%', '');
            },
            replaceCurrency: function(formattedNumber, options) {
                return formattedNumber.replace(
                    options.currency_symbol,
                    localeSettings.getCurrencySymbol(options.currency_code)
                );
            }
        };

        var doFormat = function(value, options, formattersChain) {
            var result = value;
            for (var i = 0; i < formattersChain.length; ++i) {
                var formatter = formattersChain[i];
                result = formatter.call(this, result, options, value);
            }
            return result;
        };

        return {
            formatDecimal: function(value) {
                var options = localeSettings.getNumberFormats('decimal');
                options.style = 'decimal';
                var formattersChain = [
                    formatters.numeralFormat,
                    formatters.replaceSeparator,
                    formatters.addPrefixSuffix
                ];
                return doFormat(value, options, formattersChain);
            },
            formatInteger: function(value) {
                var options = localeSettings.getNumberFormats('decimal');
                options.style = 'integer';
                options.max_fraction_digits = 0;
                options.min_fraction_digits = 0;
                var formattersChain = [
                    formatters.numeralFormat,
                    formatters.replaceSeparator,
                    formatters.addPrefixSuffix
                ];
                return doFormat(value, options, formattersChain);
            },
            formatPercent: function(value) {
                var options = localeSettings.getNumberFormats('percent');
                options.style = 'percent';
                var formattersChain = [
                    formatters.numeralFormat,
                    formatters.replaceSeparator,
                    formatters.clearPercent,
                    formatters.addPrefixSuffix
                ];
                return doFormat(value, options, formattersChain);
            },
            formatCurrency: function(value, currency) {
                var options = localeSettings.getNumberFormats('currency');
                if (!currency) {
                    currency = localeSettings.getCurrency()
                }
                options.style = 'currency';
                options.currency_code = currency;
                var formattersChain = [
                    formatters.numeralFormat,
                    formatters.replaceSeparator,
                    formatters.addPrefixSuffix,
                    formatters.replaceCurrency
                ];
                return doFormat(value, options, formattersChain);
            },
            unformat: function(value) {
                var options = localeSettings.getNumberFormats('decimal');
                var result = String(value);
                var defaultGroupingSeparator = ',', defaultDecimalSeparator = '.';
                result = result.replace(options.grouping_separator_symbol, defaultGroupingSeparator);
                result = result.replace(options.decimal_separator_symbol, defaultDecimalSeparator);

                var originLanguage = numeral.language();
                result = numeral().unformat(result);
                numeral.language(originLanguage);

                return result;
            }
        }
    };

    var numberFormatter = NumberFormatter();
//    // decimal
//    console.log('decimal', numberFormatter.formatDecimal(0));
//    console.log('decimal', numberFormatter.formatDecimal(-123456789.123456));
//    console.log('decimal', numberFormatter.formatDecimal(-123456789.123456));
//
//    // currency
//    console.log('currency', numberFormatter.formatCurrency(123456789.123456));
//    console.log('currency', numberFormatter.formatCurrency(-123456789.123456));
//
//    // integer
//    console.log('currency', numberFormatter.formatCurrency(123456789.123456));
//    console.log('integer', numberFormatter.formatInteger(-123456789.5));
//
//    // percent
//    console.log('percent', numberFormatter.formatPercent(0.10));
//    console.log('percent', numberFormatter.formatPercent(-0.10));
//    console.log('percent', numberFormatter.formatPercent(1));

    return numberFormatter;
});
