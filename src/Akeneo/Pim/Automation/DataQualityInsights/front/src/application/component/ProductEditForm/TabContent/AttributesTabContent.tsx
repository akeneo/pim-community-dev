import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';
import TabContentWithPortalDecorator from '../../TabContentWithPortalDecorator';

import {Product} from '../../../../domain';

import usePageContext from '../../../../infrastructure/hooks/ProductEditForm/usePageContext';
import {AttributeToImproveContextListener} from '../../../listener';
import {
  ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY,
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
} from '../../../constant';
import {showDataQualityInsightsAttributeToImproveAction} from '../../../../infrastructure/reducer';

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

export interface AttributesTabContentProps {
  product: Product;
}

const BaseAttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <>
      <AttributeToImproveContextListener />
    </>
  );
};

const AttributesTabContent: FunctionComponent<AttributesTabContentProps> = props => {
  const dispatchAction = useDispatch();

  const {product} = props;
  const tabName =
    product.meta.model_type === 'product' ? PRODUCT_ATTRIBUTES_TAB_NAME : PRODUCT_MODEL_ATTRIBUTES_TAB_NAME;

  useEffect(() => {
    const attributeToImprove = sessionStorage.getItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY);
    if (attributeToImprove !== null) {
      dispatchAction(showDataQualityInsightsAttributeToImproveAction(attributeToImprove));
      sessionStorage.removeItem(ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY);
    }
  }, []);

  return TabContentWithPortalDecorator(
    BaseAttributesTabContent,
    usePageContext
  )({
    ...props,
    containerId: CONTAINER_ELEMENT_ID,
    tabName: tabName,
  });
};

export default AttributesTabContent;
