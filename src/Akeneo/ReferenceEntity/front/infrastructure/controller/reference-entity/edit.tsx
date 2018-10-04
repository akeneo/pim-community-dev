import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import {Provider} from 'react-redux';
import * as React from 'react';
import {Store} from 'redux';
import __ from 'akeneoreferenceentity/tools/translator';
import ReferenceEntityView from 'akeneoreferenceentity/application/component/reference-entity/edit';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import createStore from 'akeneoreferenceentity/infrastructure/store';
import referenceEntityReducer from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import {referenceEntityEditionReceived} from 'akeneoreferenceentity/domain/event/reference-entity/edit';
import {catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged} from 'akeneoreferenceentity/domain/event/user';
import {setUpSidebar} from 'akeneoreferenceentity/application/action/sidebar';
import {updateRecordResults} from 'akeneoreferenceentity/application/action/record/search';
import {updateAttributeList} from 'akeneoreferenceentity/application/action/attribute/list';
import {updateActivatedLocales} from 'akeneoreferenceentity/application/action/locale';
import {updateCurrentTab} from 'akeneoreferenceentity/application/event/sidebar';
import {createIdentifier} from 'akeneoreferenceentity/domain/model/reference-entity/identifier';
import {updateChannels} from 'akeneoreferenceentity/application/action/channel';
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
    referenceEntityFetcher.fetch(createIdentifier(route.params.identifier)).then((referenceEntity: ReferenceEntity) => {
      this.store = createStore(true)(referenceEntityReducer);
      this.store.dispatch(referenceEntityEditionReceived(referenceEntity.normalize()));
      this.store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
      this.store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
      this.store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
      this.store.dispatch(setUpSidebar('akeneo_reference_entities_reference_entity_edit') as any);
      this.store.dispatch(updateCurrentTab(route.params.tab));
      this.store.dispatch(updateRecordResults());
      this.store.dispatch(updateAttributeList() as any);
      this.store.dispatch(updateActivatedLocales() as any);
      this.store.dispatch(updateChannels() as any);
      document.addEventListener('keydown', shortcutDispatcher(this.store));

      mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-reference-entity'});
      $(window).on('beforeunload', this.beforeUnload);

      ReactDOM.render(
        <Provider store={this.store}>
          <ReferenceEntityView />
        </Provider>,
        this.el
      );
    });

    return $.Deferred().resolve();
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

    return state.form.state.isDirty || state.attribute.isDirty;
  }
}

export = ReferenceEntityEditController;
