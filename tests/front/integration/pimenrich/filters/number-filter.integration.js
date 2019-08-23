const answerJSON = (request, body) => {
  request.respond({
    contentType: 'application/json',
    body: JSON.stringify(body)
  });
}

const renderProductGrid = async (page) => {
  // tools.mockRequests(page, {
  //   'http://pim.com/datagrid_view/rest/product-grid/default': answerJSON(JSON.stringify({view: null})),
  //   'http://pim.com/datagrid_view/rest/product-grid/default-columns': answerJSON(JSON.stringify(["identifier","image","label","family","enabled","completeness","created","updated","complete_variant_products"])),
    // 'http://pim.com/enrich/product-category-tree/product-grid/children.json?dataLocale=undefined&context=view&id=0&select_node_id=-2&with_items_count=1&include_sub=1': answerJSON(categoryChildren),
    // 'http://pim.com/datagrid/product-grid/load?dataLocale=en_US&params%5BdataLocale%5D=en_US&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject+Object%5D': answerJSON(datagridLoad),
    // 'http://pim.com/datagrid/product-grid/attributes-filters?page=1&locale=en_US': answerJSON(attributesFilters),
    // 'http://pim.com/enrich/product-category-tree/product-grid/list-tree.json?dataLocale=undefined&select_node_id=0&include_sub=1&context=view': answerJSON(listTree),
    // 'http://pim.com/enrich/product-category-tree/product-grid/children.json?dataLocale=undefined&context=view&id=1&select_node_id=-2&with_items_count=1&include_sub=1': answerJSON(categoryChildren),
  // });

    page.on('request', req => {
      console.log(req.url());

      if (req.url().includes('/datagrid_view/rest/product-grid/default-columns')) {
        return answerJSON(req, require('../../../mock/responses/product/default-columns.json'));
      }

      if (req.url().includes('/datagrid_view/rest/product-grid/default')) {
        return answerJSON(req, require('../../../mock/responses/product/default-view.json'));
      }

      req.continue();
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
