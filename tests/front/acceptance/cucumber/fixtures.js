const capitalize = source => source.replace(/\b\w/g, l => l.toUpperCase());
const activatedLocaleCode = ['en_US', 'fr_FR', 'de_DE'];

const createChannel = (code, locales = [], labels = null, category_tree = null) => {
    const localeCodes = 0 === locales.length ? activatedLocaleCode : locales;

    return {
        code,
        currencies: ['EUR', 'USD'],
        locales: localeCodes.map(localeCode => createLocale(localeCode)),
        category_tree: null !== category_tree ? category_tree : 'master',
        conversion_units: [],
        labels:
      null !== labels
          ? labels
          : activatedLocaleCode.reduce((result, localeCode) => {
              result[localeCode] = capitalize(code);

              return result;
          }, {})
    };
};

const createLocale = code => {
    const regions = {
        de: 'Germany',
        fr: 'France',
        us: 'United States'
    };

    const languages = {
        de: 'German',
        fr: 'French',
        en: 'English'
    };
    const [language, region] = code.split('_');

    return {
        code,
        label: `${languages[language.toLowerCase()]} (${regions[region]})`,
        region: regions[region],
        language: languages[language.toLowerCase()]
    };
};

const createProduct = (
    identifier,
    values = [],
    enabled = true,
    family = 'scanner',
    label = {},
    model_type = 'product',
    image = null,
    completenesses = []
) => {
    return {
        identifier,
        values,
        enabled,
        family,
        meta: {
            label,
            model_type,
            image,
            completenesses
        }
    };
};

const createProductWithLabels = (identifier, labels) => {
    return createProduct(identifier, undefined, undefined, undefined, labels);
};

module.exports = {
    createChannel,
    createLocale,
    createProduct,
    createProductWithLabels
};
