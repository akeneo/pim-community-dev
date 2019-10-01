const mockResponses = {
  '/datagrid_view/rest/product-grid/default-columns': require('./responses/default-columns.json'),
  '/datagrid_view/rest/product-grid/default': require('./responses/default-views.json'),
  '/enrich/product-category-tree/product-grid/list-tree.json': require('./responses/list-tree.json'),
  '/enrich/product-category-tree/product-grid/children.json': require('./responses/category-tree.json'),
}

const mockFilteredProducts = (page, filter) => {
  return page.on('request', (req) => {
    const filterParam = encodeURI(`product-grid[_filter][${filter.name}][type]=${filter.type}`)
    if (req.url().includes(filterParam)) {
      const { productGridData } = constructProductsResponse(filter.response)

      return req.respond({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify(productGridData),
      })
    }
  })
}

const constructProductsResponse = (products) => {
  const productGridData = {
    data: products,
    totalRecords: products.length,
    options: {
      totalRecords: products.length
    }
  }

  const productLoadData = Object.assign(require('./responses/datagrid-load.json'), {
    data: JSON.stringify(productGridData)
  })

  return { productGridData, productLoadData };
}

const matchResponseURL = (responses, url) => {
  for ([responseURL, body] of Object.entries(responses)) {
    if (url.includes(responseURL)) {
      return body;
    }
  }
}

const buildProductGridResponses = (page, products = [], filters = []) => {
  const { productGridData, productLoadData } = constructProductsResponse(products)

  const productGridResponses = Object.assign(mockResponses, {
    '/datagrid/product-grid?dataLocale=en_US&product-grid%5B_pager%5D%5B_page%5D=1&product-grid%5B_pager%5D%5B_per_page%5D=25&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject%20Object%5D&product-grid%5B_parameters%5D%5Bview%5D%5Bid%5D=&product-grid%5B_sort_by%5D%5Bupdated%5D=DESC&product-grid%5B_filter%5D%5Bscope%5D%5Bvalue%5D=ecommerce&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BtreeId%5D=1&product-grid%5B_filter%5D%5Bcategory%5D%5Bvalue%5D%5BcategoryId%5D=-2&product-grid%5B_filter%5D%5Bcategory%5D%5Btype%5D=1': productGridData,
    '/datagrid/product-grid/load?dataLocale=en_US&params%5BdataLocale%5D=en_US&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject+Object%5D': productLoadData,
    '/datagrid/product-grid/attributes-filters': filters
  });

  return page.on('request', (interceptedRequest) => {
    const body = matchResponseURL(productGridResponses, interceptedRequest.url());

    if (body) {
      return interceptedRequest.respond({
        status: 200,
        contentType: 'application/json',
        body: typeof body === 'string' ? body : JSON.stringify(body),
      })
    }
  })
}

const loadProductGrid = async (page, products, filters) => {
  await buildProductGridResponses(page, products, filters);

  await page.evaluate(() => {
    return require('pim/form-builder').build('pim-product-index').then(form => {
      form.setElement(document.getElementById('app')).render();
      return form;
    });
  });

  return page.waitForSelector('.AknLoadingMask.loading-mask', { hidden: true });
}

const getOperatorChoiceByLabel = async (filter, choiceLabel) => {
  const operatorChoices = await filter.$$('.operator_choice');
  let matchingChoice = null;

  for(let i = 0; i < operatorChoices.length; i++) {
    const text = await (await operatorChoices[i].getProperty('textContent')).jsonValue();
    if (text.trim() === choiceLabel) {
      matchingChoice = operatorChoices[i];
      break;
    }
  }

  return matchingChoice;
}

const getProductRowLabels = async (page) => {
  const rows = await page.$$('.grid .AknGrid-bodyRow.row-click-action');
  const rowLabels = [];

  for (row of rows) {
    const labelColumn = await row.$('[data-column="label"]');
    const rowLabel = await (await labelColumn.getProperty('textContent')).jsonValue();
    if (rowLabel) rowLabels.push(rowLabel);
  }

  return rowLabels;
}

// Custom Jest expect matchers for the product grid
expect.extend({
  filterToBeVisible: async (filterName, page) => {
    return {
      pass: (await page.$(`.filter-box .filter-item[data-name="${filterName}"]`)) !== null,
      message: () => `Filter "${filterName}" should be visible`
    }
  },
  toBeFilterableByOperator: async (filterName, operator, page) => {
    try {
      const filterSelector = `.filter-item[data-name="${filterName}"]`
      const filter = await page.$(filterSelector);
      await (await filter.$('.AknFilterBox-filter')).click();

      await page.waitForSelector(`${filterSelector} .filter-criteria`, {
        visible: true,
        timeout: 500
      })

      await (await filter.$('.operator')).click();
      await (await getOperatorChoiceByLabel(filter, operator)).click();
      await page.waitFor(500);
      await (await filter.$('.filter-update')).click();
      await page.waitFor(500);

      return {
        pass: true,
        message: () => `Can't filter "${filterName}" by "${operator}"`
      }
    } catch (e) {
      console.log(e.message)
      return {
        pass: false,
        message: () => `Couldn't open filter "${filterName}"`
      }
    }
  },
  toBeDisplayedOnTheProductGrid: async function(products, page) {
    await page.waitForSelector('.AknLoadingMask.loading-mask', {hidden: true});
    const rowLabels = (await getProductRowLabels(page)).map(label => label.toLowerCase());

    return {
      pass: this.equals(rowLabels, products.map(product => product.toLowerCase())),
      message: () => `Expected to see ${products.join(', ')} on the grid`
    }
  }
})

module.exports = {
  loadProductGrid,
  mockFilteredProducts
}
