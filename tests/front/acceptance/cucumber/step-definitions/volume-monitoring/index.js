module.exports = async function(cucumber) {
  const { Given, Then, When, Before } = cucumber;
  const assert = require('assert');
  const { renderView } = require('../../tools');
  const createElementDecorator = require('../../decorators/common/create-element-decorator');

  const config = {
    'Catalog volume report':  {
      selector: '.AknDefault-mainContent',
      decorator: require('../../decorators/catalog-volume/report.decorator')
    }
  };

  let data = {
    average_max_attributes_per_family: {
      value: { average: 7, max: 10 },
      has_warning: true,
      type: 'average_max'
    }
  };

  getElement = createElementDecorator(config);

  Given('a family with {int} attributes', async function(int) {
    this.page.on('request', request => {
      if (request.url().includes('/security')) {
        request.respond({
          contentType: 'application/json',
          body: `${JSON.stringify({})}`
        })
      }
    })

    await renderView(this.page, 'pim-catalog-volume-index', data);
    assert(int)
  });

  Given('the limit of the number of attributes per family is set to {int}', int => assert(int));

  When('the administrator user asks for the catalog volume monitoring report', async function () {
    const report = await getElement(this.page, 'Catalog volume report');
    const header = await report.getHeader();
    const title = await header.getTitle();

    assert.equal(title, 'Catalog volume monitoring');
  });

  Then('the report returns that the average number of attributes per family is {int}', async function (int) {
    const report = await (await getElement(this.page, 'Catalog volume report'));
    const volume = await report.getVolumeByType('average_max_attributes_per_family');
    const value = await volume.getValue();
    assert.equal(value.mean, int);
  });

  Then('the report returns that the maximum number of attributes per family is {int}', async function (int) {
    const report = await (await getElement(this.page, 'Catalog volume report'));
    const volume = await report.getVolumeByType('average_max_attributes_per_family');
    const value = await volume.getValue();
    assert.equal(value.max, int);
  });

  Then('the report warns the users that the number of attributes per family is high', async function () {
    const report = await (await getElement(this.page, 'Catalog volume report'));
    const volume = await report.getVolumeByType('average_max_attributes_per_family');
    const warning = await volume.getWarning();

    assert.equal(
      warning,
      'Wow! You hit a record with this axis! Don\'t hesitate to contact us if you need any help with this kind of volume.'
    );
  });
};
