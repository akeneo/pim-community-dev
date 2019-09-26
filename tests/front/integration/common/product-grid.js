const responses = {
  'http://pim.com/js/extensions.json': require(`${process.cwd()}/public/js/extensions.json`),
  'http://pim.com/rest/security/': require('./responses/rest-security.json')
}

const matchResponses = (page) => {
  page.on('request', (interceptedRequest) => {
    // console.log(interceptedRequest.url());
    const body = responses[interceptedRequest.url()];

    if (!body) {
      return interceptedRequest.continue();
    }

    return interceptedRequest.respond({
      status: 200,
      contentType: 'application/json',
      body: typeof body === 'string' ? body : JSON.stringify(body),
    })
  })
}

const loadProductGrid = async (page) => {
  matchResponses(page);

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