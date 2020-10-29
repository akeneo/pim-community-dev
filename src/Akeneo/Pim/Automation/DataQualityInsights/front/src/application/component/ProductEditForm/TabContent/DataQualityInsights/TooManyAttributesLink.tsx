import React, {FunctionComponent} from 'react';
import {
  DATA_QUALITY_INSIGHTS_FILTER_ALL_IMPROVABLE_ATTRIBUTES,
  DATA_QUALITY_INSIGHTS_FILTER_ALL_MISSING_ATTRIBUTES,
} from '../../../../listener';
import {isRootProductModel, isSimpleProduct, isVariantProduct} from '../../../../helper/ProductEditForm/Product';
import {Product} from '../../../../../domain';
import {ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY, PRODUCT_MODEL_ATTRIBUTES_TAB_NAME} from '../../../../constant';

const Router = require('pim/router');

const __ = require('oro/translator');

const SUB_PRODUCT_MODEL_LEVEL = 1;
const ROOT_PRODUCT_MODEL_LEVEL = 0;

interface TooManyAttributesLinkProps {
  axis: string;
  attributes: string[];
  numOfAttributes: number;
  product: Product;
}

const handleClick = (product: Product, attributes: string[], axis: string) => {
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

const TooManyAttributesLink: FunctionComponent<TooManyAttributesLinkProps> = ({
  axis,
  attributes,
  numOfAttributes,
  product,
}) => {
  return (
    <>
      <button
        onClick={() => handleClick(product, attributes, axis)}
        className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsManyAttributes"
      >
        {__('akeneo_data_quality_insights.product_evaluation.messages.too_many_attributes', {count: numOfAttributes})}
      </button>
    </>
  );
};

export default TooManyAttributesLink;
