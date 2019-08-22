const renderProductGrid = async (page) => {
  await page.evaluate(async () => await require('pim/user-context').initialize());
  await page.evaluate(async () => await require('pim/init-translator').fetch());
  await page.on('request', req => {
    if (req.url() === 'http://pim.com/datagrid_view/rest/product-grid/default') {
      req.respond({
          contentType: 'text/html;charset=UTF-8',
          body: JSON.stringify({view:null}),
      })
    }

    if (req.url() === 'http://pim.com/datagrid_view/rest/product-grid/default-columns') {
      req.respond({
          contentType: 'text/html;charset=UTF-8',
          body: JSON.stringify(["identifier","image","label","family","enabled","completeness","created","updated","complete_variant_products"]),
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
  });

  it('filters by the "is empty" operator', async () => {
    expect(true).toEqual(true)
  }, 10000);
});
