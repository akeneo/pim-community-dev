const timeout = 5000;

let page = global.__PAGE__;

beforeEach(async () => {
  await page.reload();
}, timeout);

it('It fetches all permissions', async () => {
  page.on('request', interceptedRequest => {
    if (
      'http://pim.com/permissions/rest' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const permissions = {
        locales: [{
            code: 'en_US',
            view: true,
            edit: true
          },
          {
            code: 'fr_FR',
            view: true,
            edit: true
          }
        ],
        attribute_groups: [{
            code: 'marketing',
            view: true,
            edit: true
          },
          {
            code: 'technical',
            view: true,
            edit: true
          }
        ],
        categories: {
          VIEW_ITEMS: ['master'],
          EDIT_ITEMS: ['scanners'],
          OWN_PRODUCTS: ['master']
        }
      };

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(permissions),
      });
    }
  });

  const response = await page.evaluate(async () => {
    const fetchPermissions =
      require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/permission')
      .fetchPermissions;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();
    const permissions = await fetchPermissions(fetcherRegistry.getFetcher('permission'))();

    return permissions;
  });

  expect(response).toEqual({
    locales: [{
        code: 'en_US',
        view: true,
        edit: true
      },
      {
        code: 'fr_FR',
        view: true,
        edit: true
      }
    ],
    attributeGroups: [{
        code: 'marketing',
        view: true,
        edit: true
      },
      {
        code: 'technical',
        view: true,
        edit: true
      }
    ],
    categories: {
      VIEW_ITEMS: ['master'],
      EDIT_ITEMS: ['scanners'],
      OWN_PRODUCTS: ['master']
    }
  });
});
