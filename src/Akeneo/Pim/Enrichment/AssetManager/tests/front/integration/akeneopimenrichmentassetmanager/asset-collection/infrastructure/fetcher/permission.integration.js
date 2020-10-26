const timeout = 10000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get all the permissions
    if ('http://pim.com/permissions/rest' === interceptedRequest.url() && 'GET' === interceptedRequest.method()) {
      const permissions = {
        locales: [
          {
            code: 'en_US',
            view: true,
            edit: true,
          },
          {
            code: 'fr_FR',
            view: true,
            edit: true,
          },
        ],
        attribute_groups: [
          {
            code: 'marketing',
            view: true,
            edit: true,
          },
          {
            code: 'technical',
            view: true,
            edit: true,
          },
        ],
        categories: {
          VIEW_ITEMS: ['master'],
          EDIT_ITEMS: ['scanners'],
          OWN_PRODUCTS: ['master'],
        },
      };

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(permissions),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches all permissions', async () => {
  // It fetches all permissions
  const response = await page.evaluate(async () => {
    // Sometimes this test fails on circle ci. This wait should mitigate that
    await new Promise(resolve => setTimeout(resolve, 500));

    const fetchPermissions = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/permission')
      .fetchPermissions;
    const fetcherRegistry = require('pim/fetcher-registry');
    fetcherRegistry.initialize();

    return await fetchPermissions(fetcherRegistry.getFetcher('permission'))();
  });

  // Check the family returned by the fetcher is the expected one
  expect(response).toEqual({
    locales: [
      {
        code: 'en_US',
        view: true,
        edit: true,
      },
      {
        code: 'fr_FR',
        view: true,
        edit: true,
      },
    ],
    attributeGroups: [
      {
        code: 'marketing',
        view: true,
        edit: true,
      },
      {
        code: 'technical',
        view: true,
        edit: true,
      },
    ],
    categories: {
      VIEW_ITEMS: ['master'],
      EDIT_ITEMS: ['scanners'],
      OWN_PRODUCTS: ['master'],
    },
  });
});
