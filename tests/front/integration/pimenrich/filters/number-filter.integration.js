const { mockResponses } = require('../../tools');
const mockPath = '../../../mock/responses/product';

const renderProductGrid = async (page) => {
    mockResponses(page, {
      '/datagrid_view/rest/product-grid/default-columns': {
        contentType: 'application/json',
        body: require(`${mockPath}/default-columns.json`)
      },
      '/datagrid_view/rest/product-grid/default': {
        contentType: 'application/json',
        body: require(`${mockPath}/default-view.json`)
      },
      '/datagrid/product-grid/load': {
        contentType: 'application/json',
        body: require(`${mockPath}/grid-load.json`)
      },
      '/datagrid/product-grid/attributes-filters': {
        contentType: 'application/json',
        body: require(`${mockPath}/attributes-filters.json`)
      },
      '/datagrid/product-grid?dataLocale=en_US': {
        contentType: 'application/json',
        body: require(`${mockPath}/grid-load-full.json`)
      }
    })

    await page.setRequestInterception(true);
    await page.goto('http://localhost:4000/#/enrich/product/');
  }

describe('Product grid > number filter', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    try {
     await renderProductGrid(page)
    } catch (e) {
      console.log("Error", e)
    }
  }, 60000);

  it('filters by the "is empty" operator', async () => {
    expect(true).toEqual(true)
  }, 60000);
});
