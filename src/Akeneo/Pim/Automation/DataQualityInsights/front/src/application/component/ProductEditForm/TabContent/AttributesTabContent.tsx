import React, {FunctionComponent, useEffect} from "react";
import WidgetsList from "./Attributes/EditorHighlight/WidgetsList";
import Popover from "./Attributes/EditorHighlight/Popover";
import TabContentWithPortalDecorator from "./TabContentWithPortalDecorator";
import {TextAttributesContextListener, AttributeToImproveContextListener} from "../../../listener";
import {PRODUCT_ATTRIBUTES_TAB_NAME, PRODUCT_MODEL_ATTRIBUTES_TAB_NAME} from "../../../constant";
import {Product} from "../../../../domain";
import {showDataQualityInsightsAttributeToImproveAction} from "../../../../infrastructure/reducer";
import {useDispatch} from "react-redux";

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

export const ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY = 'data-quality-insights:product-edit-form:attribute-to-improve';

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
  const dispatchAction = useDispatch();

  const {product} = props;
  const tabName = product.meta.model_type === "product" ? PRODUCT_ATTRIBUTES_TAB_NAME : PRODUCT_MODEL_ATTRIBUTES_TAB_NAME;

  useEffect(() => {
    const attributeToImprove = sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY);
    if (attributeToImprove !== null) {
      dispatchAction(showDataQualityInsightsAttributeToImproveAction(attributeToImprove));
      sessionStorage.removeItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY);
    }
  }, []);

  return TabContentWithPortalDecorator(BaseAttributesTabContent)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: tabName
  });
};

export default AttributesTabContent;
