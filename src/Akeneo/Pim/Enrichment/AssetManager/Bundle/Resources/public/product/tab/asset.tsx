import * as React from 'react';
import * as ReactDOM from 'react-dom';
// import RecordSelector from 'akeneoreferenceentity/application/component/app/record-selector';
// import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
// import RecordCode, {createCode as createRecordCode} from 'akeneoreferenceentity/domain/model/record/code';
// import LocaleReference from 'akeneoreferenceentity/domain/model/locale-reference';
// import ChannelReference from 'akeneoreferenceentity/domain/model/channel-reference';
import __ from 'akeneoreferenceentity/tools/translator';
import List from 'akeneopimenrichmentassetmanager/assets-collection/list';
import {combineReducers, createStore} from 'redux';
import {Provider} from 'react-redux';
import generate from "../../assets-collection/application/value-generator";

const Form = require('pim/form');
const fetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

class AssetTabForm extends (Form as {new (config: any): any}) {
  attributes = [];
  store: any;

  constructor(config: any) {
    super(config);

    this.store = createStore(combineReducers({
      context: contextReducer,
      structure: structureReducer,
      values: dataReducer
    }));
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
      fetcherRegistry.getFetcher('attribute').fetchByTypes(['akeneo_asset_multiple_link']).then((attributes: any[]) => {
        this.store.dispatch(attributeListUpdated(attributes));
      })
    );
  }

  render() {
    const values = this.getFormData().values;

    ReactDOM.render(
      (<Provider store={this.store}>
        <List values={values} />
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

const localeUpdated = (locale: string) => {
  return {type: 'LOCALE_UPDATED', locale}
};

const channelUpdated = (channel: string) => {
  return {type: 'CHANNEL_UPDATED', channel}
};

const attributeListUpdated = (attributes: any[]) => {
  return {type: 'ATTRIBUTE_LIST_UPDATED', attributes};
};

const valuesUpdated = (values: any[]) => {
  return {type: 'VALUE_COLLECTION_UPDATED', values};
};

const structureReducer = (state: any = {attributes: []}, action: any) => {
  switch (action.type) {
    case 'ATTRIBUTE_LIST_UPDATED':
      state = {...state, attributes: action.attributes};
      break;
    default:
      break;
  }

  return state;
};

const contextReducer = (state: {locale: String, channel: String} = {locale: '', channel: ''}, action: {type: String, channel?: String, locale?: String}) => {
  switch (action.type) {
    case 'LOCALE_UPDATED':
      if (action.locale) {
        state = {...state, locale: action.locale};
      }
      break;
    case 'CHANNEL_UPDATED':
      if (action.channel) {
        state = {...state, channel: action.channel};
      }
      break;
    default:
      break;
  }

  return state;
};

const dataReducer = (state: any[] = [], action: {type: string, values: any[]}) => {
  switch (action.type) {
    case 'VALUE_COLLECTION_UPDATED':
      state = action.values;
      break;
    default:
      break;
  }

  return state;
};

module.exports = AssetTabForm;
