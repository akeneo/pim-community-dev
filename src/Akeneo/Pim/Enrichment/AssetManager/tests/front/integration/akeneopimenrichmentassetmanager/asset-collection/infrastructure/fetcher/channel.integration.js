const timeout = 5000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the channels
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

  await page.reload();
}, timeout);

it('It fetches the channels', async () => {
  // It fetches the channels
  const response = await page.evaluate(async () => {
    const fetchAssetAttributes =
      require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/channel')
      .fetchChannels;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();

    return await fetchAssetAttributes(fetcherRegistry.getFetcher('channel'))();
  });

  // Check the channels returned by the fetcher are the one expected
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
