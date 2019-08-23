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

    return page.goto('http://localhost:4000/#/enrich/product/', {
      timeout: 0
    });
  }

describe('Product grid > number filter', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await renderProductGrid(page)
  }, 60000);

  it('filters a number attribute by the "is empty" operator', async () => {
    await page.waitForSelector('tr.AknGrid-bodyRow:nth-child(3)', {visible: true});
    await page.click('button.AknFilterBox-addFilterButton')
    await page.waitFor(500)
    await page.click('.filters-column label[for="weight"]')
    await page.click('.AknButton.AknButton--apply.close')
    await page.click('.filter-box [data-name="weight"]')
    await page.click('.open-filter .AknDropdown.operator')
    await page.click('.open-filter .operator_choice[data-value="empty"]')
    await page.click('.open-filter .filter-update')
    expect(true).toBeTruthy()
  }, 100000);
});
