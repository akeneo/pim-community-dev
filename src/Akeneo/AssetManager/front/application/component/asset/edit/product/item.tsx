import * as React from 'react';
import Product, {PRODUCT_MODEL_TYPE} from 'akeneoassetmanager/domain/model/product/product';
import {getImageShowUrl} from 'akeneoassetmanager/tools/media-url-generator';
import Completeness from 'akeneoassetmanager/domain/model/product/completeness';
import ProductCompletenessLabel from 'akeneoassetmanager/application/component/app/product-completeness';
import {productidentifiersAreEqual, denormalizeProductIdentifier, productIdentifierStringValue} from 'akeneoassetmanager/domain/model/product/identifier';

const router = require('pim/router');

export default ({
  product,
  locale,
  isLoading = false,
}: {
  product: Product;
  locale: string;
  isLoading?: boolean;
} & {
  onRedirectToProduct: (product: Product) => void;
}) => {
  const path =
    productidentifiersAreEqual(product.getId(), denormalizeProductIdentifier(''))
      ? `#${router.generate(`pim_enrich_${product.getType()}_edit`, {
          id: productIdentifierStringValue(product.getId()),
        })}`
      : '';
  const completeness = Completeness.createFromNormalized(product.getCompleteness().normalize());

  return (
    <a
      href={path}
      target="_blank"
      title={product.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? 'AknLoadingPlaceHolder' : ''
      } ${PRODUCT_MODEL_TYPE === product.getType() ? 'AknGrid-bodyRow--withLayer' : ''}`}
      data-identifier={productIdentifierStringValue(product.getIdentifier())}
    >
      <div
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(product.getImage(), 'thumbnail')}")`,
        }}
      >
        <ProductCompletenessLabel completeness={completeness} type={product.getType()} />
      </div>
      <span className="AknGrid-title">{product.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">{productIdentifierStringValue(product.getIdentifier())}</span>
    </a>
  );
};
