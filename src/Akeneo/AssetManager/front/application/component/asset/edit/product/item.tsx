import * as React from 'react';
import Product, {PRODUCT_MODEL_TYPE} from 'akeneoreferenceentity/domain/model/product/product';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import Completeness from 'akeneoreferenceentity/domain/model/product/completeness';
import ProductCompletenessLabel from 'akeneoreferenceentity/application/component/app/product-completeness';

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
    '' !== product.getId().stringValue()
      ? `#${router.generate(`pim_enrich_${product.getType()}_edit`, {
          id: product.getId().stringValue(),
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
      data-identifier={product.getIdentifier().stringValue()}
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
      <span className="AknGrid-subTitle">{product.getIdentifier().stringValue()}</span>
    </a>
  );
};
