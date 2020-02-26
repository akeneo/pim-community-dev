import React, {FunctionComponent} from "react";
import WidgetsList from "./Attributes/EditorHighlight/WidgetsList";
import Popover from "./Attributes/EditorHighlight/Popover";
import TabContentWithPortalDecorator from "./TabContentWithPortalDecorator";
import {TextAttributesContextListener, AttributeToImproveContextListener} from "../../../listener";
import {PRODUCT_ATTRIBUTES_TAB_NAME, PRODUCT_MODEL_ATTRIBUTES_TAB_NAME} from "../../../constant";
import {Product} from "../../../../domain";

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

export interface AttributesTabContentProps {
  product: Product;
}

const BaseAttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <>
      <TextAttributesContextListener />
      <AttributeToImproveContextListener />

      <WidgetsList/>
      <Popover/>
    </>
  );
};

const AttributesTabContent: FunctionComponent<AttributesTabContentProps> = (props) => {
  const {product} = props;
  const tabName = product.meta.model_type === "product" ? PRODUCT_ATTRIBUTES_TAB_NAME : PRODUCT_MODEL_ATTRIBUTES_TAB_NAME;

  return TabContentWithPortalDecorator(BaseAttributesTabContent)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: tabName
  });
};

export default AttributesTabContent;
