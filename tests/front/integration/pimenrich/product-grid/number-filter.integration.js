const loadDatagrid = async (page) => {
  return await page.evaluate(() => {
    const FormBuilder = require('pim/form-builder');

    return FormBuilder.build('pim-product-index').then(form => {
      form.setElement(document.getElementById('app')).render();

      return form;
    });
  });
}


describe('Pimenrich > product grid > number filter', () => {
  let page = global.__PAGE__;

  it('filters the product grid by the "is empty" operator', async () => {
    // Load datagrid - products, filters, answers
    loadDatagrid(page);
  });
});
