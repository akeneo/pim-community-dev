import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store} from 'redux';
import ProductInterface from 'pimfront/product-grid/domain/model/product';
import routerMiddleware from 'pimfront/tools/router-middleware';
import gridReducer, {State} from 'pimfront/product-grid/application/reducer/main';
const router = require('pim/router');
import logger from 'redux-logger';

export type GlobalState = State<ProductInterface>;

export default (debug: boolean = true): Store<GlobalState> => {
  const middleWares = debug
    ? [thunkMiddleware, routerMiddleware(router), logger]
    : [thunkMiddleware, routerMiddleware(router)];

  return createStore<GlobalState>(gridReducer, applyMiddleware(...middleWares));
};
