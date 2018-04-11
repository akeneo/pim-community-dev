const timeout = 5000;

describe('Pimenrich > fetcher > family', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
    await page.evaluate(async () => await require('pim/fetcher-registry').initialize());
  }, timeout);

  it('provide an empty list of axis', async () => {
    page.once('request', request => {
      if (request.url().includes('/configuration/family/rest/camcorder/available_axes')) {
        request.respond({
          contentType: 'application/json',
          body: '[]',
        });
      }
    });

    const axis = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('family')
        .fetchAvailableAxes('camcorder');
    });

    expect(axis).toEqual([]);
  });
});
