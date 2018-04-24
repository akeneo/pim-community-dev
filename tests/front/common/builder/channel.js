const capitalize = source => source.replace(/\b\w/g, l => l.toUpperCase());
const LocaleBuilder = require('./locale');
const createLocale = (localeCode) => (new LocaleBuilder()).setCode(localeCode).build();

/**
 * Generate a channel
 *
 * Example:
 *
 * const ChannelBuilder = require('../../common/builder/channel');
 * const channel = (new ChannelBuilder())
 *   .withCode('ecommerce')
 *   .withLocales(['en_AU'])
 *   .withLabels({ en_AU: 'comm' })
 *   .withCategoryTree('child')
 *   .build();
 */
class ChannelBuilder {
  constructor() {
    this.locales = [];
    this.labels = null;
    this.categoryTree = 'master';
    this.currencies = ['EUR', 'USD'];
    this.conversionUnits = [];
  }

  withCode(code) {
    this.code = code;

    return this;
  }

  withLocales(locales) {
    this.locales = locales;

    return this;
  }

  withLabels(labels) {
    this.labels = labels;

    return this;
  }

  withCategoryTree(categoryTree) {
    this.categoryTree = categoryTree;

    return this;
  }

  withCurrencies(currencies) {
    this.currencies = currencies;

    return this;
  }

  withConversionUnits(conversionUnits) {
    this.conversionUnits = conversionUnits;

    return this;
  }

  build() {
    const activatedLocaleCode = ['en_US', 'fr_FR', 'de_DE'];
    const localeCodes = 0 === this.locales.length ? activatedLocaleCode : this.locales;

    return {
      code: this.code,
      currencies: this.currencies,
      locales: localeCodes.map(localeCode => createLocale(localeCode)),
      category_tree: this.categoryTree,
      conversion_units: this.conversionUnits,
      labels: this.labels || activatedLocaleCode.reduce((result, localeCode) => {
          result[localeCode] = capitalize(this.code);

          return result;
      }, {})
    };
  }
}

module.exports = ChannelBuilder;
