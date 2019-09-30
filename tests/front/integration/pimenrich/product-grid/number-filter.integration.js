const { loadProductGrid, mockFilteredResponse } = require('./grid-tools');
const DatagridProductBuilder = require('../../../common/builder/datagrid-product')
const NumberFilterBuilder = require('../../../common/builder/filters/number')

describe('Pimenrich > product grid > number filter', () => {
  let page = global.__PAGE__;

  const firstProduct = new DatagridProductBuilder()
    .withIdentifier('one')
    .withLabel('First')
    .withAttribute('count', 100)
    .build();

  const secondProduct = new DatagridProductBuilder()
    .withIdentifier('two')
    .withLabel('Second')
    .withAttribute('count', 200)
    .build();

  const filters = [
    new NumberFilterBuilder()
      .withEnabled(true)
      .withLabel('Count')
      .withName('count')
      .withGroup('Marketing')
      .build()
  ];

  it('filters the product grid by the "is empty" operator', async (done) => {
    const filter = encodeURI('product-grid[_filter][count][type]=empty');
    await mockFilteredResponse(page, { [filter]: [secondProduct] })

    await loadProductGrid(page, [firstProduct, secondProduct], filters);
    await expect('count').filterToBeVisible(page);
    await expect('count').toBeFilterableByOperator('is empty', page);
    await expect(['Second']).toBeDisplayedOnTheProductGrid(page);
    done();
  }, 10000);

  it('filters the product grid by the "is not empty" operator', async (done) => {
    const filter = encodeURI('product-grid[_filter][count][type]=not empty');
    await mockFilteredResponse(page, { [filter]: [firstProduct] })

    await expect('count').toBeFilterableByOperator('is not empty', page);
    await expect(['First']).toBeDisplayedOnTheProductGrid(page);

    done();
  }, 10000)
});

