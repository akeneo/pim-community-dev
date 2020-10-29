const Edit = require('../../decorators/asset-family/edit.decorator');
const Header = require('../../decorators/asset-family/app/header.decorator');
const path = require('path');
const {askForAssetFamily, getRequestContract, listenRequest} = require('../../tools');

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

  When('an asset family', async function() {
    const permissionRequestContract = getRequestContract('AssetFamilyPermission/show.json');
    await listenRequest(this.page, permissionRequestContract);

    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');
    await listenRequest(this.page, requestContract);

    await askForAssetFamily.apply(this, ['designer']);
  });

  When('the user sets the following permissions for the asset family:', async function(permissions) {
    const editView = await await getElement(this.page, 'Edit');
    const permissionView = await editView.getPermission();

    for (const permission of convertItemTable(permissions)) {
      await permissionView.setPermission(permission.user_group_identifier, permission.right_level);
    }
    const editRequestContract = getRequestContract('AssetFamilyPermission/edit.json');

    await listenRequest(this.page, editRequestContract);
  });

  When('the user ask for an asset family without any user groups', async function() {
    const showRequestContract = getRequestContract('AssetFamilyPermission/show_empty.json');
    await listenRequest(this.page, showRequestContract);
    const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');
    await listenRequest(this.page, requestContract);

    await askForAssetFamily.apply(this, ['designer']);
  });

  Then('there should be a {string} permission right for the user group {string} on the asset family', async function(
    rightLevel,
    groupName
  ) {
    const editView = await await getElement(this.page, 'Edit');
    const permissionView = await editView.getPermission();
    const actualRightLevel = await permissionView.getRightLevel(groupName);

    assert.strictEqual(actualRightLevel, rightLevel);
  });

  Then('the user should be warned that he needs to create user groups first', async function() {
    const editView = await await getElement(this.page, 'Edit');
    const permissionView = await editView.getPermission();
    permissionView.isEmpty();
  });
};
