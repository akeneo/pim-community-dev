import {
  assetFamilyEditionLabelUpdated,
  assetFamilyEditionReceived,
  assetFamilyEditionUpdated,
  assetFamilyEditionErrorOccured,
  assetFamilyEditionSucceeded,
  assetFamilyAssetCountUpdated,
  assetFamilyEditionAttributeAsMainMediaUpdated,
  assetFamilyEditionTransformationsUpdated,
  assetFamilyEditionSubmission,
  assetFamilyEditionNamingConventionUpdated,
  assetFamilyEditionProductLinkRulesUpdated,
} from 'akeneoassetmanager/domain/event/asset-family/edit';
import {
  notifyAssetFamilyWellSaved,
  notifyAssetFamilySaveFailed,
} from 'akeneoassetmanager/application/action/asset-family/notify';
import assetFamilySaver from 'akeneoassetmanager/infrastructure/saver/asset-family';
import assetFamilyFetcher, {AssetFamilyResult} from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {assetFamilyPermissionChanged} from 'akeneoassetmanager/domain/event/user';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {TransformationCollection} from 'akeneoassetmanager/domain/model/asset-family/transformation';
import NamingConvention from 'akeneoassetmanager/domain/model/asset-family/naming-convention';
import ProductLinkRuleCollection from 'akeneoassetmanager/domain/model/asset-family/product-link-rule-collection';

export const saveAssetFamily = () => async (dispatch: any, getState: () => EditState): Promise<void> => {
  const assetFamily = getState().form.data;

  dispatch(assetFamilyEditionSubmission());
  try {
    const errors = await assetFamilySaver.save(assetFamily);
    if (errors) {
      if (Array.isArray(errors)) {
        dispatch(assetFamilyEditionErrorOccured(errors));
      } else {
        console.error(errors);
      }
      dispatch(notifyAssetFamilySaveFailed());

      return;
    }
  } catch (error) {
    dispatch(notifyAssetFamilySaveFailed());

    return;
  }

  dispatch(assetFamilyEditionSucceeded());
  dispatch(notifyAssetFamilyWellSaved());

  dispatch(refreshAssetFamily(assetFamily.identifier));
};

export const refreshAssetFamily = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  refreshDataForm: boolean = false
) => async (dispatch: any): Promise<void> => {
  const assetFamilyResult: AssetFamilyResult = await assetFamilyFetcher.fetch(assetFamilyIdentifier);
  if (refreshDataForm) {
    dispatch(assetFamilyAssetCountUpdated(assetFamilyResult.assetCount));
  }
  dispatch(assetFamilyEditionReceived(assetFamilyResult.assetFamily));
  dispatch(assetFamilyPermissionChanged(assetFamilyResult.permission));
};

export const assetFamilyLabelUpdated = (value: string, locale: string) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionLabelUpdated(value, locale));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};

export const attributeAsMainMediaUpdated = (attributeAsMainMedia: AttributeIdentifier) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionAttributeAsMainMediaUpdated(attributeAsMainMedia));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};

export const assetFamilyTransformationsUpdated = (transformations: TransformationCollection) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionTransformationsUpdated(transformations));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};

export const assetFamilyNamingConventionUpdated = (namingConvention: NamingConvention) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionNamingConventionUpdated(namingConvention));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};

export const assetFamilyProductLinkRulesUpdated = (productLinkRules: ProductLinkRuleCollection) => (
  dispatch: any,
  getState: () => EditState
) => {
  dispatch(assetFamilyEditionProductLinkRulesUpdated(productLinkRules));
  dispatch(assetFamilyEditionUpdated(getState().form.data));
};
