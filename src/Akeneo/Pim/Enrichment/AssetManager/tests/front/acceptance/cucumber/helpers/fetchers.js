const {
  tools: {getRequestContract, listenRequest},
} = require('../test-helpers.js');

const answerChannelList = async page => {
  const requestContract = getRequestContract('Channel/List/filtered_locale.json');

  await listenRequest(page, requestContract);
};
const answerRuleRelationList = async page => {
  const requestContract = getRequestContract('Rule/ok.json');

  await listenRequest(page, requestContract);
};
const answerAttributeList = async page => {
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
const answerAssetList = async page => {
  const requestContract = getRequestContract('Asset/Search/multiple_code_filtered.json');

  await listenRequest(page, requestContract);
};

module.exports = {
  answerChannelList,
  answerRuleRelationList,
  answerAttributeList,
  answerPermissionList,
  answerAssetFamilyDetails,
  answerAssetList,
};
