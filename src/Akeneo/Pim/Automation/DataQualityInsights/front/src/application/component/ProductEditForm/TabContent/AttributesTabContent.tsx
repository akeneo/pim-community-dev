import React, {FunctionComponent, useEffect} from 'react';
import {useDispatch} from 'react-redux';
import {TextAttributesContextListener} from '../../../listener';
import {Product} from '../../../../domain';
import {
  ATTRIBUTE_TO_IMPROVE_SESSION_STORAGE_KEY,
  PRODUCT_ATTRIBUTES_TAB_NAME,
  PRODUCT_MODEL_ATTRIBUTES_TAB_NAME,
} from '@akeneo-pim-community/data-quality-insights/src/application/constant';
import {AttributeGroupsStatusProvider} from '@akeneo-pim-community/data-quality-insights/src/application';
import {AttributeToImproveContextListener} from '@akeneo-pim-community/data-quality-insights/src/application/listener';
import {showDataQualityInsightsAttributeToImproveAction} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/reducer';
import TabContentWithPortalDecorator from '@akeneo-pim-community/data-quality-insights/src/application/component/TabContentWithPortalDecorator';
import {usePageContext} from '@akeneo-pim-community/data-quality-insights/src/infrastructure/hooks';
import SpellcheckProductValuesList from './SpellcheckProductValuesList';

export const CONTAINER_ELEMENT_ID = 'attributes-product-tab-content-dqi';

export interface AttributesTabContentProps {
  product: Product;
}

const BaseAttributesTabContent: FunctionComponent<AttributesTabContentProps> = () => {
  return (
    <AttributeGroupsStatusProvider>
      <TextAttributesContextListener />
      <AttributeToImproveContextListener />

      <SpellcheckProductValuesList />
    </AttributeGroupsStatusProvider>
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
