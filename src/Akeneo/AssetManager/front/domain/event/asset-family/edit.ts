import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {ValidationError} from '@akeneo-pim-community/shared';
import AttributeIdentifier from 'akeneoassetmanager/domain/model/attribute/identifier';
import {TransformationCollection} from 'akeneoassetmanager/domain/model/asset-family/transformation';
import NamingConvention from 'akeneoassetmanager/domain/model/asset-family/naming-convention';
import ProductLinkRuleCollection from 'akeneoassetmanager/domain/model/asset-family/product-link-rule-collection';

export const assetFamilyEditionReceived = (assetFamily: AssetFamily) => {
  return {type: 'ASSET_FAMILY_EDITION_RECEIVED', assetFamily};
};

export const assetFamilyAssetCountUpdated = (assetCount: number) => {
  return {type: 'GRID_TOTAL_COUNT_UPDATED', totalCount: assetCount};
};

export const assetFamilyEditionUpdated = (assetFamily: AssetFamily) => {
  return {type: 'ASSET_FAMILY_EDITION_UPDATED', assetFamily};
};

export const assetFamilyEditionLabelUpdated = (value: string, locale: string) => {
  return {type: 'ASSET_FAMILY_EDITION_LABEL_UPDATED', value, locale};
};

export const assetFamilyEditionTransformationsUpdated = (transformations: TransformationCollection) => {
  return {type: 'ASSET_FAMILY_EDITION_TRANSFORMATIONS_UPDATED', transformations};
};

export const assetFamilyEditionNamingConventionUpdated = (namingConvention: NamingConvention) => {
  return {type: 'ASSET_FAMILY_EDITION_NAMING_CONVENTION_UPDATED', namingConvention};
};

export const assetFamilyEditionProductLinkRulesUpdated = (productLinkRules: ProductLinkRuleCollection) => {
  return {type: 'ASSET_FAMILY_EDITION_PRODUCT_LINK_RULES_UPDATED', productLinkRules};
};

export const assetFamilyEditionAttributeAsMainMediaUpdated = (attributeAsMainMedia: AttributeIdentifier) => {
  return {type: 'ASSET_FAMILY_EDITION_ATTRIBUTE_AS_MAIN_MEDIA_UPDATED', attributeAsMainMedia};
};

export const assetFamilyEditionSubmission = () => {
  return {type: 'ASSET_FAMILY_EDITION_SUBMISSION'};
};

export const assetFamilyEditionSucceeded = () => {
  return {type: 'ASSET_FAMILY_EDITION_SUCCEEDED'};
};

export const assetFamilyEditionErrorOccured = (errors: ValidationError[]) => {
  return {type: 'ASSET_FAMILY_EDITION_ERROR_OCCURED', errors};
};
