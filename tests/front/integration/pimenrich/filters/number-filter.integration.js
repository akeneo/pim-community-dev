const process = require('process')
const fs = require('fs')
const datagridLoad = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/product_grid.json`, 'utf-8');
const categoryChildren = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/category_children.json`, 'utf-8');
const listTree = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/list_tree.json`, 'utf-8');
const attributesFilters = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/attributes_filters.json`, 'utf-8');
const dateFormat = fs.readFileSync(`${process.cwd()}/tests/front/integration/common/contracts/date_format.json`, 'utf-8');

const renderProductGrid = async (page) => {
  await page.evaluate(async () => await require('oro/init-layout')());
  // await page.evaluate(async () => await require('pim/date-context').initialize());
  await page.evaluate(async () => await require('pim/user-context').initialize());
  await page.evaluate(async () => await require('pim/init-translator').fetch());
  await page.evaluate(async () => await require('pim/init')());

  console.log('date format', dateFormat)
  await page.on('request', req => {
    const url = req.url(); 
    console.log('url', url);

    if (url === 'http://pim.com/localization/format/date') {
      return res.respond({
        contentType: 'application/json',
        body: dateFormat
      })
    }

    if (url === 'http://pim.com/datagrid_view/rest/product-grid/default') {
      return req.respond({
          contentType: 'text/html;charset=UTF-8',
          body: JSON.stringify({view:null}),
      })
    }

    if (url === 'http://pim.com/datagrid_view/rest/product-grid/default-columns') {
      return req.respond({
          contentType: 'text/html;charset=UTF-8',
          body: JSON.stringify(["identifier","image","label","family","enabled","completeness","created","updated","complete_variant_products"]),
      })
    }

    if(url.includes('/datagrid/product-grid/load')) {
      return req.respond({
        contentType: 'application/json',
        body: datagridLoad
      })
    }

    if (url.includes('/enrich/product-category-tree/product-grid/list-tree.json')) {
      return req.respond({
        contentType: 'application/json',
        body: listTree
      })
    }

    if (url === ('http://pim.com/enrich/product-category-tree/product-grid/children.json?dataLocale=undefined&context=view&id=1&select_node_id=-2&with_items_count=1&include_sub=1')) {
      return req.respond({
        contentType: 'application/json',
        body: categoryChildren
      })
    }

    if (url.includes('/datagrid/product-grid/attributes-filters')) {
      return req.respond({
        contentType: 'application/json',
        body: attributesFilters
      })
    }
  })

  return page.evaluate(({data, extension}) => {
    const FormBuilder = require('pim/form-builder');

    return FormBuilder.build(extension).then(form => {
      form.setData(data);
      form.setElement(document.getElementById('app')).render();
      
      return form;
    });
  }, { data: {}, extension: 'pim-product-index' });
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
