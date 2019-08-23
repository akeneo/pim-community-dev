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
    const requests = []
    page.on('request', (req) => requests.push(req.url()))

    await page.waitForSelector('tr.AknGrid-bodyRow:nth-child(3)', {visible: true});
    await page.click('button.AknFilterBox-addFilterButton')
    await page.waitFor(500)
    await page.click('.filters-column label[for="weight"]')
    await page.click('.AknButton.AknButton--apply.close')
    await page.click('.filter-box [data-name="weight"]')
    await page.click('.open-filter .AknDropdown.operator')
    await page.click('.open-filter .operator_choice[data-value="empty"]')
    await page.click('.open-filter .filter-update')

    const datagridWithFilterRequestWasCalled = requests.includes('http://localhost:4000/datagrid/product-grid?dataLocale=en_US&product-grid%5B_pager%5D%5B_page%5D=1&product-grid%5B_pager%5D%5B_per_page%5D=25&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject%20Object%5D&product-grid%5B_parameters%5D%5Bview%5D%5Bid%5D=&product-grid%5B_sort_by%5D%5Bupdated%5D=DESC&product-grid%5B_filter%5D%5Bweight%5D%5Bvalue%5D=&product-grid%5B_filter%5D%5Bweight%5D%5Btype%5D=empty&product-grid%5B_filter%5D%5Bweight%5D%5Bunit%5D=MILLIGRAM&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BtreeId%5D=0&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BcategoryId%5D=-2&product-grid%5B_filter%5D%5Bcategory%5D%5Btype%5D=1');

    expect(datagridWithFilterRequestWasCalled).toBeTruthy();

  }, 100000);
});
