import * as React from 'react';
import ProductInterface, { ProductModel } from 'pimfront/product/domain/model/product';
import { getImageShowUrl } from 'pimfront/tools/media-url-generator';

export default (
  {product, locale, channel, onRedirectToProduct, onLoadChildren}:
  {product: ProductInterface, channel: string, locale: string} & {
    onRedirectToProduct: (product: ProductInterface) => void;
    onLoadChildren: (product: ProductInterface) => void;
  }
) => {
  const imageClass = 'AknGrid-image' + (product instanceof ProductModel ?
    ' AknGrid-image--withLayer' :
    '');
  const clickAction = () => {
    product instanceof ProductModel ? onLoadChildren(product) : onRedirectToProduct(product);
  }

  return (
    <tr className="AknGrid-bodyRow row-click-action" onClick={clickAction}>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox select-row-cell"></td>
      <td className="AknGrid-bodyCell string-cell" data-column="identifier">{product.getIdentifier()}</td>
      <td className="AknGrid-bodyCell string-cell">
        {product instanceof ProductModel ? <div className="AknGrid-imageLayer" /> : ''}
        <img className={imageClass} src={getImageShowUrl(product.meta.image, 'thumbnail_small')} title="" />
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--highlight" data-column="label">{product.getLabel(channel, locale)}</td>
      <td className="AknGrid-bodyCell string-cell" data-column="family">{product.family}</td>
      <td className="AknGrid-bodyCell string-cell">
        <div className="AknBadge AknBadge--medium AknBadge--disabled status-disabled"><i className="AknBadge-icon icon-status-disabled icon-circle"></i>Disabled</div>
      </td>
      <td className="AknGrid-bodyCell string-cell">
        <span className="AknBadge AknBadge--medium AknBadge--warning">{product.getCompleteness(channel, locale).ratio}%</span>
      </td>
      <td className="AknGrid-bodyCell string-cell" data-column="created">01/05/2018</td>
      <td className="AknGrid-bodyCell string-cell" data-column="updated">01/09/2018</td>
      <td className="AknGrid-bodyCell string-cell">N/A</td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right"></div>
      </td>
    </tr>
  )
};
