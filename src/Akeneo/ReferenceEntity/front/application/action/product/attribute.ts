import {EditState} from 'akeneoreferenceentity/application/reducer/record/edit';
import promisify from 'akeneoreferenceentity/tools/promisify';
import {
  productListAttributeListUpdated,
  productListProductListUpdated,
  productListAttributeSelected,
} from 'akeneoreferenceentity/domain/event/record/product';
import productFetcher from 'akeneoreferenceentity/infrastructure/fetcher/product';
import ReferenceEntityIdentifier, {
  createIdentifier,
} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import Product from 'akeneoreferenceentity/domain/model/product/product';

const fetcherRegistry = require('pim/fetcher-registry');

export const updateAttributeList = (referenceEntityIdentifier: ReferenceEntityIdentifier) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const attributes = await promisify(
    fetcherRegistry
      .getFetcher('attribute')
      .fetchByTypes(['akeneo_reference_entity_collection', 'akeneo_reference_entity'])
  );

  const linkedAttributes = attributes.filter(
    (attribute: any) => referenceEntityIdentifier.stringValue() === attribute.reference_data_name
  );

  dispatch(productListAttributeListUpdated(linkedAttributes));
  if (linkedAttributes.length > 0) {
    dispatch(attributeSelected(linkedAttributes[0].code));
  }
};

export const attributeSelected = (attributeCode: string) => async (
  dispatch: any,
  getState: () => EditState
): Promise<void> => {
  const referenceEntityIdentifier = createIdentifier(getState().form.data.reference_entity_identifier);
  const recordCode = createCode(getState().form.data.code);
  const products = await productFetcher.fetchLinkedProducts(referenceEntityIdentifier, recordCode, attributeCode);

  dispatch(productListAttributeSelected(attributeCode));
  dispatch(productListProductListUpdated(products.map((product: Product) => product.normalize())));
};
