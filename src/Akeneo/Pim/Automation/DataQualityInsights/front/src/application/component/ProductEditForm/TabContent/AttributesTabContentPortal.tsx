import React, {FunctionComponent} from 'react';
import {createPortal} from 'react-dom';
import AttributesTabContent from "./AttributesTabContent";
import {usePageContext} from "../../../../infrastructure/hooks";
import {ATTRIBUTES_TAB_NAME} from "../../../constant";

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

const isVisible = (currentTab: string) => {
  return currentTab === ATTRIBUTES_TAB_NAME;
};

const AttributesTabContentPortal: FunctionComponent = () => {
  const portalContainer = document.getElementById(CONTAINER_ELEMENT_ID);
  const {currentTab} = usePageContext();

  return portalContainer && createPortal(
    <>
      {isVisible(currentTab) && (
        <AttributesTabContent />
      )}
    </>,
    portalContainer
  );
};

export default AttributesTabContentPortal;
