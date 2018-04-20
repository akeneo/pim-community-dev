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
 *   .setCode('ecommerce')
 *   .setLocales(['en_AU'])
 *   .setLabels({ en_AU: 'comm' })
 *   .setCategoryTree('child');
 */
class ChannelBuilder {
  constructor() {
    this.locales = [];
    this.labels = null;
    this.categoryTree = 'master';
    this.currencies = ['EUR', 'USD'];
    this.conversionUnits = [];
  }

  setLocales(locales) {
    this.locales = locales;
  }

  setLabels(labels) {
    this.labels = labels;
  }

  setCategoryTree(categoryTree) {
    this.categoryTree = categoryTree;
  }

  setCurrencies(currencies) {
    this.currencies = currencies;
  }

  setConversionUnits(conversionUnits) {
    this.conversionUnits = conversionUnits;
  }

  build() {
    const activatedLocaleCode = ['en_US', 'fr_FR', 'de_DE']:

    return {
      code: this.code,
      currencies: this.currencies,
      locales: localeCodes.map(localeCode => createLocale(localeCode)),
      category_tree: this.categoryTree,
      conversion_units: this.conversionUnits,
      labels: this.labels || this.activatedLocaleCode.reduce((result, localeCode) => {
          result[localeCode] = capitalize(this.code);

          return result;
      }, {})
    };
  }
}
