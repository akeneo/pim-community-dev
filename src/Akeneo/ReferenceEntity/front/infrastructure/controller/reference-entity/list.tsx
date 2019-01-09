import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import ReferenceEntityView from 'akeneoreferenceentity/application/component/reference-entity/index';
import createStore from 'akeneoreferenceentity/infrastructure/store';
import referenceEntityReducer from 'akeneoreferenceentity/application/reducer/reference-entity/index';
import {updateReferenceEntityResults} from 'akeneoreferenceentity/application/action/reference-entity/search';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
} from 'akeneoreferenceentity/domain/event/user';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if ('Escape' === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class ReferenceEntityListController extends BaseController {
  renderRoute() {
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-reference-entity'});

    const store = createStore(true)(referenceEntityReducer);
    store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
    store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
    store.dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
    store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
    store.dispatch(updateReferenceEntityResults());
    document.addEventListener('keydown', shortcutDispatcher(store));

    ReactDOM.render(
      <Provider store={store}>
        <ReferenceEntityView />
      </Provider>,
      this.el
    );

    return $.Deferred().resolve();
  }

  beforeUnload = () => {
    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };
}

export = ReferenceEntityListController;
