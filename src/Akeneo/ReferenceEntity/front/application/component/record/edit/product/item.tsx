import * as React from 'react';
import Product from 'akeneoreferenceentity/domain/model/product/product';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
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

  return (
    <a
      href={path}
      target="_blank"
      title={product.getLabel(locale)}
      className={`AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder ${
        isLoading ? 'AknLoadingPlaceHolder' : ''
      } ${product.getType() === 'product_model' ? 'AknGrid-bodyRow--withLayer' : ''}`}
      data-identifier={product.getIdentifier().stringValue()}
    >
      <span
        className="AknGrid-fullImage"
        style={{
          backgroundImage: `url("${getImageShowUrl(product.getImage(), 'thumbnail')}")`,
        }}
      />
      <span className="AknGrid-title">{product.getLabel(locale)}</span>
      <span className="AknGrid-subTitle">{product.getIdentifier().stringValue()}</span>
    </a>
  );
};
