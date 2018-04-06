const capitalize = source => source.replace(/\b\w/g, l => l.toUpperCase());
const createLocale = require('./locale');

/**
 * Generate a channel
 *
 * Example:
 *
 * const createChannel = require('../../factory/channel');
 * createChannel('ecommerce', [ 'en_AU' ], { en_AU: 'comm' }, 'child');
 *
 * @param {String} code
 * @param {Array} locales
 * @param {Object} labels
 * @param {String} category_tree
 * @returns {Object}
 */
module.exports = (code, locales = [], labels = null, category_tree = null) => {
    const activatedLocaleCode = ['en_US', 'fr_FR', 'de_DE'];
    const localeCodes = 0 === locales.length ? activatedLocaleCode : locales;

    return {
        code,
        currencies: ['EUR', 'USD'],
        locales: localeCodes.map(localeCode => createLocale(localeCode)),
        category_tree: category_tree || 'master',
        conversion_units: [],
        labels: labels || activatedLocaleCode.reduce((result, localeCode) => {
            result[localeCode] = capitalize(code);

            return result;
        }, {})
    };
};
