import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import promisify from 'akeneoassetmanager/tools/promisify';
import {
  productListAttributeListUpdated,
  productListProductListUpdated,
  productListAttributeSelected,
} from 'akeneoassetmanager/domain/event/asset/product';
import productFetcher from 'akeneoassetmanager/infrastructure/fetcher/product';
import AssetFamilyIdentifier, {
  denormalizeAssetFamilyIdentifier,
  assetFamilyidentifiersAreEqual,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {denormalizeAssetCode} from 'akeneoassetmanager/domain/model/asset/code';
import {NormalizedAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import hydrate from 'akeneoassetmanager/application/hydrator/product/attribute';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import {denormalizeChannelReference} from 'akeneoassetmanager/domain/model/channel-reference';
import {denormalizeLocaleReference} from 'akeneoassetmanager/domain/model/locale-reference';
import {denormalizeAttributeCode} from 'akeneoassetmanager/domain/model/attribute/code';

const fetcherRegistry = require('pim/fetcher-registry');

export const updateAttributeList = (assetFamilyIdentifier: AssetFamilyIdentifier) => async (
  dispatch: any
): Promise<void> => {
  const attributes = await promisify(
    fetcherRegistry.getFetcher('attribute').fetchByTypes(['pim_catalog_asset_collection', 'akeneo_asset'], false)
  );

  const linkedAttributes = attributes
    .filter((attribute: NormalizedAttribute) =>
      assetFamilyidentifiersAreEqual(
        assetFamilyIdentifier,
        denormalizeAssetFamilyIdentifier(attribute.reference_data_name)
      )
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

  const assetFamilyIdentifier = getState().form.data.asset_family_identifier;
  const assetCode = denormalizeAssetCode(getState().form.data.code);
  const attributeCode = denormalizeAttributeCode(normalizedAttribute.code);
  const channel = denormalizeChannelReference(getState().user.catalogChannel);
  const locale = denormalizeLocaleReference(getState().user.catalogLocale);

  const products = await productFetcher.fetchLinkedProducts(
    assetFamilyIdentifier,
    assetCode,
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
