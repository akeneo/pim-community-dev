import * as React from 'react';
import ProductInterface, {ProductModel} from 'pimfront/product/domain/model/product';
import {getImageShowUrl} from 'pimfront/tools/media-url-generator';

export default ({
  product,
  locale,
  channel,
  onRedirectToProduct,
}: {product: ProductInterface; channel: string; locale: string} & {
  onRedirectToProduct: (product: ProductInterface) => void;
  onLoadChildren: (product: ProductInterface) => void;
}) => {
  const rowClass =
    'AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder' +
    (product instanceof ProductModel ? ' AknGrid-bodyRow--withLayer' : '');

  return (
    <tr
      title="{product.getLabel(channel, locale)}"
      className={rowClass}
      data-identifier={product.getIdentifier()}
      onClick={() => onRedirectToProduct(product)}
    >
      <td
        className="AknGrid-fullImage"
        style={{backgroundImage: `url("${getImageShowUrl(product.meta.image, 'thumbnail')}")`, display: 'block'}}
      />
      <td style={{display: 'block'}} className="AknGrid-title">
        {product.getLabel(channel, locale)}
      </td>
      <td style={{display: 'block'}} className="AknGrid-subTitle">
        {product.getIdentifier()}
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox select-row-cell" />
      <td className="AknGrid-bodyCell string-cell AknBadge--topRight">
        <span className="AknBadge AknBadge--medium AknBadge--warning">
          {product.getCompleteness(channel, locale).ratio}%
        </span>
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right" />
      </td>
    </tr>
  );
};
