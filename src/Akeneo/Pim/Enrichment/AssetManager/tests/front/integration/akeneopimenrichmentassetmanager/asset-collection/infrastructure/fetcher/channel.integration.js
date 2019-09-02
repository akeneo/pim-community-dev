const timeout = 5000;

let page = global.__PAGE__;

beforeEach(async () => {
  await page.reload();
}, timeout);

it('It fetches the channels', async () => {
  page.on('request', interceptedRequest => {
    if (
      'http://pim.com/configuration/channel/rest' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const channels = [{
          code: 'ecommerce',
          locales: [{
              code: 'en_US',
              label: 'English (United States)',
              region: 'United States',
              language: 'English'
            },
            {
              code: 'fr_FR',
              label: 'French (France)',
              region: 'France',
              language: 'French'
            }
          ],
          labels: {
            en_US: 'Ecommerce'
          }
        },
        {
          code: 'mobile',
          locales: [{
              code: 'en_US',
              label: 'English (United States)',
              region: 'United States',
              language: 'English'
            },
            {
              code: 'fr_FR',
              label: 'French (France)',
              region: 'France',
              language: 'French'
            }
          ],
          labels: {
            en_US: 'Mobile'
          }
        }
      ];

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(channels),
      });
    }
  });

  const response = await page.evaluate(async () => {
    const fetchAssetAttributes = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel').fetchChannels;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();
    const channels = await fetchAssetAttributes(fetcherRegistry.getFetcher('channel'));

    return channels;
  });

  expect(response).toEqual([{
      code: 'ecommerce',
      locales: [{
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English'
        },
        {
          code: 'fr_FR',
          label: 'French (France)',
          region: 'France',
          language: 'French'
        }
      ],
      labels: {
        en_US: 'Ecommerce'
      }
    },
    {
      code: 'mobile',
      locales: [{
          code: 'en_US',
          label: 'English (United States)',
          region: 'United States',
          language: 'English'
        },
        {
          code: 'fr_FR',
          label: 'French (France)',
          region: 'France',
          language: 'French'
        }
      ],
      labels: {
        en_US: 'Mobile'
      }
    }
  ]);
});
