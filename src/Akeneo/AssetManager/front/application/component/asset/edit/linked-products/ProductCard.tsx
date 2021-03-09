import React from 'react';
import {Card, Link} from 'akeneo-design-system';
import {isProductModel, NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';
import {ProductCompleteness} from 'akeneoassetmanager/application/component/app/product-completeness';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {getProductEditUrl} from 'akeneoassetmanager/tools/media-url-generator';
import {getMediaFilePath} from 'akeneoassetmanager/domain/model/asset/data/media-file';

type ProductProps = {
  product: NormalizedProduct;
  locale: string;
};

const ProductCard = ({product, locale}: ProductProps) => {
  const completeness = Completeness.createFromNormalized(product.completeness);
  const label = getLabelInCollection(product.labels, locale, true, product.identifier);

  return (
    <Card stacked={isProductModel(product)} fit="contain" src={getMediaFilePath(product.image)}>
      <Card.BadgeContainer>
        <ProductCompleteness completeness={completeness} type={product.type} />
      </Card.BadgeContainer>
      <Link href={getProductEditUrl(product.type, product.id)} target="_blank" title={label}>
        {label}
      </Link>
    </Card>
  );
};

export {ProductCard};
