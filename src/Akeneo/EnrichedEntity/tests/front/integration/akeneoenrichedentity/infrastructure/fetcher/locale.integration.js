const timeout = 5000;

describe('Akeneoenrichedentity > infrastructure > fetcher > locale', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It loads all activated locales', async () => {
    page.on('request', interceptedRequest => {
      if (
        'http://pim.com/configuration/locale/rest?activated=true' === interceptedRequest.url() &&
        'GET' === interceptedRequest.method()
      ) {
        interceptedRequest.respond({
          contentType: 'application/json',
          body: JSON.stringify([
            {code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
            {code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
            {code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
          ]),
        });
      }
    });

    const response = await page.evaluate(async () => {
      const fetcher = require('akeneoenrichedentity/infrastructure/fetcher/locale').default;

      return await fetcher.fetchActivated();
    });

    expect(response).toEqual([
      {code: 'de_DE', label: 'German (Germany)', region: 'Germany', language: 'German'},
      {code: 'en_US', label: 'English (United States)', region: 'United States', language: 'English'},
      {code: 'fr_FR', label: 'French (France)', region: 'France', language: 'French'},
    ]);
  });
});
