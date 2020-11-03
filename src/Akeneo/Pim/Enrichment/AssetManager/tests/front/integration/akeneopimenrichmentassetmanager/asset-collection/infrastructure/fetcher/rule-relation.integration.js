const timeout = 10000;

let page = global.__PAGE__;

// Setup to intercept the calls and return a fake response
beforeEach(async () => {
  page.on('request', interceptedRequest => {
    // Intercept the call to get the rule relations
    if (
      'http://pim.com/rest/rule_relation/attribute' === interceptedRequest.url() &&
      'GET' === interceptedRequest.method()
    ) {
      const ruleRelations = [
        {
          attribute: 'packshot',
          rule: 'copy_scanner_xera_packshot_enUS',
        },
        {
          attribute: 'notices',
          rule: 'update_scanner_xera_notices_enUS',
        },
      ];

      interceptedRequest.respond({
        contentType: 'application/json',
        body: JSON.stringify(ruleRelations),
      });
    }
  });

  await page.reload();
}, timeout);

it('It fetches the rule relations', async () => {
  // It fetches the ruleRelations
  const response = await page.evaluate(async () => {
    // Sometimes this test fails on circle ci. This wait should mitigate that
    await new Promise(resolve => setTimeout(resolve, 500));

    const fetchRuleRelations = require('akeneopimenrichmentassetmanager/assets-collection/infrastructure/fetcher/rule-relation')
      .fetchRuleRelations;

    return await fetchRuleRelations();
  });

  // Check the ruleRelations returned by the fetcher are the expected ones
  expect(response).toEqual([
    {
      attribute: 'packshot',
      rule: 'copy_scanner_xera_packshot_enUS',
    },
    {
      attribute: 'notices',
      rule: 'update_scanner_xera_notices_enUS',
    },
  ]);
});
