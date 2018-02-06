import * as React from 'react';
import ProductInterface from 'pimfront/product/domain/model/product';
import * as trans from 'pimenrich/lib/translator';
import ListView from 'pimfront/product-grid/application/component/item/list';
import GalleryView from 'pimfront/product-grid/application/component/item/gallery';
import { Display } from 'pimfront/product-grid/domain/event/display';

interface TableState {
  locale: string;
  channel: string;
  items: ProductInterface[];
  displayType: Display;
  withHeader?: boolean
  depth?: number
};

interface TableDispatch {
  onRedirectToProduct: (product: ProductInterface) => void;
  onLoadChildren: (product: ProductInterface) => void;
}

export default (
  {items, locale, channel, displayType, onRedirectToProduct, onLoadChildren, withHeader = true, depth = 0}:
  TableState & TableDispatch
): any => {
  const ItemView = displayType === Display.Gallery ? GalleryView : ListView;

  return (
    <table className="AknGrid">
      {withHeader ? <thead className="AknGrid-header">
        <tr className="AknGrid-bodyRow">
          <th className="AknGrid-headerCell AknGrid-headerCell--checkbox select-all-header-cell"></th>
          <th className="AknGrid-headerCell">{trans.get('ID')} <span className="AknGrid-caret AknCaret caret"></span></th>
          <th className="AknGrid-headerCell"><span>{trans.get('Image')}</span></th>
          <th className="AknGrid-headerCell"><span>{trans.get('Label')}</span></th>
          <th className="AknGrid-headerCell">{trans.get('Family')} <span className="AknGrid-caret AknCaret caret"></span></th>
          <th className="AknGrid-headerCell">{trans.get('Status')} <span className="AknGrid-caret AknCaret caret"></span></th>
          <th className="AknGrid-headerCell">{trans.get('Complete')} <span className="AknGrid-caret AknCaret caret"></span></th>
          <th className="AknGrid-headerCell">{trans.get('Created at')} <span className="AknGrid-caret AknCaret caret"></span></th>
          <th className="AknGrid-headerCell AknGrid-headerCell--descending descending">
            {trans.get('Updated at')} <span className="AknGrid-caret AknCaret caret"></span>
          </th>
          <th className="AknGrid-headerCell"><span>{trans.get('Variant products')}</span></th>
          <th className="AknGrid-headerCell action-column"></th>
        </tr>
      </thead> : null}
      <tbody className="AknGrid-body">
        {items.map((product: ProductInterface) => (
          <ItemView
            key={product.getIdentifier()}
            product={product}
            channel={channel}
            locale={locale}
            onRedirectToProduct={onRedirectToProduct}
            onLoadChildren={onLoadChildren}
            depth={depth}
          />
        ))}
      </tbody>
    </table>
  )
};
