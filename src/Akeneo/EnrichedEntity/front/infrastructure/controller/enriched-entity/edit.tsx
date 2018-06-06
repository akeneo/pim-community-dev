import * as $ from 'jquery';
import * as ReactDOM from 'react-dom';
import { Provider } from 'react-redux';
import * as React from 'react';
import EnrichedEntityView from 'akeneoenrichedentity/application/component/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import createStore from 'akeneoenrichedentity/infrastructure/store';
import enrichedEntityReducer from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import enrichedEntityFetcher from 'akeneoenrichedentity/infrastructure/fetcher/enriched-entity';
import { enrichedEntityReceived } from 'akeneoenrichedentity/domain/event/show.ts';
import { catalogLocaleChanged, catalogChannelChanged, uiLocaleChanged } from 'akeneoenrichedentity/domain/event/user';
import { setUpSidebar } from "akeneoenrichedentity/application/action/enriched-entity/sidebar";

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const userContext = require('pim/user-context');

class EnrichedEntityEditController extends BaseController {
  renderRoute(route: any) {
    enrichedEntityFetcher.fetch(route.params.identifier)
      .then((enrichedEntity: EnrichedEntity) => {
        const store = createStore(true)(enrichedEntityReducer);
        store.dispatch(enrichedEntityReceived(enrichedEntity));
        store.dispatch(catalogLocaleChanged(userContext.get('catalogLocale')));
        store.dispatch(catalogChannelChanged(userContext.get('catalogScope')));
        store.dispatch(uiLocaleChanged(userContext.get('uiLocale')));
        store.dispatch(setUpSidebar());

        mediator.trigger('pim_menu:highlight:tab', { extension: 'pim-menu-enriched-entity' });

        ReactDOM.render(
          (<Provider store={store}>
            <EnrichedEntityView/>
          </Provider>),
          this.el
        );
      });

    return $.Deferred().resolve();
  }
}

export = EnrichedEntityEditController;
