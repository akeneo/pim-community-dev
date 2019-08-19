import * as React from 'react';
import * as ReactDOM from 'react-dom';
import __ from 'akeneoreferenceentity/tools/translator';
import List from 'akeneopimenrichmentassetmanager/assets-collection/list';
import {createStore, Store} from 'redux';
import {Provider} from 'react-redux';
import generate from 'akeneopimenrichmentassetmanager/assets-collection/application/value-generator';
import {localeUpdated, channelUpdated} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {valuesUpdated} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/values';
import {assetCollectionReducer, AssetCollectionState} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {attributeListUpdated, Attribute} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {ThemeProvider, ThemedStyledProps} from 'styled-components';

const Form = require('pim/form');
const fetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

type AkeneoTheme = {
  color: {
    grey60: string,
    grey80: string,
    grey100: string,
    grey120: string,
    grey140: string,
    purple100: string,
    yellow100: string,
  },
  fontSize: {
    bigger: string,
    big: string,
    default: string,
    small: string,
  }
}

export type ThemedProps<P> = ThemedStyledProps<P, AkeneoTheme>;

const akeneoTheme: AkeneoTheme = {
  color: {
    grey60: '#f9f9fb',
    grey80: '#d9dde2',
    grey100: '#a1a9b7',
    grey120: '#67768a',
    grey140: '#11324d',
    purple100: '#9452ba',
    yellow100: '#f9b53f',
  },
  fontSize: {
    bigger: '17px',
    big: '15px',
    default: '13px',
    small: '11px',
  }
}

class AssetTabForm extends (Form as {new (config: any): any}) {
  attributes = [];
  store: Store<AssetCollectionState>;

  constructor(config: any) {
    super(config);

    this.store = createStore(assetCollectionReducer);
  }

  configure() {
    this.trigger('tab:register', {
      code: this.code,
      label: __('pim_enrich.entity.product.module.comment.title')
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

    return $.when(
      Form.prototype.configure.apply(this, arguments),
      fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link']).then((attributes: Attribute[]) => {
        this.store.dispatch(attributeListUpdated(attributes));
      })
    );
  }

  render() {
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
