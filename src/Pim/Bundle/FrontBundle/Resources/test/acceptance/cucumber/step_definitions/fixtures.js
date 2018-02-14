const capitalize = source => source.replace(/\b\w/g, l => l.toUpperCase());

const createChannel = (code, locales = [], labels = null, category_tree = null) => {
  const localeCodes = 0 === locales.length ? ['en_US', 'fr_FR', 'de_DE'] : locales;

  return {
    code,
    currencies: ['EUR', 'USD'],
    locales: localeCodes.map(localeCode => createLocale(localeCode)),
    category_tree: null !== category_tree ? category_tree : 'master',
    conversion_units: [],
    labels:
      null !== labels
        ? labels
        : localeCodes.reduce((result, localeCode) => {
            result[localeCode] = capitalize(localeCode);

            return result;
          }, {}),
  };
};

const createLocale = code => {
  const regions = {
    de: 'Germany',
    fr: 'France',
    us: 'United States',
  };

  const languages = {
    de: 'German',
    fr: 'French',
    en: 'English',
  };
  const [region, language] = code.split('_');

  return {
    code,
    label: `${languages[language.toLowerCase()]} (${regions[region]})`,
    region: regions[region],
    language: languages[language.toLowerCase()],
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
      completenesses,
    },
  };
};

const createProductWithLabels = (identifier, labels) => {
  return createProduct(identifier, undefined, undefined, undefined, labels);
};

module.exports = {
  createChannel,
  createLocale,
  createProduct,
  createProductWithLabels,
  // productList: `
  //   {
  //     "items": [
  //       {
  //         "identifier": "17851719",
  //         "family": "scanners",
  //         "parent": null,
  //         "enabled": true,
  //         "values": {
  //           "sku": [
  //             {
  //               "locale": null,
  //               "scope": null,
  //               "data": "17851719"
  //             }
  //           ],
  //           "name": [
  //             {
  //               "locale": null,
  //               "scope": null,
  //               "data": "Avision @V2800"
  //             }
  //           ],
  //           "release_date": [
  //             {
  //               "locale": null,
  //               "scope": "ecommerce",
  //               "data": "2013-03-01"
  //             }
  //           ],
  //           "color_scanning": [
  //             {
  //               "locale": null,
  //               "scope": null,
  //               "data": true
  //             }
  //           ]
  //         },
  //         "created": "2018-01-25T17:47:50+01:00",
  //         "updated": "2018-01-25T17:48:01+01:00",
  //         "meta": {
  //           "form": "pim-product-edit-form",
  //           "id": 100,
  //           "model_type": "product",
  //           "has_children": false,
  //           "completenesses": [
  //             {
  //               "locale": "de_DE",
  //               "channel": "ecommerce",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "en_US",
  //               "channel": "ecommerce",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "fr_FR",
  //               "channel": "ecommerce",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "de_DE",
  //               "channel": "mobile",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "en_US",
  //               "channel": "mobile",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "fr_FR",
  //               "channel": "mobile",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             },
  //             {
  //               "locale": "de_DE",
  //               "channel": "print",
  //               "ratio": 80,
  //               "missing": 1,
  //               "required": 5
  //             },
  //             {
  //               "locale": "en_US",
  //               "channel": "print",
  //               "ratio": 80,
  //               "missing": 1,
  //               "required": 5
  //             },
  //             {
  //               "locale": "fr_FR",
  //               "channel": "print",
  //               "ratio": 60,
  //               "missing": 2,
  //               "required": 5
  //             }
  //           ],
  //           "image": null,
  //           "label": {
  //             "de_DE": "Avision @V2800 in german",
  //             "en_US": "Avision @V2800 in english",
  //             "fr_FR": "Avision @V2800 in french"
  //           },
  //           "associations": [],
  //           "ascendant_category_ids": []
  //         }
  //       }
  //     ],
  //     "total": 1
  //   }
  // `
};
