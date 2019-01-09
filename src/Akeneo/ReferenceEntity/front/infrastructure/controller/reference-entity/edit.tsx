import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntityView from 'akeneoreferenceentity/application/component/reference-entity/edit';
import createStore from 'akeneoreferenceentity/infrastructure/store';
import referenceEntityReducer from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import referenceEntityFetcher, {
  ReferenceEntityResult,
} from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import {
  referenceEntityEditionReceived,
  referenceEntityRecordCountUpdated,
} from 'akeneoreferenceentity/domain/event/reference-entity/edit';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
} from 'akeneoreferenceentity/domain/event/user';
import {setUpSidebar} from 'akeneoreferenceentity/application/action/sidebar';
import {updateActivatedLocales} from 'akeneoreferenceentity/application/action/locale';
import {updateCurrentTab} from 'akeneoreferenceentity/application/event/sidebar';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {updateChannels} from 'akeneoreferenceentity/application/action/channel';
import {updateFilter, removeFilter} from 'akeneoreferenceentity/application/event/search';
import {getFilter, getCompletenessFilter} from 'akeneoreferenceentity/tools/filter';
import {attributeListGotUpdated} from 'akeneoreferenceentity/application/action/attribute/list';
import {CompletenessValue} from 'akeneoreferenceentity/application/component/record/index/completeness-filter';
const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if ('Escape' === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class ReferenceEntityEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const promise = $.Deferred();

    referenceEntityFetcher
      .fetch(createIdentifier(route.params.identifier))
      .then(async (referenceEntityResult: ReferenceEntityResult) => {
        this.store = createStore(true)(referenceEntityReducer);
        const referenceEntityIdentifier = referenceEntityResult.referenceEntity.getIdentifier().stringValue();
        const userSearch = this.getUserSearch(referenceEntityIdentifier);
        const completenessFilter = this.getCompletenessFilter(referenceEntityIdentifier);

        // Not idea, maybe we should discuss about it
        await this.store.dispatch(updateChannels() as any);
        this.store.dispatch(updateActivatedLocales() as any);
        this.store.dispatch(referenceEntityEditionReceived(referenceEntityResult.referenceEntity.normalize()));
        this.store.dispatch(referenceEntityRecordCountUpdated(referenceEntityResult.recordCount));
        this.store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(setUpSidebar('akeneo_reference_entities_reference_entity_edit') as any);
        this.store.dispatch(updateCurrentTab(route.params.tab));
        this.store.dispatch(updateFilter('full_text', '=', userSearch));
        this.store.dispatch(attributeListGotUpdated(referenceEntityResult.attributes) as any);
        document.addEventListener('keydown', shortcutDispatcher(this.store));
        this.updateCompletenessFilter(completenessFilter);

        mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-reference-entity'});
        $(window).on('beforeunload', this.beforeUnload);

        ReactDOM.render(
          <Provider store={this.store}>
            <ReferenceEntityView />
          </Provider>,
          this.el
        );

        promise.resolve();
      })
      .catch((error: any) => {
        if (error.request) {
          promise.reject(error.request);
        }

        throw error;
      });

    return promise.promise();
  }

  getUserSearch = (referenceEntityIdentifier: string): string => {
    return null !== sessionStorage.getItem(`pim_reference_entity.record.grid.search.${referenceEntityIdentifier}`)
      ? getFilter(
          JSON.parse(sessionStorage.getItem(
            `pim_reference_entity.record.grid.search.${referenceEntityIdentifier}`
          ) as string),
          'full_text'
        ).value
      : '';
  };

  getCompletenessFilter = (referenceEntityIdentifier: string): CompletenessValue => {
    return null !== sessionStorage.getItem(`pim_reference_entity.record.grid.search.${referenceEntityIdentifier}`)
      ? getCompletenessFilter(
          JSON.parse(sessionStorage.getItem(
            `pim_reference_entity.record.grid.search.${referenceEntityIdentifier}`
          ) as string)
        )
      : CompletenessValue.All;
  };

  updateCompletenessFilter = (completenessFilter: CompletenessValue) => {
    switch (completenessFilter) {
      case CompletenessValue.All:
        this.store.dispatch(removeFilter('complete'));
        break;
      case CompletenessValue.Yes:
        this.store.dispatch(updateFilter('complete', '=', true));
        break;
      case CompletenessValue.No:
        this.store.dispatch(updateFilter('complete', '=', false));
        break;
    }
  }

  beforeUnload = () => {
    if (this.isDirty()) {
      return __('pim_enrich.confirmation.discard_changes', {entity: 'reference entity'});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const message = __('pim_enrich.confirmation.discard_changes', {entity: 'reference entity'});

    return this.isDirty() ? confirm(message) : true;
  }

  isDirty() {
    if (undefined === this.store) {
      return false;
    }

    const state = this.store.getState();

    return state.form.state.isDirty || state.attribute.isDirty || state.options.isDirty;
  }
}

export = ReferenceEntityEditController;
