import * as React from 'react';
import ProductInterface from 'pimfront/product/domain/model/product';
import __ from 'pimfront/tools/translator';
import ListView from 'pimfront/product-grid/application/component/item/list';
import GalleryView from 'pimfront/product-grid/application/component/item/gallery';
import {Display} from 'pimfront/product-grid/domain/event/display';

interface TableState {
  locale: string;
  channel: string;
  items: ProductInterface[];
  displayType: Display;
  withHeader?: boolean;
  depth?: number;
}

interface TableDispatch {
  onRedirectToProduct: (product: ProductInterface) => void;
  onLoadChildren: (product: ProductInterface) => void;
}

export default ({
  items,
  locale,
  channel,
  displayType,
  onRedirectToProduct,
  onLoadChildren,
  withHeader = true,
  depth = 0,
}: TableState & TableDispatch): any => {
  const ItemView = displayType === Display.Gallery ? GalleryView : ListView;

  return (
    <table className="AknGrid">
      {withHeader ? (
        <thead className="AknGrid-header">
          <tr className="AknGrid-bodyRow">
            <th className="AknGrid-headerCell AknGrid-headerCell--checkbox select-all-header-cell" />
            <th className="AknGrid-headerCell">
              {__('ID')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell">
              <span>{__('Image')}</span>
            </th>
            <th className="AknGrid-headerCell">
              <span>{__('Label')}</span>
            </th>
            <th className="AknGrid-headerCell">
              {__('Family')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell">
              {__('Status')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell">
              {__('Complete')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell">
              {__('Created at')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell AknGrid-headerCell--descending descending">
              {__('Updated at')} <span className="AknGrid-caret AknCaret caret" />
            </th>
            <th className="AknGrid-headerCell">
              <span>{__('Variant products')}</span>
            </th>
            <th className="AknGrid-headerCell action-column" />
          </tr>
        </thead>
      ) : null}
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
  );
};
