import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store} from 'redux';
import ProductInterface from 'pimfront/product-grid/domain/model/product';
import routerMiddleware from 'pimfront/tools/router-middleware';
import gridReducer, {State} from 'pimfront/product-grid/application/reducer/main';
import {composeWithDevTools} from 'redux-devtools-extension';
const router = require('pim/router');

export type GlobalState = State<ProductInterface>;

export default (debug: boolean = true): Store<GlobalState> => {
  return createStore<GlobalState>(
    gridReducer,
    composeWithDevTools(applyMiddleware(thunkMiddleware, routerMiddleware(router)))
  );
};
