/**
 * Generate a locale
 *
 * Example:
 *
 * const LocaleBuilder = require('../../common/builder/locale');
 * const locale = (new LocaleBuilder()).withCode('en_US').build();
 */
class LocaleBuilder {
  constructor() {
    this.languages = { de: 'German', fr: 'French', en: 'English'};
    this.regions = { DE: 'Germany', FR: 'France', US: 'United States'};
  }

  withCode(code) {
    this.code = code;

    return this;
  }

  build() {
    const [language, region] = this.code.split('_');
    const languageLabel = language.toLowerCase();

    return {
      code: this.code,
      region: this.regions[region],
      label: `${this.languages[languageLabel]} (${this.regions[region]})`,
      language: this.languages[languageLabel]
    };
  }
}

module.exports = LocaleBuilder;

