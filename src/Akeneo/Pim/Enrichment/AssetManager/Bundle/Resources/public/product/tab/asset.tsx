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
import {errorsReceived, errorsRemovedAll} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import thunkMiddleware from 'redux-thunk';
import {akeneoTheme} from 'akeneoassetmanager/application/component/app/theme';
import {updateRuleRelations} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {LegacyValue} from 'web/bundles/akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {isValidErrorCollection, denormalizeErrorCollection} from 'akeneopimenrichmentassetmanager/platform/model/validation-error';

const Form = require('pim/form');
const UserContext = require('pim/user-context');

const updateValueMiddleware = (formView: AssetTabForm) => {
  return () => (next: any) => (action: any) => {
    if ('VALUE_CHANGED' === action.type) {
      const valueToUpdate = formView.getFormData().values[action.value.attribute.code].find((value: LegacyValue) => {
        return value.locale === action.value.locale &&
          value.scope === action.value.channel
      })

      valueToUpdate.data = action.value.data;
      formView.getRoot().trigger('pim_enrich:form:entity:update_state');
    }

    return next(action);
  }
}

class AssetTabForm extends (Form as {new (config: any): any}) {
  attributes = [];
  store: Store<AssetCollectionState>;

  constructor(config: any) {
    super(config);

    this.store = createStore(assetCollectionReducer, applyMiddleware(
      thunkMiddleware,
      updateValueMiddleware(this)
    ));
  }

  configure() {
    // Register the asset tab in the sidebar
    this.trigger('tab:register', {
      code: this.code,
      label: __('pim_enrich.entity.product.module.asset.title')
    });

    this.listenTo(UserContext, 'change:catalogLocale', this.updateLocale);
    this.listenTo(UserContext, 'change:catalogScope', this.updateChannel);
    this.listenTo(this.getRoot(), this.getRoot().postUpdateEventName, async () => {
      const values = await generate(this.getFormData());

      this.store.dispatch(valuesUpdated(values));
    });

    //Validation errors
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.addErrors);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.removeErrors);

    this.store.dispatch(localeUpdated(UserContext.get('catalogLocale')));
    this.store.dispatch(channelUpdated(UserContext.get('catalogScope')));
    this.store.dispatch(updateChannels() as any);
    this.store.dispatch(updateRuleRelations() as any);

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

  addErrors(event: any) {
    if (isValidErrorCollection(event.response)) {
      const errorCollection = denormalizeErrorCollection(event.response);
      this.store.dispatch(errorsReceived(errorCollection));
    }
  }

  removeErrors() {
    this.store.dispatch(errorsRemovedAll());
  }
}


module.exports = AssetTabForm;
