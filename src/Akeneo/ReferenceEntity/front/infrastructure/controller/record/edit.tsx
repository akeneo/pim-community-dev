import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoreferenceentity/tools/translator';
import RecordView from 'akeneoreferenceentity/application/component/record/edit';
import Record from 'akeneoreferenceentity/domain/model/record/record';
import createStore from 'akeneoreferenceentity/infrastructure/store';
import recordReducer from 'akeneoreferenceentity/application/reducer/record/edit';
import recordFetcher from 'akeneoreferenceentity/infrastructure/fetcher/record';
import {recordEditionReceived} from 'akeneoreferenceentity/domain/event/record/edit';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
} from 'akeneoreferenceentity/domain/event/user';
import {setUpSidebar} from 'akeneoreferenceentity/application/action/sidebar';
import {updateActivatedLocales} from 'akeneoreferenceentity/application/action/locale';
import {updateChannels} from 'akeneoreferenceentity/application/action/channel';
import {updateCurrentTab} from 'akeneoreferenceentity/application/event/sidebar';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if ('Escape' === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class RecordEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const promise = $.Deferred();

    recordFetcher
      .fetch(
        createReferenceEntityIdentifier(route.params.referenceEntityIdentifier),
        createCode(route.params.recordCode)
      )
      .then((record: Record) => {
        this.store = createStore(true)(recordReducer);
        this.store.dispatch(recordEditionReceived(record));
        this.store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(setUpSidebar('akeneo_reference_entities_record_edit') as any);
        this.store.dispatch(updateCurrentTab(route.params.tab));
        this.store.dispatch(updateActivatedLocales() as any);
        this.store.dispatch(updateChannels() as any);
        document.addEventListener('keydown', shortcutDispatcher(this.store));

        mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-reference-entity'});
        $(window).on('beforeunload', this.beforeUnload);

        ReactDOM.render(
          <Provider store={this.store}>
            <RecordView />
          </Provider>,
          this.el
        );

        promise.resolve();
      })
      .catch(function(error: any) {
        if (error.request) {
          promise.reject(error.request);
        }

        throw error;
      });

    return promise.promise();
  }

  beforeUnload = () => {
    if (this.isDirty()) {
      return __('pim_enrich.confirmation.discard_changes', {entity: 'record'});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const message = __('pim_enrich.confirmation.discard_changes', {entity: 'record'});

    return this.isDirty() ? confirm(message) : true;
  }

  isDirty() {
    if (undefined === this.store) {
      return false;
    }
    const state = this.store.getState();

    return state.form.state.isDirty;
  }
}

export = RecordEditController;
