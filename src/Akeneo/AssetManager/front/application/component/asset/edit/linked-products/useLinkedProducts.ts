import {useEffect, useState} from 'react';
import {ChannelReference, LocaleReference} from '@akeneo-pim-community/shared';
import {Product} from 'akeneoassetmanager/domain/model/product/product';
import productFetcher from 'akeneoassetmanager/infrastructure/fetcher/product';
import AssetFamilyIdentifier from 'akeneoassetmanager/domain/model/asset-family/identifier';
import AssetCode from 'akeneoassetmanager/domain/model/asset/code';
import {ProductAttribute} from 'akeneoassetmanager/domain/model/product/attribute';

const useLinkedProducts = (
  assetFamilyIdentifier: AssetFamilyIdentifier,
  assetCode: AssetCode,
  selectedAttribute: ProductAttribute | null,
  channel: ChannelReference,
  locale: LocaleReference
) => {
  const [products, setProducts] = useState<Product[] | null>(null);
  const [totalCount, setTotalCount] = useState<number>(0);

  useEffect(() => {
    if (null === selectedAttribute) return;

    (async () => {
      const searchResult = await productFetcher.fetchLinkedProducts(
        assetFamilyIdentifier,
        assetCode,
        selectedAttribute.code,
        channel,
        locale
      );

      setProducts(searchResult.items);
      setTotalCount(searchResult.totalCount);
    })();
  }, [assetFamilyIdentifier, assetCode, selectedAttribute, channel, locale]);

  return [products, totalCount] as const;
};

export {useLinkedProducts};
