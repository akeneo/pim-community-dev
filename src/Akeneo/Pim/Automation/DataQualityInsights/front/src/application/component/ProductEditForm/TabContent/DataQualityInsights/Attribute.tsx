import React, {FunctionComponent, ReactElement} from 'react';
import {DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE} from "../../../../listener";
import {Product} from "../../../../../domain";
import {isSimpleProduct} from "../../../../helper/ProductEditForm/Product";
import {PRODUCT_MODEL_ATTRIBUTES_TAB_NAME} from "../../../../constant";
import {ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY} from "../AttributesTabContent";

const Router = require('pim/router');

const SUB_PRODUCT_MODEL_LEVEL = 1;
const ROOT_PRODUCT_MODEL_LEVEL = 0;

interface AttributeProps {
  attributeCode: string;
  label: string;
  separator: ReactElement | null;
  product: Product;
}

const handleClick = (attributeCode: string, product: Product) => {
  if (isSimpleProduct(product) || product.meta.attributes_for_this_level.includes(attributeCode)) {
    window.dispatchEvent(new CustomEvent(DATA_QUALITY_INSIGHTS_SHOW_ATTRIBUTE, {
     detail: {
       code: attributeCode,
     }
    }));
  } else {
    sessionStorage.setItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY, attributeCode);
    sessionStorage.setItem('current_column_tab', PRODUCT_MODEL_ATTRIBUTES_TAB_NAME);
    let modelId = product.meta.variant_navigation[SUB_PRODUCT_MODEL_LEVEL].selected.id;
    if(! product.meta.hasOwnProperty('parent_attributes') || ! product.meta.parent_attributes.includes(attributeCode)) {
      modelId = product.meta.variant_navigation[ROOT_PRODUCT_MODEL_LEVEL].selected.id;
    }
    window.location.href = '#' + Router.generate('pim_enrich_product_model_edit', {id: modelId});
  }
};

const Attribute: FunctionComponent<AttributeProps> = ({attributeCode, label, separator, product}) => {

  const content =
    <>
      <span data-testid={"dqiAttributeWithRecommendation"}>{label}</span>
      {separator}
    </>;

  return (
    <button onClick={() => handleClick(attributeCode, product)} className="AknActionButton AknActionButton--withoutBorder AknDataQualityInsightsAttribute AknDataQualityInsightsAttribute--link">
      {content}
    </button>
  );
};

export default Attribute;



