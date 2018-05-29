import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store, combineReducers} from 'redux';
import routerMiddleware from 'akeneoenrichedentity/infrastructure/middleware/router';
import enrichedEntityReducer from 'akeneoenrichedentity/application/reducer/enriched-entity/index';
import {composeWithDevTools} from 'redux-devtools-extension';
const router = require('pim/router');

export default (debug: boolean = true): Store<any> => {
  return createStore(
    combineReducers(enrichedEntityReducer),
    true === debug
      ? composeWithDevTools(applyMiddleware(thunkMiddleware, routerMiddleware(router)))
      : applyMiddleware(thunkMiddleware, routerMiddleware(router))
  );
};
