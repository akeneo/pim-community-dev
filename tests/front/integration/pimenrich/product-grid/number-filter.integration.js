const { loadProductGrid } = require('../../common/product-grid');

describe('Pimenrich > product grid > number filter', () => {
  let page = global.__PAGE__;

  it('filters the product grid by the "is empty" operator', async () => {
    const products = [];
    const filters = [];

    loadProductGrid(page, products, filters);
  });
});
