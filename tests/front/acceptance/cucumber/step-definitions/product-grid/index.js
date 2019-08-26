module.exports = function(cucumber) {
  const { Given, Then } = cucumber;
  const assert = require('assert');
  const  { answerJson, renderView, convertItemTable } = require('../../tools');
  const DatagridProductBuilder = require('../../../../common/builder/datagrid-product')
  const createElementDecorator = require('../../decorators/common/create-element-decorator');
  const datagridLoad = require('../../contracts/datagrid-load.json')
  const categoryListTree = require('../../contracts/category-list-tree.json')
  const categoryTreeChildren = require('../../contracts/category-tree-children.json')

  const config = {
    'Product grid':  {
      selector: '.AknGrid.AknGrid--withCheckbox',
      decorator: require('../../decorators/product-grid/grid.decorator')
    }
  };

  Given('the following attributes:', function (dataTable, callback) {
    this.page.on('request', request => {
      if (request.url().includes('attributes-filters')) {
        answerJson(request, []);
      }
    });

    callback();
  });

  Given('the following products:', function (products, callback) {
    const followingProducts = convertItemTable(products)
    const productData = followingProducts.map((product) => {
      return new DatagridProductBuilder()
        .withIdentifier(product.sku)
        .withLabel(product.sku)
        .withAttribute('count', product.count)
        .withAttribute('rate', product.rate)
        .build()
    })

    const productGridData = {
      data: productData,
      totalRecords: 1049,
      options: {
        totalRecords: null
      }
    }

    const productLoadData = Object.assign(datagridLoad, {
      data: JSON.stringify(productGridData)
    })

    this.page.on('request', request => {
      if (request.url().includes('/datagrid/product-grid/load?dataLocale=en_US')) {
        return answerJson(request, productLoadData)
      }

      if (request.url().includes('/datagrid/product-grid')) {
        return answerJson(request, productGridData)
      }
    })

    callback();
  });

  Given('I am on the products grid', async function () {
    this.page.on('request', request => {
      if (request.url() === 'http://pim.com/datagrid_view/rest/product-grid/default') {
        return answerJson(request, { view: null })
      }

      if (request.url().includes('datagrid_view/rest/product-grid/default-columns')) {
        return answerJson(request, ["identifier","image","label","count", "rate"])
      }

      if (request.url().includes('/enrich/product-category-tree/product-grid/list-tree')) {
        return answerJson(request, categoryListTree)
      }

      if (request.url().includes('/enrich/product-category-tree/product-grid/children')) {
        return answerJson(request, categoryTreeChildren)
      }
    })

    await renderView(this.page, 'pim-product-index', {});
  });

  Then('the grid should contain {int} elements', async function (x) {
    const productGrid = await createElementDecorator(config)(this.page, 'Product grid')
    const productCount = await productGrid.getRowCount();
    assert.equal(productCount, x)
  });

  Then('I should see products postit, book and mug', async function () {
    const productGrid = await createElementDecorator(config)(this.page, 'Product grid')
    const rowNames = await productGrid.getRowNames();
    assert.notStrictEqual(rowNames, ['postit', 'book', 'mug'])
  });

  Then('I should be able to use the following filters:', function (dataTable, callback) {
    callback(null, 'pending');
  });
};


