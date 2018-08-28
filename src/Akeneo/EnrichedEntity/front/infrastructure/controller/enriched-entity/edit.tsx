import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntityView from 'akeneoenrichedentity/application/component/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import createStore from 'akeneoenrichedentity/infrastructure/store';
import enrichedEntityReducer from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import {enrichedEntityEditionReceived} from 'akeneoenrichedentity/domain/event/enriched-entity/edit';
import {catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged} from 'akeneoenrichedentity/domain/event/user';
import {setUpSidebar} from 'akeneoenrichedentity/application/action/enriched-entity/sidebar';
import {updateRecordResults} from 'akeneoenrichedentity/application/action/record/search';
import {updateAttributeList} from 'akeneoenrichedentity/application/action/attribute/list';
import {updateActivatedLocales} from 'akeneoenrichedentity/application/action/locale';
const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

const shortcutDispatcher = (store: any) => (event: any) => {
  if ('Escape' === event.code) {
    store.dispatch({type: 'DISMISS'});
  }
};

class EnrichedEntityEditController extends BaseController {
  private store: Store<any>;

  renderRoute(route: any) {
    enrichedEntityFetcher.fetch(route.params.identifier).then((enrichedEntity: EnrichedEntity) => {
      this.store = createStore(true)(enrichedEntityReducer);
      this.store.dispatch(enrichedEntityEditionReceived(enrichedEntity.normalize()));
      this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
      this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
      this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
      this.store.dispatch(setUpSidebar() as any);
      this.store.dispatch(updateRecordResults());
      this.store.dispatch(updateAttributeList() as any);
      this.store.dispatch(updateActivatedLocales() as any);
      document.addEventListener('keydown', shortcutDispatcher(this.store));

      mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-enriched-entity'});
      $(window).on('beforeunload', this.beforeUnload);

      ReactDOM.render(
        <Provider store={this.store}>
          <EnrichedEntityView />
        </Provider>,
        this.el
      );
    });

    return $.Deferred().resolve();
  }

  beforeUnload = () => {
    if (this.isDirty()) {
      return __('pim_enrich.confirmation.discard_changes', {entity: 'enriched entity'});
    }

    document.removeEventListener('keypress', shortcutDispatcher);

    return;
  };

  canLeave() {
    const message = __('pim_enrich.confirmation.discard_changes', {entity: 'enriched entity'});

    return this.isDirty() ? confirm(message) : true;
  }

  isDirty() {
    const state = this.store.getState();

    return state.form.state.isDirty || state.attribute.isDirty;
  }
}

export = EnrichedEntityEditController;
