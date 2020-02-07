import * as React from 'react';
import styled from 'styled-components';
import {NormalizedProduct} from 'akeneoassetmanager/domain/model/product/product';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';
import ProductCompletenessLabel from 'akeneoassetmanager/application/component/app/product-completeness';
import {ThemedProps} from 'akeneoassetmanager/application/component/app/theme';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';

const router = require('pim/router');

const ProductLink = styled.a`
  display: inline-block;
  height: 165px;
  margin: 0 10px 20px;
  width: 142px;
`;

const ThumbnailContainer = styled.div`
  border: 1px solid ${(props: ThemedProps<void>) => props.theme.color.grey100};
  display: flex;
  height: 142px;
  overflow: hidden;
  width: 142px;
`;

const Thumbnail = styled.img`
  margin: auto;
  max-height: 140px;
  object-fit: contain;
  width: 100%;
`;

const Label = styled.div`
  margin: 5px 0 0;
  color: ${(props: ThemedProps<void>) => props.theme.color.grey120};
`;

export const Product = React.memo(({product, locale}: {product: NormalizedProduct; locale: string}) => {
  const path = `#${router.generate(`pim_enrich_${product.type}_edit`, {
    id: product.id,
  })}`;
  const completeness = Completeness.createFromNormalized(product.completeness);
  const label = getLabelInCollection(product.labels, locale, true, product.identifier);

  return (
    <ProductLink href={path} target="_blank" title={label}>
      <ThumbnailContainer>
        <Thumbnail src={getImageShowUrl(product.image, 'thumbnail')} />
      </ThumbnailContainer>
      <ProductCompletenessLabel completeness={completeness} type={product.type} />
      <Label>{label}</Label>
    </ProductLink>
  );
});
