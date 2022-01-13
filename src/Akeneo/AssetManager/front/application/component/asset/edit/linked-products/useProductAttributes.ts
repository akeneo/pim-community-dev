import {useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier, {
  assetFamilyidentifiersAreEqual,
  denormalizeAssetFamilyIdentifier,
} from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ProductAttribute} from 'akeneoassetmanager/domain/model/product/attribute';
import hydrate from 'akeneoassetmanager/application/hydrator/product/attribute';

const ASSET_COLLECTION_ATTRIBUTE_LIMIT = 100;

const useProductAttributes = (assetFamilyIdentifier: AssetFamilyIdentifier) => {
  const [attributes, setAttributes] = useState<ProductAttribute[] | null>(null);
  const [selectedAttribute, setSelectedAttribute] = useState<ProductAttribute | null>(null);
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const response = await fetch(
        router.generate('pim_enrich_attribute_rest_index', {
          types: ['pim_catalog_asset_collection'],
          options: {limit: ASSET_COLLECTION_ATTRIBUTE_LIMIT},
        }),
        {}
      );
      const attributes = await response.json();

      const productAttributes = attributes
        .filter((attribute: ProductAttribute) =>
          assetFamilyidentifiersAreEqual(
            assetFamilyIdentifier,
            denormalizeAssetFamilyIdentifier(attribute.reference_data_name)
          )
        )
        .map(hydrate);

      setAttributes(productAttributes);

      setSelectedAttribute(productAttributes.length > 0 ? productAttributes[0] : null);
    })();
  }, [assetFamilyIdentifier]);

  return [attributes, selectedAttribute, setSelectedAttribute] as const;
};

export {useProductAttributes};
