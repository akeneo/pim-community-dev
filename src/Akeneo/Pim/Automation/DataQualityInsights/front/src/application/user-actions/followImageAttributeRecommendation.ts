import {
  followAttributeRecommendation,
  FollowAttributeRecommendationHandler
} from "@akeneo-pim-community/data-quality-insights/src/application/user-actions"
import {Attribute, Family} from "@akeneo-pim-community/data-quality-insights/src/domain"
import {DATA_QUALITY_INSIGHTS_EDIT_PRODUCT_ASSETS} from "../../domain";

const router = require('pim/router');

const ASSET_COLLECTION_TYPE = 'pim_catalog_asset_collection';

const findAttribute = (code: string, family: Family|null): Attribute|undefined => {
  if (!family) {
    return undefined;
  }

  return family.attributes.find((attribute) => attribute.code === code);
}

const followImageAttributeRecommendation: FollowAttributeRecommendationHandler = (attributeCode, product, family) => {
  const attribute = findAttribute(attributeCode, family);

  if (!attribute || attribute.type !== ASSET_COLLECTION_TYPE) {
    followAttributeRecommendation(attributeCode, product, family);
    return
  }

  window.dispatchEvent(
    new CustomEvent(DATA_QUALITY_INSIGHTS_EDIT_PRODUCT_ASSETS, {
      detail: {
        code: attributeCode,
      },
    })
  );
};
export {followImageAttributeRecommendation};
