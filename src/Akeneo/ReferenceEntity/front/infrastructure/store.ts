import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store, combineReducers} from 'redux';
import routerMiddleware from 'akeneoreferenceentity/infrastructure/middleware/router';
import formNotifier from 'akeneoreferenceentity/infrastructure/middleware/form-notifier';
import gridMiddleware from 'akeneoreferenceentity/infrastructure/middleware/grid';
import userContextMiddleware from 'akeneoreferenceentity/infrastructure/middleware/user-context';
import {composeWithDevTools} from 'redux-devtools-extension';
const router = require('pim/router');

export default (debug: boolean = true) => (reducer: any): Store<any> => {
  return createStore(
    combineReducers(reducer),
    true === debug
      ? composeWithDevTools(
          applyMiddleware(
            thunkMiddleware,
            routerMiddleware(router),
            formNotifier(),
            gridMiddleware(),
            userContextMiddleware()
          )
        )
      : applyMiddleware(
          thunkMiddleware,
          routerMiddleware(router),
          formNotifier(),
          gridMiddleware(),
          userContextMiddleware()
        )
  );
};
