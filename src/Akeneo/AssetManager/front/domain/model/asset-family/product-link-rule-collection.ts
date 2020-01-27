type ProductLinkRuleCollection = string;

export default ProductLinkRuleCollection;

export const denormalizeAssetFamilyProductLinkRules = (productLinkRules: any): ProductLinkRuleCollection => {
  return JSON.stringify(productLinkRules, null, 4);
};
