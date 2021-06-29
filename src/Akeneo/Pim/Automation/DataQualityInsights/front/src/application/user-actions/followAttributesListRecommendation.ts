import {Product} from '../../domain';
import {isRootProductModel, isSimpleProduct, isVariantProduct} from '../helper';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
} from '../listener';
import {
  ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  ROOT_PRODUCT_MODEL_LEVEL,
  SUB_PRODUCT_MODEL_LEVEL,
} from '../constant';

const Router = require('pim/router');

type FollowAttributesListRecommendationHandler = (product: Product, attributes: string[], axis: string) => void;

const followAttributesListRecommendation: FollowAttributesListRecommendationHandler = (
  product: Product,
  attributes: string[],
  axis: string
) => {
  const attributeToImprove = attributes[0];

  // @ts-ignore
  if (isSimpleProduct(product) || isVariantProduct(product) || isRootProductModel(product)) {
    switch (axis) {
      case 'enrichment':
        window.dispatchEvent(
          new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES, {
            detail: {
              attributes: attributes,
            },
          })
        );
        break;
      case 'consistency':
        window.dispatchEvent(
          new CustomEvent(DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES, {
            detail: {
              attributes: attributes,
            },
          })
        );
        break;
    }
  } else {
    sessionStorage.setItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY, attributeToImprove);
    sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);

    let modelId = product.meta.variant_navigation[SUB_PRODUCT_MODEL_LEVEL].selected.id;

    if (
      !product.meta.hasOwnProperty('parent_attributes') ||
      !product.meta.parent_attributes.includes(attributeToImprove)
    ) {
      modelId = product.meta.variant_navigation[ROOT_PRODUCT_MODEL_LEVEL].selected.id;
    }
    window.location.href = '#' + Router.generate('pim_enrich_product_model_edit', {id: modelId});
  }
};

export {followAttributesListRecommendation};
export type {FollowAttributesListRecommendationHandler};
