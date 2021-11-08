import {EditState} from 'akeneoassetmanager/application/reducer/asset/edit';
import {
  productListAttributeListUpdated,
  productListProductListUpdated,
  productListAttributeSelected,
} from 'akeneoassetmanager/domain/event/asset/product';
import productFetcher from 'akeneoassetmanager/infrastructure/fetcher/product';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeCode from 'akeneoassetmanager/domain/model/product/attribute/code';
import productAttributeFetcher from 'akeneoassetmanager/infrastructure/fetcher/product/attribute';

export const updateAttributeList = (assetFamilyIdentifier: AssetFamilyIdentifier) => async (
  dispatch: any
): Promise<void> => {
  const linkedAttributes = await productAttributeFetcher.fetchLinkedAssetAttributes(assetFamilyIdentifier);

  dispatch(productListAttributeListUpdated(linkedAttributes));
  if (linkedAttributes.length > 0) {
    dispatch(attributeSelected(linkedAttributes[0].getCode()));
  } else {
    dispatch(productListProductListUpdated([], 0));
  }
};

export const updateProductList = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const normalizedAttribute = getState().products.selectedAttribute;

  if (null === normalizedAttribute) {
    return;
  }

  const assetFamilyIdentifier = getState().form.data.assetFamily.identifier;
  const assetCode = getState().form.data.code;
  const attributeCode = normalizedAttribute.code;
  const channel = getState().user.catalogChannel;
  const locale = getState().user.catalogLocale;

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
