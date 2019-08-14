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

const Form = require('pim/form');
const fetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');


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
    this.store.dispatch(channelUpdated(UserContext.get('scopeLocale')));

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
        <List />
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
