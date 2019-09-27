const { loadProductGrid, mockFilteredResponse } = require('../../common/product-grid');
const DatagridProductBuilder = require('../../../common/builder/datagrid-product')
const NumberFilterBuilder = require('../../../common/builder/filters/number')

describe('Pimenrich > product grid > number filter', () => {
  let page = global.__PAGE__;

  it('filters the product grid by the "is empty" operator', async () => {
    const products = [
      new DatagridProductBuilder()
        .withIdentifier('one')
        .withLabel('First')
        .withAttribute('count', 100)
        .build(),
      new DatagridProductBuilder()
        .withIdentifier('two')
        .withLabel('Second')
        .withAttribute('count', 200)
        .build()
    ]

    const filters = [
      new NumberFilterBuilder()
        .withEnabled(true)
        .withLabel('Count')
        .withName('count')
        .withGroup('Marketing')
        .build()
    ];

    await mockFilteredResponse(page, {
      'http://pim.com/datagrid/product-grid?dataLocale=en_US&product-grid%5B_pager%5D%5B_page%5D=1&product-grid%5B_pager%5D%5B_per_page%5D=25&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject%20Object%5D&product-grid%5B_parameters%5D%5Bview%5D%5Bid%5D=&product-grid%5B_sort_by%5D%5Bupdated%5D=DESC&product-grid%5B_filter%5D%5Bscope%5D%5Bvalue%5D=ecommerce&product-grid%5B_filter%5D%5Bcount%5D%5Bvalue%5D=&product-grid%5B_filter%5D%5Bcount%5D%5Btype%5D=empty&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BtreeId%5D=1&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BcategoryId%5D=-2&product-grid%5B_filter%5D%5Bcategory%5D%5Btype%5D=1': [products[0]]
    })

    await loadProductGrid(page, products, filters);

    await expect('count').filterToBeVisible(page);
    await expect('count').toBeFilterableByOperator('is empty', page);
    // await expect(['two']).toBeDisplayedOnTheProductGrid(page);

  }, 30000);
});
