import React from 'react';
import {Card, Link} from 'akeneo-design-system';
import {Router, useRouter} from '@akeneo-pim-community/shared';
import {isProductModel} from 'akeneoassetmanager/domain/model/product/product';
import {Product} from 'akeneoassetmanager/domain/model/product/product';
import {ProductCompleteness} from 'akeneoassetmanager/application/component/app/product-completeness';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {FilePath, isFileEmpty, File} from 'akeneoassetmanager/domain/model/file';

const isAssetManagerImagePath = (path: FilePath): boolean => path.includes('rest/asset_manager/image_preview');
const PLACEHOLDER_PATH = '/bundles/pimui/img/image_default.png';

const getMediaFilePath = (router: Router, mediaFile: File) => {
  if (isFileEmpty(mediaFile)) {
    return PLACEHOLDER_PATH;
  }

  if (isAssetManagerImagePath(mediaFile.filePath)) {
    return mediaFile.filePath;
  }

  return router.generate('pim_enrich_media_show', {
    filename: encodeURIComponent(mediaFile.filePath),
    filter: 'thumbnail',
  });
};

type ProductProps = {
  product: Product;
  locale: string;
};

const ProductCard = ({product, locale}: ProductProps) => {
  const label = getLabelInCollection(product.labels, locale, true, product.identifier);
  const router = useRouter();
  const productEditUrl =
    product.type === 'product'
      ? router.generate('pim_enrich_product_edit', {uuid: product.id})
      : router.generate('pim_enrich_product_model_edit', {id: product.id});

  return (
    <Card stacked={isProductModel(product)} fit="contain" src={getMediaFilePath(router, product.image)}>
      <Card.BadgeContainer>
        <ProductCompleteness completeness={product.completeness} type={product.type} />
      </Card.BadgeContainer>
      <Link href={`#${productEditUrl}`} target="_blank" title={label}>
        {label}
      </Link>
    </Card>
  );
};

export {ProductCard};
