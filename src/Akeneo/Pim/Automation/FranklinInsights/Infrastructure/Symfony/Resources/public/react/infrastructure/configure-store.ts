import {applyMiddleware, createStore, Middleware} from 'redux';
import {composeWithDevTools} from 'redux-devtools-extension';
import thunkMiddleware from 'redux-thunk';

import familyMappingReducer from '../application/reducer/family-mapping';
import {notificationMiddleware} from './middleware/notification';

export function configureStore(middlewares: Middleware[]) {
  const storeEnhancer = applyMiddleware(thunkMiddleware, notificationMiddleware, ...middlewares);

  return createStore(familyMappingReducer, undefined, composeWithDevTools(storeEnhancer));
}
