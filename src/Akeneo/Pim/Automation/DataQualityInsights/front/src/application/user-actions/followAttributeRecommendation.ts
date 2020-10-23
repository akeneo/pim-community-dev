import {Product} from '../../domain';
import {isRootProductModel, isSimpleProduct, isVariantProduct} from '../helper';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE,
} from '../listener';
import {
  ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY,
  MAX_VARIATION_LEVELS,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
  ROOT_PRODUCT_MODEL_LEVEL,
  SUB_PRODUCT_MODEL_LEVEL,
} from '../constant';

const Router = require('pim/router');

const followAttributeRecommendation = (attributeCode: string, product: Product) => {
  if (isSimpleProduct(product) || product.meta.attributes_for_this_level.includes(attributeCode)) {
    window.dispatchEvent(
      new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
        detail: {
          code: attributeCode,
        },
      })
    );
  } else {
    sessionStorage.setItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY, attributeCode);
    sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);

    let modelId: number = product.meta.variant_navigation[ROOT_PRODUCT_MODEL_LEVEL].selected.id;
    if (product.meta.variant_navigation.length === MAX_VARIATION_LEVELS) {
      modelId = product.meta.variant_navigation[SUB_PRODUCT_MODEL_LEVEL].selected.id;
      if (
        !product.meta.hasOwnProperty('parent_attributes') ||
        !product.meta.parent_attributes.includes(attributeCode)
      ) {
        modelId = product.meta.variant_navigation[ROOT_PRODUCT_MODEL_LEVEL].selected.id;
      }
    }

    window.location.href = '#' + Router.generate('pim_enrich_product_model_edit', {id: modelId});
  }
};

const followAttributesListRecommendations = (product: Product, attributes: string[], axis: string) => {
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

export {followAttributeRecommendation, followAttributesListRecommendations};
