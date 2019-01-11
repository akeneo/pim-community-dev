import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoreferenceentity/tools/translator';
import RecordView from 'akeneoreferenceentity/application/component/record/edit';
import createStore from 'akeneoreferenceentity/infrastructure/store';
import recordReducer from 'akeneoreferenceentity/application/reducer/record/edit';
import recordFetcher, {RecordResult} from 'akeneoreferenceentity/infrastructure/fetcher/record';
import {recordEditionReceived} from 'akeneoreferenceentity/domain/event/record/edit';
import {
  defaultCatalogLocaleChanged,
  catalogLocaleChanged,
  catalogChannelChanged,
  uiLocaleChanged,
  localePermissionsChanged,
  referenceEntityPermissionChanged,
} from 'akeneoreferenceentity/domain/event/user';
import {setUpSidebar} from 'akeneoreferenceentity/application/action/sidebar';
import {updateActivatedLocales} from 'akeneoreferenceentity/application/action/locale';
import {updateChannels} from 'akeneoreferenceentity/application/action/channel';
import {updateCurrentTab} from 'akeneoreferenceentity/application/event/sidebar';
import {createCode} from 'akeneoreferenceentity/domain/model/record/code';
import {createIdentifier as createReferenceEntityIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {LocalePermission} from 'akeneoreferenceentity/domain/model/permission/locale';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');
const fetcherRegistry = require('pim/fetcher-registry');

const shortcutDispatcher = (store: any) => (event: KeyboardEvent) => {
  if ('Escape' === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class RecordEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    const promise = $.Deferred();

    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-reference-entity'});
    $(window).on('beforeunload', this.beforeUnload);

    recordFetcher
      .fetch(
        createReferenceEntityIdentifier(route.params.referenceEntityIdentifier),
        createCode(route.params.recordCode)
      )
      .then((recordResult: RecordResult) => {
        this.store = createStore(true)(recordReducer);
        this.store.dispatch(updateChannels() as any);
        this.store = createStore(true)(recordReducer);
        this.store.dispatch(recordEditionReceived(recordResult.record));
        this.store.dispatch(referenceEntityPermissionChanged(recordResult.permission));
        this.store.dispatch(defaultCatalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')) as any);
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(setUpSidebar('akeneo_reference_entities_record_edit') as any);
        this.store.dispatch(updateCurrentTab(route.params.tab));
        this.store.dispatch(updateActivatedLocales() as any);
        document.addEventListener('keydown', shortcutDispatcher(this.store));

        fetcherRegistry
          .getFetcher('locale-permission')
          .fetchAll()
          .then((localePermissions: LocalePermission[]) => {
            this.store.dispatch(localePermissionsChanged(localePermissions));
          });

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
