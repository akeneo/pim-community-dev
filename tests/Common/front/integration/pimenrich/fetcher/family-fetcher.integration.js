const AttributeBuilder = require('../../../common/builder/attribute');
const attribute = (new AttributeBuilder()).build();

describe('Pimenrich > fetcher > family', () => {
  let page = global.__PAGE__;


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

  it('provide an non empty list of axis', async () => {
    page.once('request', request => {
      if (request.url().includes('/configuration/family/rest/camcorder/available_axes')) {
        request.respond({
          contentType: 'application/json',
          body: JSON.stringify([attribute]),
        });
      }
    });

    const axis = await page.evaluate(async () => {
      const fetcherRegistry = require('pim/fetcher-registry');

      return await fetcherRegistry
        .getFetcher('family')
        .fetchAvailableAxes('camcorder');
    });

    expect(axis).toEqual([attribute]);
  });
});
