import * as React from 'react';
import Sidebar from 'pimfront/product-grid/application/component/sidebar';
import ProductInterface, { ProductModel } from 'pimfront/product/domain/model/product';
import { connect } from 'react-redux';
import { GlobalState } from 'pimfront/product-grid/application/store/main';
const __ = require('oro/translator');
import { redirectToProduct } from 'pimfront/product-grid/application/action/router';
import { needMoreResultsAction } from 'pimfront/product-grid/application/action/search';
import { getImageShowUrl } from 'pimfront/tools/media-url-generator';
import { Display } from 'pimfront/product-grid/domain/event/display';
import DisplaySwitcher from 'pimfront/product-grid/application/component/header/display-switcher';
import { changeGridDisplay } from 'pimfront/product-grid/domain/event/display';

interface GridDispatch {
  onRedirectToProduct: (product: ProductInterface) => void;
  onNeedMoreResults: () => void;
  onchangeGridDisplay: (display: Display) => void;
}

interface GridViewState {
  context: {
    locale: string;
    channel: string;
  };
  items: ProductInterface[];
  displayType: Display;
};

const RowView = (
  {product, locale, channel, onRedirectToProduct}:
  {product: ProductInterface, channel: string, locale: string} & {onRedirectToProduct: (product: ProductInterface) => void;}
) => {
  const imageClass = 'AknGrid-image' + (product instanceof ProductModel ?
    ' AknGrid-image--withLayer' :
    '');
  const cellClass = (product instanceof ProductModel ?
    'AknGrid-imageLayer' :
    '');

  return (
    <tr className="AknGrid-bodyRow row-click-action" onClick={() => onRedirectToProduct(product)}>
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

const MosaicView = (
  {product, locale, channel, onRedirectToProduct}:
  {product: ProductInterface, channel: string, locale: string} & {onRedirectToProduct: (product: ProductInterface) => void;}
) => {
  const rowClass = 'AknGrid-bodyRow AknGrid-bodyRow--thumbnail AknGrid-bodyRow--withoutTopBorder' +
    (product instanceof ProductModel ?
    ' AknGrid-bodyRow--withLayer' :
    '');

  return (
    <tr
      title="{product.getLabel(channel, locale)}"
      className={rowClass}
      onClick={() => onRedirectToProduct(product)}
    >
      <td className="AknGrid-fullImage" style={{backgroundImage: `url("${getImageShowUrl(product.meta.image, 'thumbnail')}")`, display: 'block'}}></td>
      <td style={{display: 'block'}} className="AknGrid-title">{product.getLabel(channel, locale)}</td>
      <td style={{display: 'block'}} className="AknGrid-subTitle">{product.getIdentifier()}</td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox select-row-cell"></td>
      <td className="AknGrid-bodyCell string-cell AknBadge--topRight">
        <span className="AknBadge AknBadge--medium AknBadge--warning">{product.getCompleteness(channel, locale).ratio}%</span>
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right"></div>
      </td>
    </tr>
  )
};

export class GridView extends React.Component<
  GridViewState & GridDispatch,
  {}
> {
  handleScroll (event: any) {
    const scrollSize     = event.target.children[0].offsetHeight;
    const scrollPosition = event.target.scrollTop;
    const containerSize  = event.target.offsetHeight;
    const remainingHeightToBottom = scrollSize - scrollPosition - containerSize;

    if (remainingHeightToBottom < 2000) {
      this.props.onNeedMoreResults();
    }
  }

  render () {
    const isGallery = this.props.displayType === Display.Gallery;
    const ItemView = isGallery ? MosaicView : RowView;

    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent" onScroll={this.handleScroll.bind(this)}>
            <div>
              <div className="AknGridToolbar">
                <div className="AknGridToolbar-right AknDisplaySelector">
                  <DisplaySwitcher displayType={this.props.displayType} onDisplayChange={this.props.onchangeGridDisplay}/>
                </div>
              </div>
              <div className={isGallery ? 'AknGrid--gallery' : ''}>
                <div className="AknGridContainer AknGridContainer--withCheckbox">
                  <table className="AknGrid">
                    <thead className="AknGrid-header">
                      <tr className="AknGrid-bodyRow">
                        <th className="AknGrid-headerCell AknGrid-headerCell--checkbox select-all-header-cell"></th>
                        <th className="AknGrid-headerCell">{__('ID')} <span className="AknGrid-caret AknCaret caret"></span></th>
                        <th className="AknGrid-headerCell"><span>{__('Image')}</span></th>
                        <th className="AknGrid-headerCell"><span>{__('Label')}</span></th>
                        <th className="AknGrid-headerCell">{__('Family')} <span className="AknGrid-caret AknCaret caret"></span></th>
                        <th className="AknGrid-headerCell">{__('Status')} <span className="AknGrid-caret AknCaret caret"></span></th>
                        <th className="AknGrid-headerCell">{__('Complete')} <span className="AknGrid-caret AknCaret caret"></span></th>
                        <th className="AknGrid-headerCell">{__('Created at')} <span className="AknGrid-caret AknCaret caret"></span></th>
                        <th className="AknGrid-headerCell AknGrid-headerCell--descending descending">
                          {__('Updated at')} <span className="AknGrid-caret AknCaret caret"></span>
                        </th>
                        <th className="AknGrid-headerCell"><span>{__('Variant products')}</span></th>
                        <th className="AknGrid-headerCell action-column"></th>
                      </tr>
                    </thead>
                    <tbody className="AknGrid-body">
                      {this.props.items.map((product: ProductInterface) => (
                        <ItemView
                          key={product.identifier}
                          product={product}
                          channel={this.props.context.channel}
                          locale={this.props.context.locale}
                          onRedirectToProduct={this.props.onRedirectToProduct}
                        />
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
        <Sidebar />
      </div>
    );
  }
}

export const gridConnector = connect(
  (state: GlobalState): GridViewState => {
    const locale = undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;
    const channel = undefined === state.user.catalogChannel ? '' : state.user.catalogChannel;

    return {
      context: {
        locale,
        channel
      },
      items: state.grid.items,
      displayType: state.productGrid.display
    };
  },
  (dispatch: any): GridDispatch => {
    return {
      onRedirectToProduct: (product: ProductInterface) => {
        dispatch(redirectToProduct(product));
      },
      onNeedMoreResults: () => {
        dispatch(needMoreResultsAction());
      },
      onchangeGridDisplay: (display: Display) => {
        dispatch(changeGridDisplay(display));
      }
    };
  }
);

export default gridConnector(GridView);
