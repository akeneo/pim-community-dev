import * as React from 'react';
import * as ReactDOM from 'react-dom';
import __ from 'akeneoassetmanager/tools/translator';
import List from 'akeneopimenrichmentassetmanager/assets-collection/list';
import {createStore, Store, applyMiddleware, compose} from 'redux';
import {Provider} from 'react-redux';
import generate from 'akeneopimenrichmentassetmanager/assets-collection/application/value-generator';
import {localeUpdated, channelUpdated} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/context';
import {
  valuesUpdated,
  labelsUpdated,
  productIdentifierChanged,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/product';
import {
  assetCollectionReducer,
  AssetCollectionState,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/asset-collection';
import {ThemeProvider} from 'styled-components';
import {
  updateChannels,
  updateFamily,
  updateRuleRelations,
  updateAttributeGroups,
} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/structure';
import {errorsReceived, errorsRemovedAll} from 'akeneopimenrichmentassetmanager/assets-collection/reducer/errors';
import thunkMiddleware from 'redux-thunk';
import {LegacyValue} from 'akeneopimenrichmentassetmanager/enrich/domain/model/product';
import {isValidErrorCollection, denormalizeErrorCollection} from 'akeneoassetmanager/platform/model/validation-error';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {pimTheme} from 'akeneo-design-system';

const Form = require('pim/form');
const UserContext = require('pim/user-context');

const updateValueMiddleware = (formView: AssetTabForm) => {
  return () => (next: any) => (action: any) => {
    if ('VALUE_CHANGED' === action.type) {
      const valueToUpdate = formView.getFormData().values[action.value.attribute.code].find((value: LegacyValue) => {
        return value.locale === action.value.locale && value.scope === action.value.channel;
      });

      valueToUpdate.data = action.value.data;
      formView.getRoot().trigger('pim_enrich:form:entity:update_state');
    }

    return next(action);
  };
};

class AssetTabForm extends (Form as {new (config: any): any}) {
  attributes = [];
  store: Store<AssetCollectionState>;

  constructor(config: any) {
    super(config);

    // @ts-ignore
    const composeEnhancers = window.__REDUX_DEVTOOLS_EXTENSION_COMPOSE__ || compose;
    this.store = createStore(
      assetCollectionReducer,
      composeEnhancers(applyMiddleware(thunkMiddleware, updateValueMiddleware(this)))
    );
  }

  configure() {
    this.registerAssetManagerTab();

    this.listenTo(UserContext, 'change:catalogLocale', this.updateLocale);
    this.listenTo(UserContext, 'change:catalogScope', this.updateChannel);
    this.listenTo(this.getRoot(), this.getRoot().postUpdateEventName, async () => {
      const values = await generate(this.getFormData());

      this.store.dispatch(productIdentifierChanged(this.getFormData().identifier));
      this.store.dispatch(valuesUpdated(values));
      this.store.dispatch(labelsUpdated(this.getFormData().meta.label));
    });

    //Validation errors
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:bad_request', this.addErrors);
    this.listenTo(this.getRoot(), 'pim_enrich:form:entity:pre_save', this.removeErrors);

    this.store.dispatch(localeUpdated(UserContext.get('catalogLocale')));
    this.store.dispatch(channelUpdated(UserContext.get('catalogScope')));
    this.store.dispatch(updateChannels() as any);
    this.store.dispatch(updateRuleRelations() as any);
    this.store.dispatch(updateAttributeGroups() as any);

    return Form.prototype.configure.apply(this, arguments);
  }

  registerAssetManagerTab() {
    this.trigger('tab:register', {
      code: this.code,
      label: __('pim_enrich.entity.product.module.asset.title'),
    });
  }

  render() {
    if (null === this.store.getState().structure.family) {
      this.store.dispatch(updateFamily(this.getFormData().family) as any);
    }

    ReactDOM.render(
      <Provider store={this.store}>
        <DependenciesProvider>
          <ThemeProvider theme={pimTheme}>
            <List />
          </ThemeProvider>
        </DependenciesProvider>
      </Provider>,
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
