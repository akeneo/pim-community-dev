import * as ReactDOM from 'react-dom';
import * as React from 'react';
import { applyMiddleware, createStore } from 'redux';
import thunkMiddleware from 'redux-thunk';
import { Provider, connect } from 'react-redux';
const router = require('pim/router');
const __ = require('oro/translator');
import { ProductInterface } from 'pimfront/js/product/domain/model/product';
const userContext = require('pim/user-context');
import { updateGridAction } from 'pimfront/js/grid/domain/action/search';

const redirectToProduct = (product: ProductInterface) => {
  return {type: 'REDIRECT_TO_ROUTE', route: 'pim_enrich_product_edit', params: {id: product.meta.id}}
}

const reducer = (state: State|undefined, action: {type: string, data: any}): State => {
  if (undefined === state) {
    state = {
      items: []
    };
  }

  switch (action.type) {
    case 'DATA_RECEIVED':
      state = {...state, items: action.data.items}
    break;
    default:
    break;
  }

  return state;
};

const routerMiddleware = (store: any) => (next: any) => (action: any) => {
  if ('REDIRECT_TO_ROUTE' === action.type) {
    router.redirectToRoute(
        action.route,
        action.params ? action.params : {},
        {trigger: true}
    );

    return;
  }

  let result = next(action)

  return result
}

let store = createStore(
  reducer,
  applyMiddleware(thunkMiddleware, routerMiddleware)
);

const GridView = ({items, redirectToProduct}: GridState & GridDispatch) => {
  const itemViews = items.map((item: ProductInterface) => (
    <tr className="AknGrid-bodyRow row-click-action" onClick={() => redirectToProduct(item)}>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--tight AknGrid-bodyCell--checkbox select-row-cell"></td>
      <td className="AknGrid-bodyCell string-cell" data-column="identifier">{item.identifier}</td>
      <td className="AknGrid-bodyCell string-cell">
        <img className="AknGrid-image" src="/media/show/{item.meta.image}/thumbnail_small" title="" />
      </td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--highlight" data-column="label">{item.getLabel('ecommerce', 'en_US')}</td>
      <td className="AknGrid-bodyCell string-cell" data-column="family">{item.family}</td>
      <td className="AknGrid-bodyCell string-cell">
        <div className="AknBadge AknBadge--medium AknBadge--disabled status-disabled"><i className="AknBadge-icon icon-status-disabled icon-circle"></i>Disabled</div>
      </td>
      <td className="AknGrid-bodyCell string-cell">
        <span className="AknBadge AknBadge--medium AknBadge--warning">50%</span>
      </td>
      <td className="AknGrid-bodyCell string-cell" data-column="created">01/05/2018</td>
      <td className="AknGrid-bodyCell string-cell" data-column="updated">01/09/2018</td>
      <td className="AknGrid-bodyCell string-cell">N/A</td>
      <td className="AknGrid-bodyCell AknGrid-bodyCell--actions action-cell">
        <div className="AknButtonList AknButtonList--right"></div>
      </td>
    </tr>
  ));


  return (
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
          {itemViews}
        </tbody>
      </table>
    </div>
  );
};

interface State {
  items: ProductInterface[];
}

interface GridState {
  items: ProductInterface[];
}

interface GridDispatch {
  redirectToProduct: (product: ProductInterface) => void;
}

const Grid = connect(
  (state: State): GridState => {
    return {
      items: state.items
    };
  },
  (dispatch: any): GridDispatch => {
    return {
      redirectToProduct: (product: ProductInterface) => {
        dispatch(redirectToProduct(product));
      }
    };
  }
)(GridView);

const render = (Component: any) => (DOMElement: HTMLElement) => {
  // store.dispatch(updateLocale(userContext.get('catalogLocale')));
  // store.dispatch(updateChannel(userContext.get('catalogScope')));
  store.dispatch(updateGridAction(userContext.get('catalogLocale'), userContext.get('catalogScope'), 25));

  return ReactDOM.render(
    <Provider store={store}>
      <Component />
    </Provider>,
    DOMElement as HTMLElement
  );
};

export default render(Grid);
