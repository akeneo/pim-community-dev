import React from 'react';
import {Card, Link} from 'akeneo-design-system';
import {useRoute} from '@akeneo-pim-community/shared';
import Product, {isProductModel} from 'akeneoreferenceentity/domain/model/product/product';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import Completeness from 'akeneoreferenceentity/domain/model/product/completeness';
import {ProductCompleteness} from 'akeneoreferenceentity/application/component/app/product-completeness';

type ProductCardProps = {
  product: Product;
  locale: string;
};

const ProductCard = ({product, locale}: ProductCardProps) => {
  const productRoute =
    product.getType() === 'product'
      ? useRoute('pim_enrich_product_edit', {uuid: product.getId().stringValue()})
      : useRoute('pim_enrich_product_model_edit', {id: product.getId().stringValue()});

  const completeness = Completeness.createFromNormalized(product.getCompleteness().normalize());
  const label = product.getLabel(locale);

  return (
    <Card stacked={isProductModel(product)} fit="contain" src={getImageShowUrl(product.getImage(), 'thumbnail')}>
      <Card.BadgeContainer>
        <ProductCompleteness completeness={completeness} type={product.getType()} />
      </Card.BadgeContainer>
      <Link href={`#${productRoute}`} target="_blank" title={label}>
        {label}
      </Link>
    </Card>
  );
};

export {ProductCard};
