import * as React from 'react';
import * as ReactDOM from 'react-dom';
import __ from 'akeneoreferenceentity/tools/translator';
import List from 'akeneopimenrichmentassetmanager/assets-collection/list';
import {createStore, Store, applyMiddleware} from 'redux';
import {Provider} from 'react-redux';
import generate from 'akeneopimenrichmentassetmanager/assets-collection/application/value-generator';
import {localeUpdated, channelUpdated} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {valuesUpdated} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {assetCollectionReducer, AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {ThemeProvider} from 'styled-components';
import {updateChannels, updateFamily} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import thunkMiddleware from 'redux-thunk';
import {akeneoTheme} from 'akeneopimenrichmentassetmanager/platform/component/theme';

const Form = require('pim/form');
const UserContext = require('pim/user-context');

class AssetTabForm extends (Form as {new (config: any): any}) {
  attributes = [];
  store: Store<AssetCollectionState>;

  constructor(config: any) {
    super(config);

    this.store = createStore(assetCollectionReducer, applyMiddleware(
      thunkMiddleware
    ));
  }

  configure() {
    // Register the asset tab in the sidebar
    this.trigger('tab:register', {
      code: this.code,
      label: __('pim_enrich.entity.product.module.asset.title')
    });

    UserContext.off('change:catalogLocale change:catalogScope', this.updateContext);
    this.listenTo(UserContext, 'change:catalogLocale', this.updateLocale);
    this.listenTo(UserContext, 'change:catalogScope', this.updateChannel);
    this.listenTo(this.getRoot(), this.getRoot().postUpdateEventName, async () => {
      const values = await generate(this.getFormData());

      this.store.dispatch(valuesUpdated(values));
    });

    this.store.dispatch(localeUpdated(UserContext.get('catalogLocale')));
    this.store.dispatch(channelUpdated(UserContext.get('catalogScope')));
    this.store.dispatch(updateChannels() as any);

    return Form.prototype.configure.apply(this, arguments)
  }

  render() {
    if (null === this.store.getState().structure.family) {
      this.store.dispatch(updateFamily(this.getFormData().family) as any);
    }

    ReactDOM.render(
      (<Provider store={this.store}>
        <ThemeProvider theme={akeneoTheme}>
          <List />
        </ThemeProvider>
      </Provider>),
      this.el
    );
  }

  updateLocale() {
    this.store.dispatch(localeUpdated(UserContext.get('catalogLocale')));
  }

  updateChannel() {
    this.store.dispatch(channelUpdated(UserContext.get('catalogScope')));
  }
}


module.exports = AssetTabForm;
