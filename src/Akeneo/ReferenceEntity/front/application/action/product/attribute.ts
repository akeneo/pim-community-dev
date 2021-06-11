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
import {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createCode as createAttributeCode} from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/product/attribute';
import hydrate from 'akeneoreferenceentity/application/hydrator/product/attribute';
import AttributeCode from 'akeneoreferenceentity/domain/model/product/attribute/code';
import {createChannelReference} from 'akeneoreferenceentity/domain/model/channel-reference';
import {createLocaleReference} from 'akeneoreferenceentity/domain/model/locale-reference';

const fetcherRegistry = require('pim/fetcher-registry');

const ATTRIBUTES_LIMIT = 1000;

export const updateAttributeList = (referenceEntityIdentifier: ReferenceEntityIdentifier) => async (
  dispatch: any
): Promise<void> => {
  const attributes = await promisify(
    fetcherRegistry
      .getFetcher('attribute')
      .fetchByTypes(['akeneo_reference_entity_collection', 'akeneo_reference_entity'], false, {limit: ATTRIBUTES_LIMIT})
  );

  const linkedAttributes = attributes
    .filter(
      (attribute: NormalizedAttribute) => referenceEntityIdentifier.stringValue() === attribute.reference_data_name
    )
    .map(hydrate);

  dispatch(productListAttributeListUpdated(linkedAttributes));
  if (linkedAttributes.length > 0) {
    dispatch(attributeSelected(linkedAttributes[0].getCode()));
  }
};

export const updateProductList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const normalizedAttribute = getState().products.selectedAttribute;

  if (null === normalizedAttribute) {
    return;
  }

  const referenceEntityIdentifier = createIdentifier(getState().form.data.reference_entity_identifier);
  const recordCode = createRecordCode(getState().form.data.code);
  const attributeCode = createAttributeCode(normalizedAttribute.code);
  const channel = createChannelReference(getState().user.catalogChannel);
  const locale = createLocaleReference(getState().user.catalogLocale);

  const products = await productFetcher.fetchLinkedProducts(
    referenceEntityIdentifier,
    recordCode,
    attributeCode,
    channel,
    locale
  );

  dispatch(productListProductListUpdated(products.items, products.totalCount));
};

export const attributeSelected = (attributeCode: AttributeCode) => async (dispatch: any): Promise<void> => {
  dispatch(productListAttributeSelected(attributeCode));
  dispatch(updateProductList());
};
