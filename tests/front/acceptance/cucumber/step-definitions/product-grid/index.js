module.exports = function(cucumber) {
  const { Given, Then } = cucumber;
  const assert = require('assert');
  const { answerJson, renderView, convertItemTable, csvToArray, getQueryParamsFromRequest } = require('../../tools');
  const DatagridProductBuilder = require('../../../../common/builder/datagrid-product')
  const NumberFilterBuilder = require('../../../../common/builder/filter/number')
  const createElementDecorator = require('../../decorators/common/create-element-decorator');
  const datagridLoad = require('../../contracts/datagrid-load.json')
  const categoryListTree = require('../../contracts/category-list-tree.json')
  const categoryTreeChildren = require('../../contracts/category-tree-children.json')

  const config = {
    'Product grid':  {
      selector: '.AknGrid.AknGrid--withCheckbox',
      decorator: require('../../decorators/product-grid/grid.decorator')
    },
    'Product grid filter list': {
      selector: '.filter-box',
      decorator: require('../../decorators/product-grid/filter-list.decorator')
    },
    'Product grid filter search': {
      selector: '.AknFilterBox-column',
      decorator: require('../../decorators/product-grid/filter-search.decorator')
    }
  };

  Given('the following attributes:', function (attributes, callback) {
    const followingAttributes = convertItemTable(attributes);
    const datagridFilters = followingAttributes.map((attribute) => {
      return new NumberFilterBuilder()
        .withEnabled(true)
        .withLabel(attribute['label-en_US'])
        .withName(attribute['label-en_US'].split(' ').join('_'))
        .withGroup(attribute.group)
        .build()
    });

    this.page.on('request', request => {
      if (request.url().includes('attributes-filters')) {
        answerJson(request, datagridFilters);
      }
    });

    callback();
  });

  Given('the following products:', function (products, callback) {
    const followingProducts = convertItemTable(products)
    const datagridProducts = followingProducts.map((product) => {
      return new DatagridProductBuilder()
        .withIdentifier(product.sku)
        .withLabel(product.sku)
        .withAttribute('count', product.count)
        .withAttribute('rate', product.rate)
        .build()
    })

    const productGridData = {
      data: datagridProducts,
      totalRecords: datagridProducts.length,
      options: {
        totalRecords: datagridProducts.length
      }
    }

    const productLoadData = Object.assign(datagridLoad, {
      data: JSON.stringify(productGridData)
    })

    this.page.on('request', request => {
      if (request.url().includes('/datagrid/product-grid/load?dataLocale=en_US')) {
        return answerJson(request, productLoadData)
      }

      if (request.url().includes('/datagrid/product-grid?dataLocale=en_US')) {
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

      if (request.url().includes('/datagrid_view/rest/product-grid/default-columns')) {
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

  Then('I should see products {string}', async function (csvString) {
    const products = csvToArray(csvString);
    const productGrid = await createElementDecorator(config)(this.page, 'Product grid')

    await this.page.waitForSelector('.AknLoadingMask.loading-mask', {hidden: true});

    const rowNames = await productGrid.getRowNames();
    assert.deepEqual(rowNames, products)
  });

  Then('I should be able to use the following filters:', { timeout: 60000 }, async function (itemTable) {
    const filterList = await createElementDecorator(config)(this.page, 'Product grid filter list')
    const filters = convertItemTable(itemTable);
    const filterSearch = await createElementDecorator(config)(this.page, 'Product grid filter search')

    // console.log(filterSearch)
    // this.page.on('request', request => {
    //   const params = getQueryParamsFromRequest(request);
    //   console.log(params)
    //   request.continue();
    // })

    for (let i = 0; i < filters.length; i++) {
      const uniqueFilters = Array.from(new Set(filters.map(filter => filter.filter)))
      await filterSearch.disableFilters(uniqueFilters);
      await filterSearch.enableFilter(filters[i].filter);
      console.log('enabled filter', filters[i].filter)
      await filterList.setFilterValue(
        filters[i].filter,
        filters[i].operator,
        filters[i].value
      )

      await this.page.waitForSelector('.AknLoadingMask.loading-mask', {hidden: true});

      const productGrid = await createElementDecorator(config)(this.page, 'Product grid')
      const rowNames = await productGrid.getRowNames();

      // console.log(filters[i].result)

      // assert.deepEqual(rowNames, [filters[i].result]);
    }

  });
};


