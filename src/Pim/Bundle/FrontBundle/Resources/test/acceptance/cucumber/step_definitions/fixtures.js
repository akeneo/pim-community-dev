
module.exports = {
  channelList: `
  [
    {
      "code": "ecommerce",
      "currencies": [
        "USD",
        "EUR"
      ],
      "locales": [
        {
          "code": "de_DE",
          "label": "German (Germany)",
          "region": "Germany",
          "language": "German"
        },
        {
          "code": "en_US",
          "label": "English (United States)",
          "region": "United States",
          "language": "English"
        },
        {
          "code": "fr_FR",
          "label": "French (France)",
          "region": "France",
          "language": "French"
        }
      ],
      "category_tree": "master",
      "conversion_units": [],
      "labels": {
        "en_US": "Ecommerce",
        "de_DE": "Ecommerce",
        "fr_FR": "Ecommerce"
      }
    }
  ]`,
  productList: `
    {
      "items": [
        {
          "identifier": "17851719",
          "family": "scanners",
          "parent": null,
          "enabled": true,
          "values": {
            "sku": [
              {
                "locale": null,
                "scope": null,
                "data": "17851719"
              }
            ],
            "name": [
              {
                "locale": null,
                "scope": null,
                "data": "Avision @V2800"
              }
            ],
            "release_date": [
              {
                "locale": null,
                "scope": "ecommerce",
                "data": "2013-03-01"
              }
            ],
            "color_scanning": [
              {
                "locale": null,
                "scope": null,
                "data": true
              }
            ]
          },
          "created": "2018-01-25T17:47:50+01:00",
          "updated": "2018-01-25T17:48:01+01:00",
          "meta": {
            "form": "pim-product-edit-form",
            "id": 100,
            "model_type": "product",
            "has_children": false,
            "completenesses": [
              {
                "locale": "de_DE",
                "channel": "ecommerce",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "en_US",
                "channel": "ecommerce",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "fr_FR",
                "channel": "ecommerce",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "de_DE",
                "channel": "mobile",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "en_US",
                "channel": "mobile",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "fr_FR",
                "channel": "mobile",
                "ratio": 60,
                "missing": 2,
                "required": 5
              },
              {
                "locale": "de_DE",
                "channel": "print",
                "ratio": 80,
                "missing": 1,
                "required": 5
              },
              {
                "locale": "en_US",
                "channel": "print",
                "ratio": 80,
                "missing": 1,
                "required": 5
              },
              {
                "locale": "fr_FR",
                "channel": "print",
                "ratio": 60,
                "missing": 2,
                "required": 5
              }
            ],
            "image": null,
            "label": {
              "de_DE": "Avision @V2800 in german",
              "en_US": "Avision @V2800 in english",
              "fr_FR": "Avision @V2800 in french"
            },
            "associations": [],
            "ascendant_category_ids": []
          }
        }
      ],
      "total": 1
    }
  `
};
