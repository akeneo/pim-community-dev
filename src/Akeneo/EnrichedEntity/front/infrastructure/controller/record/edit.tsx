import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoenrichedentity/tools/translator';
import RecordView from 'akeneoenrichedentity/application/component/record/edit';
import Record from 'akeneoenrichedentity/domain/model/record/record';
import createStore from 'akeneoenrichedentity/infrastructure/store';
import recordReducer from 'akeneoenrichedentity/application/reducer/record/edit';
import recordFetcher from 'akeneoenrichedentity/infrastructure/fetcher/record';
import {recordEditionReceived} from 'akeneoenrichedentity/domain/event/record/edit';
import {catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged} from 'akeneoenrichedentity/domain/event/user';
import {setUpSidebar} from 'akeneoenrichedentity/application/action/sidebar';
import {updateActivatedLocales} from 'akeneoenrichedentity/application/action/locale';
import {updateChannels} from 'akeneoenrichedentity/application/action/channel';
import {updateCurrentTab} from 'akeneoenrichedentity/application/event/sidebar';
import {createCode} from 'akeneoenrichedentity/domain/model/record/code';
import {createIdentifier as createEnrichedEntityIdentifier} from 'akeneoenrichedentity/domain/model/enriched-entity/identifier';

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
    recordFetcher
      .fetch(createEnrichedEntityIdentifier(route.params.enrichedEntityIdentifier), createCode(route.params.recordCode))
      .then((record: Record) => {
        this.store = createStore(true)(recordReducer);
        this.store.dispatch(recordEditionReceived(record));
        this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
        this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        this.store.dispatch(setUpSidebar('akeneo_enriched_entities_record_edit') as any);
        this.store.dispatch(updateCurrentTab(route.params.tab));
        this.store.dispatch(updateActivatedLocales() as any);
        this.store.dispatch(updateChannels() as any);
        document.addEventListener('keydown', shortcutDispatcher(this.store));

        mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-enriched-entity'});
        $(window).on('beforeunload', this.beforeUnload);

        ReactDOM.render(
          <Provider store={this.store}>
            <RecordView />
          </Provider>,
          this.el
        );
      });

    return $.Deferred().resolve();
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
