import React from 'react';
import {Card, Link} from 'akeneo-design-system';
import {Router, useRouter} from '@akeneo-pim-community/shared';
import {isProductModel, NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';
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
  product: NormalizedProduct;
  locale: string;
};

const ProductCard = ({product, locale}: ProductProps) => {
  const completeness = Completeness.createFromNormalized(product.completeness);
  const label = getLabelInCollection(product.labels, locale, true, product.identifier);
  const router = useRouter();
  const productEditUrl = router.generate(`pim_enrich_${product.type}_edit`, {id: product.id});

  return (
    <Card stacked={isProductModel(product)} fit="contain" src={getMediaFilePath(router, product.image)}>
      <Card.BadgeContainer>
        <ProductCompleteness completeness={completeness} type={product.type} />
      </Card.BadgeContainer>
      <Link href={`#${productEditUrl}`} target="_blank" title={label}>
        {label}
      </Link>
    </Card>
  );
};

export {ProductCard};
