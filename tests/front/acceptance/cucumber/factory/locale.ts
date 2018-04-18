interface Languages {
    de: string,
    fr: string,
    en: string
    [key: string]: any;
}

interface Regions {
    de: string,
    fr: string,
    us: string
    [key: string]: any;
}

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
export default function createLocale(code: string) {
    const languages: Languages = { de: 'German', fr: 'French', en: 'English'};
    const regions: Regions = { de: 'Germany', fr: 'France', us: 'United States'};
    const [language, region] = code.split('_');
    const languageLabel = language.toLowerCase();

    return {
        code,
        region: regions[region],
        label: `${languages[languageLabel]} (${regions[region]})`,
        language: languages[languageLabel]
    };
};

