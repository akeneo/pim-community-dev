const UserBuilder = require('../../common/builder/user');
const adminUser = new UserBuilder().withUsername('admin').build();
const datagridLoad = require('./responses/datagrid-load.json');
const { readFileSync } = require('fs');

const mockResponses = {
  'http://pim.com/js/extensions.json': ['application/json', require(`${process.cwd()}/public/js/extensions.json`)],
  'http://pim.com/rest/security/': ['application/json', require('./responses/rest-security.json')],
  'http://pim.com/configuration/locale/rest?activated=true': ['application/json', require('./responses/activated-locales.json')],
  'http://pim.com/localization/format/date': ['application/json', require('./responses/date-format.json')],
  'http://pim.com/datagrid_view/rest/product-grid/default-columns': ['application/json', require('./responses/default-columns.json')],
  'http://pim.com/datagrid_view/rest/product-grid/default': ['application/json', require('./responses/default-views.json')],
  'http://pim.com/rest/user/': ['application/json', adminUser],
  'http://pim.com/js/translation/en_US.js': ['application/javascript', readFileSync(`${process.cwd()}/public/js/translation/en_US.js`, 'utf-8')],
  'http://pim.com/enrich/product-category-tree/product-grid/list-tree.json?dataLocale=undefined&select_node_id=0&include_sub=1&context=view': [
    'application/json', require('./responses/list-tree.json')
  ],
  'http://pim.com/enrich/product-category-tree/product-grid/children.json?dataLocale=undefined&context=view&id=1&select_node_id=-2&with_items_count=1&include_sub=1': [
    'application/json', require('./responses/category-tree.json')
  ],
}

const matchResponses = (page, products, filters) => {
  const datagridProducts = [];
  const productGridFilters = [];

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

  const productGridResponses = {
    'http://pim.com/datagrid/product-grid/load?dataLocale=en_US&params%5BdataLocale%5D=en_US&product-grid%5B_parameters%5D%5Bview%5D%5Bcolumns%5D=identifier%2Cimage%2Clabel%2Cfamily%2Cenabled%2Ccompleteness%2Ccreated%2Cupdated%2Ccomplete_variant_products%2Csuccess%2C%5Bobject+Object%5D': [
    'application/json', productLoadData
      ],
      'http://pim.com/datagrid/product-grid/attributes-filters?page=1&locale=en_US': productGridFilters
  };

  return page.on('request', (interceptedRequest) => {
    // console.log('url', interceptedRequest.url())
    const response = Object.assign(mockResponses, productGridResponses)[interceptedRequest.url()];

    if (!response) {
      return interceptedRequest.continue();
    }

    const [contentType, body] = response;

    return interceptedRequest.respond({
      status: 200,
      contentType: contentType,
      body: typeof body === 'string' ? body : JSON.stringify(body),
    })
  })
}

const loadProductGrid = async (page, products, filters) => {
  await matchResponses(page, products, filters);

  await page.evaluate(async () => await require('pim/init')());
  await page.evaluate(async () => await require('pim/user-context').initialize());
  await page.evaluate(async () => await require('pim/date-context').initialize());
  await page.evaluate(async () => await require('pim/init-translator').fetch());
  await page.evaluate(async () => await require('oro/init-layout')());

  return await page.evaluate(() => {
    const FormBuilder = require('pim/form-builder');

    return FormBuilder.build('pim-product-index').then(form => {
      form.setElement(document.getElementById('app')).render();

      return form;
    });
  });
}


module.exports = {
  loadProductGrid
}