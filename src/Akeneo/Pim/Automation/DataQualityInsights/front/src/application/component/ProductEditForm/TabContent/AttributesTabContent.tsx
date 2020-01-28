import React, {FunctionComponent} from "react";
import WidgetsList from "./Attributes/EditorHighlight/WidgetsList";
import Popover from "./Attributes/EditorHighlight/Popover";
import TabContentWithPortalDecorator from "./TabContentWithPortalDecorator";
import AttributesContextListener from "../../../listener/AttributesContextListener";
import {ATTRIBUTES_TAB_NAME} from "../../../constant";

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

export interface AttributesTabContentProps {}

const BaseAttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <>
      <AttributesContextListener />

      <WidgetsList/>
      <Popover/>
    </>
  );
};

const AttributesTabContent: FunctionComponent<AttributesTabContentProps> = (props) => {
  return TabContentWithPortalDecorator(BaseAttributesTabContent)({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: ATTRIBUTES_TAB_NAME
  });
};

export default AttributesTabContent;
