const Edit = require('../../decorators/reference-entity/edit.decorator');
const path = require('path');

const {
  decorators: {createElementDecorator},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {Then} = cucumber;
  const assert = require('assert');

  const config = {
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };

  const getElement = createElementDecorator(config);

  Then('the user wants to see the confirmation dialog before leaving the page and accept', function() {
    this.page.on('dialog', async dialog => {
      assert.strictEqual(dialog.type(), 'beforeunload');
      await dialog.accept();
    });
  });

  Then('the user wants to see the confirmation dialog before leaving the page and dismiss', function() {
    this.page.on('dialog', async dialog => {
      assert.strictEqual(dialog.type(), 'beforeunload');
      await dialog.dismiss();
    });
  });

  Then('the user should be on the edit page', async function() {
    const editPage = await getElement(this.page, 'Edit');
    const isLoaded = await editPage.isLoaded();

    assert.strictEqual(isLoaded, true);
  });

  Then('the user goes to {string}', function(url) {
    this.page.goto(url);
  });

  Then('the user reload the page', async function() {
    this.page.reload();
  });

  Then('the user should be notified that modification have been made', async function() {
    const editPage = await getElement(this.page, 'Edit');
    const isUpdated = await editPage.isUpdated();

    assert.strictEqual(isUpdated, true);
  });

  Then("the user shouldn't be notified that modification have been made", async function() {
    const editPage = await getElement(this.page, 'Edit');
    const isSaved = await editPage.isSaved();

    assert.strictEqual(isSaved, true);
  });
};
