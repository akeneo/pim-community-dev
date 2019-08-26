module.exports = function(cucumber) {
  const { Given, Then } = cucumber;
  const  { answerJson, csvToArray, renderView } = require('../../tools');
  const datagridLoad = require('../../contracts/datagrid-load.json')
  const datagridProducts = require('../../contracts/datagrid-products.json')
  const categoryListTree = require('../../contracts/category-list-tree.json')
  const categoryTreeChildren = require('../../contracts/category-tree-children.json')

  // Given('the "default" catalog configuration', function (callback) {
  //   callback(null, 'pending');
  // });

  // Given('I am logged in as "Mary"', function (callback) {
  //   callback(null, 'pending');
  // });

  Given('the following attributes:', function (dataTable, callback) {
    this.page.on('request', request => {
      if (request.url().includes('attributes-filters')) {
        answerJson(request, []);
      }
    });

    callback();
  });

  Given('the following products:', function (dataTable, callback) {
    callback();
  });

  // Given('the "book" product has the "count" attribute', function (string, string2, callback) {
  //   callback(null, 'pending');
  // });

  // Given('the "mug" product has the "rate" attribute', function (string, string2, callback) {
  //   callback(null, 'pending');
  // });

  Given('I am on the products grid', async function () {
    this.page.on('request', request => {
      if (request.url() === 'http://pim.com/datagrid_view/rest/product-grid/default') {
        return answerJson(request, { view: null })
      }

      if (request.url().includes('datagrid_view/rest/product-grid/default-columns')) {
        return answerJson(request, ["identifier","image","label","family","enabled","completeness","created","updated"])
      }

      if (request.url().includes('/datagrid/product-grid/load?dataLocale=en_US')) {
        return answerJson(request, datagridLoad)
      }

      if (request.url().includes('/datagrid/product-grid')) {
        return answerJson(request, datagridProducts)
      }

      if (request.url().includes('/enrich/product-category-tree/product-grid/list-tree')) {
        return answerJson(request, categoryListTree)
      }

      if (request.url().includes('/enrich/product-category-tree/product-grid/children')) {
        return answerJson(request, categoryTreeChildren)
      }

      // request.continue();
    })

    await renderView(this.page, 'pim-product-index', {});
  });

  Then('the grid should contain 3 elements', function (callback) {
    callback(null, 'pending');
  });

  Then('I should see products postit, book and mug', function (callback) {
    callback(null, 'pending');
  });

  Then('I should be able to use the following filters:', function (dataTable, callback) {
    callback(null, 'pending');
  });
};


