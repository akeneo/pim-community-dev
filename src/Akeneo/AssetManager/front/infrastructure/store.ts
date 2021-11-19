import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore, Store, combineReducers} from 'redux';
import routerMiddleware from 'akeneoassetmanager/infrastructure/middleware/router';
import formNotifier from 'akeneoassetmanager/infrastructure/middleware/form-notifier';
import gridMiddleware from 'akeneoassetmanager/infrastructure/middleware/grid';
import userContextMiddleware from 'akeneoassetmanager/infrastructure/middleware/user-context';
import {composeWithDevTools} from 'redux-devtools-extension';
import {Notify, Router, Translate, UserContext} from '@akeneo-pim-community/shared';

type Dependencies = {
  router: Router;
  datagridState: any;
  translate: Translate;
  notify: Notify;
  userContext: UserContext;
};

export default (debug: boolean = true, dependencies: Dependencies) => (reducer: any): Store<any> => {
  return createStore(
    combineReducers(reducer),
    true === debug
      ? composeWithDevTools(
          applyMiddleware(
            thunkMiddleware,
            routerMiddleware(dependencies.router, dependencies.datagridState),
            formNotifier(dependencies.notify, dependencies.translate),
            gridMiddleware(),
            userContextMiddleware(dependencies.userContext)
          )
        )
      : applyMiddleware(
          thunkMiddleware,
          routerMiddleware(dependencies.router, dependencies.datagridState),
          formNotifier(dependencies.notify, dependencies.translate),
          gridMiddleware(),
          userContextMiddleware(dependencies.userContext)
        )
  ) as any;
};
