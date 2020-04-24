import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import {BackendAssetFamily} from 'akeneoassetmanager/infrastructure/model/asset-family';
import {denormalizeAssetFamilyIdentifier} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {denormalizeLabelCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {createFileFromNormalized} from 'akeneoassetmanager/domain/model/file';
import {denormalizeAttributeIdentifier} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {denormalizeAttribute} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {denormalizeAssetFamilyNamingConvention} from 'akeneoassetmanager/domain/model/asset-family/naming-convention';
import {denormalizeAssetFamilyProductLinkRules} from 'akeneoassetmanager/domain/model/asset-family/product-link-rule-collection';

export const hydrator = () => (backendAssetFamily: BackendAssetFamily): AssetFamily => {
  return {
    identifier: denormalizeAssetFamilyIdentifier(backendAssetFamily.identifier),
    code: denormalizeAssetFamilyIdentifier(backendAssetFamily.identifier),
    labels: denormalizeLabelCollection(backendAssetFamily.labels),
    image: createFileFromNormalized(backendAssetFamily.image),
    attributeAsMainMedia: denormalizeAttributeIdentifier(backendAssetFamily.attribute_as_main_media),
    attributeAsLabel: denormalizeAttributeIdentifier(backendAssetFamily.attribute_as_label),
    attributes: Object.values(backendAssetFamily.attributes).map(denormalizeAttribute),
    transformations: backendAssetFamily.transformations || [],
    namingConvention: denormalizeAssetFamilyNamingConvention(backendAssetFamily.naming_convention),
    productLinkRules: denormalizeAssetFamilyProductLinkRules(backendAssetFamily.product_link_rules),
    assetCount: backendAssetFamily.asset_count,
  };
};

export default hydrator();
