import thunkMiddleware from 'redux-thunk';
import { applyMiddleware, createStore } from 'redux';
import { ProductInterface } from 'pimfront/product/domain/model/product';
import routerMiddleware from 'pimfront/tools/router-middleware';
import gridReducer, { State } from 'pimfront/grid/application/reducer/reducer';
const router = require('pim/router');

export type GlobalState = State<ProductInterface>;

export default createStore<GlobalState>(
  gridReducer,
  applyMiddleware(thunkMiddleware, routerMiddleware(router))
);
