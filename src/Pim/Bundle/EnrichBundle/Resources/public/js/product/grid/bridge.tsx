import * as ReactDOM from 'react-dom';
import * as React from 'react';
import {Provider} from 'react-redux';
const userContext = require('pim/user-context');
import {addFilter} from 'pimfront/product-grid/application/action/filter';
import {updateChannels} from 'pimfront/app/application/action/channel';
import store from 'pimfront/product-grid/application/store/main';
import Grid from 'pimfront/product-grid/application/component/grid';
import {catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged} from 'pimfront/app/domain/event/user';

const render = (Component: any) => (DOMElement: HTMLElement) => {
  store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
  store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
  store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
  store.dispatch(updateChannels());
  store.dispatch(addFilter('enabled'));

  return ReactDOM.render(
    <Provider store={store}>
      <Component />
    </Provider>,
    DOMElement as HTMLElement
  );
};

export default render(Grid);
