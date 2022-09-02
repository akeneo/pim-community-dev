import {useEffect, useState} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import {ProductAttribute} from 'akeneoassetmanager/domain/model/product/attribute';

const useProductAttributes = (assetFamilyIdentifier: AssetFamilyIdentifier) => {
  const [attributes, setAttributes] = useState<ProductAttribute[] | null>(null);
  const [selectedAttribute, setSelectedAttribute] = useState<ProductAttribute | null>(null);
  const router = useRouter();

  useEffect(() => {
    (async () => {
      const response = await fetch(
        router.generate('akeneo_asset_manager_linked_product_attributes', {
          assetFamilyIdentifier: assetFamilyIdentifier,
        }),
        {}
      );
      const attributes = await response.json();

      setAttributes(attributes);
      setSelectedAttribute(attributes.length > 0 ? attributes[0] : null);
    })();
  }, [assetFamilyIdentifier]);

  return [attributes, selectedAttribute, setSelectedAttribute] as const;
};

export {useProductAttributes};
