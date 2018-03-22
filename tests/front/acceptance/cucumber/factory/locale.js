/**
 * Generate a locale
 *
 * Example:
 *
 * const createLocale = require('../../factory/locale');
 * createLocale('en_US');
 *
 * @param {String} code
 * @returns {Object}
 */
module.exports = function createLocale(code) {
    const languages = { de: 'German', fr: 'French', en: 'English'};
    const regions = { de: 'Germany', fr: 'France', us: 'United States'};
    const [language, region] = code.split('_');
    const languageLabel = language.toLowerCase();

    return {
        code,
        region: regions[region],
        label: `${languages[languageLabel]} (${regions[region]})`,
        language: languages[languageLabel]
    };
};

