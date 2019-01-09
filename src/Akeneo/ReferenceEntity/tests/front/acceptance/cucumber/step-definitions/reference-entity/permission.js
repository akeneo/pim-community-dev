const Edit = require('../../decorators/reference-entity/edit.decorator');
const Header = require('../../decorators/reference-entity/app/header.decorator');
const path = require('path');
const {askForReferenceEntity, getRequestContract, listenRequest} = require('../../tools');

const {
  decorators: {createElementDecorator},
  tools: {convertItemTable},
} = require(path.resolve(process.cwd(), './tests/front/acceptance/cucumber/test-helpers.js'));

module.exports = async function(cucumber) {
  const {When, Then} = cucumber;
  const assert = require('assert');

  const config = {
    Header: {
      selector: '.AknTitleContainer',
      decorator: Header,
    },
    Edit: {
      selector: '.AknDefault-contentWithColumn',
      decorator: Edit,
    },
  };
  const getElement = createElementDecorator(config);

  When('a reference entity', async function() {
    const permissionRequestContract = getRequestContract('ReferenceEntityPermission/show.json');
    await listenRequest(this.page, permissionRequestContract);

    const requestContract = getRequestContract('ReferenceEntity/ReferenceEntityDetails/ok.json');
    await listenRequest(this.page, requestContract);

    await askForReferenceEntity.apply(this, ['designer']);
  });

  When('the user sets the following permissions for the reference entity:', async function(permissions) {
    const showRequestContract = getRequestContract('ReferenceEntityPermission/show.json');
    await listenRequest(this.page, showRequestContract);
    const editView = await await getElement(this.page, 'Edit');
    const permissionView = await editView.getPermission();

    for (const permission of convertItemTable(permissions)) {
      await permissionView.setPermission(permission.user_group_identifier, permission.right_level);
    }
    const editRequestContract = getRequestContract('ReferenceEntityPermission/edit.json');

    await listenRequest(this.page, editRequestContract);
  });

  Then(
    'there should be a {string} permission right for the user group {string} on the reference entity',
    async function(rightLevel, groupName) {
      const editView = await await getElement(this.page, 'Edit');
      const permissionView = await editView.getPermission();
      const actualRightLevel = await permissionView.getRightLevel(groupName);

      assert.strictEqual(actualRightLevel, rightLevel);
    }
  );
};
