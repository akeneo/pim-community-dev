const {
  tools: {getRequestContract, listenRequest},
} = require('../test-helpers.js');

const answerChannelList = async page => {
  const requestContract = getRequestContract('Channel/List/filtered_locale.json');

  await listenRequest(page, requestContract);

  const filteredRequestContract = getRequestContract('Channel/List/ok.json');
  await listenRequest(page, filteredRequestContract);
};
const answerRuleRelationList = async page => {
  const requestContract = getRequestContract('Rule/ok.json');

  await listenRequest(page, requestContract);
};
const answerAttributeGroup = async page => {
  const requestContract = getRequestContract('AttributeGroup/list.json');

  await listenRequest(page, requestContract);
};
const answerProductAttributeList = async page => {
  const requestContract = getRequestContract('ProductAttribute/ok.json');

  await listenRequest(page, requestContract);
};
const answerPermissionList = async page => {
  const requestContract = getRequestContract('Permission/All/ok.json');

  await listenRequest(page, requestContract);
};
const answerAssetFamilyDetails = async page => {
  const requestContract = getRequestContract('AssetFamily/AssetFamilyDetails/ok.json');

  await listenRequest(page, requestContract);
};
const answerAssetAttributes = async page => {
  const requestContract = getRequestContract('Attribute/ListDetails/ok/designer.json');

  await listenRequest(page, requestContract);
};
const answerAssetList = async page => {
  const requestContract1 = getRequestContract('Asset/Search/asset_picker_search.json');
  const requestContract2 = getRequestContract('Asset/Search/product_asset_collection.json');
  const requestContract3 = getRequestContract('Asset/Search/product_asset_collection_updated.json');
  await listenRequest(page, requestContract1);
  await listenRequest(page, requestContract2);
  await listenRequest(page, requestContract3);
};

module.exports = {
  answerChannelList,
  answerRuleRelationList,
  answerAttributeGroup,
  answerProductAttributeList,
  answerPermissionList,
  answerAssetFamilyDetails,
  answerAssetList,
  answerAssetAttributes,
};
