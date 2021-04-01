import {formatCurrentCompleteness} from "../../../../src/helpers/formatCurrentCompleteness";

test('it formats the current completeness of a product', () => {
  const rawCompleteness = {
    "channel": "ecommerce",
    "stats": {
        "total": 3,
        "complete": 0,
        "average": 70
    },
    "locales": {
        "en_US": {
            "completeness": {
                "required": 5,
                "missing": 2,
                "ratio": 60,
            },
            "missing": [
                {
                    "code": "description",
                    "labels": {
                        "en_US": "Description",
                        "fr_FR": "Description"
                    }
                },
                {
                    "code": "price",
                    "labels": {
                        "en_US": "Price",
                        "fr_FR": "Prix"
                    }
                }
            ],
            "label": "English (United States)"
        },
        "fr_FR": {
            "completeness": {
                "required": 5,
                "missing": 1,
                "ratio": 80,
            },
            "missing": [
                {
                    "code": "price",
                    "labels": {
                        "en_US": "Price",
                        "fr_FR": "Prix"
                    }
                }
            ],
            "label": "French (France)"
        }
    }
  };

  const formattedCurrentCompleteness = formatCurrentCompleteness(rawCompleteness, 'en_US');

  expect(formattedCurrentCompleteness).toEqual({
      channelRatio: 70,
      localesCompleteness: {
          'en_US': {
              label: 'English (United States)',
              ratio: 60,
              missingCount: 2,
              missingAttributes: [
                  {
                      code: 'description',
                      label: 'Description'
                  },
                  {
                      code: 'price',
                      label: 'Price'
                  }
              ]
          },
          'fr_FR': {
              label: 'French (France)',
              ratio: 80,
              missingCount: 1,
              missingAttributes: [
                  {
                      code: 'price',
                      label: 'Price'
                  }
              ]
          }
      }
  });
});
