import * as React from 'react';
import Sidebar from 'pimfront/product-grid/application/component/sidebar';
import ProductInterface from 'pimfront/product/domain/model/product';
import { connect } from 'react-redux';
import { GlobalState } from 'pimfront/product-grid/application/store/main';
import { redirectToProduct } from 'pimfront/product-grid/application/action/router';
import { needMoreResultsAction, loadChildrenAction } from 'pimfront/product-grid/application/action/search';
import { Display } from 'pimfront/product-grid/domain/event/display';
import DisplaySwitcher from 'pimfront/product-grid/application/component/header/display-switcher';
import { changeGridDisplay } from 'pimfront/product-grid/domain/event/display';
import Table from 'pimfront/product-grid/application/component/table';

interface GridDispatch {
  onRedirectToProduct: (product: ProductInterface) => void;
  onLoadChildren: (product: ProductInterface) => void;
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
              <div className={this.props.displayType === Display.Gallery ? 'AknGrid--gallery' : ''}>
                <div className="AknGridContainer AknGridContainer--withCheckbox">
                  <Table
                    onRedirectToProduct={this.props.onRedirectToProduct}
                    onLoadChildren={this.props.onLoadChildren}
                    channel={this.props.context.channel}
                    locale={this.props.context.locale}
                    items={this.props.items}
                    displayType={this.props.displayType}
                    withHeader={true}
                  />
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
      onLoadChildren: (product: ProductInterface) => {
        dispatch(loadChildrenAction(product));
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

console.log(process);

export default gridConnector(GridView);
