const { loadProductGrid } = require('../../common/product-grid');
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

    loadProductGrid(page, products, filters);
  });
});
