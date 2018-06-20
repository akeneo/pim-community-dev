import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import Table from 'akeneoenrichedentity/application/component/enriched-entity/index/table';
import Breadcrumb from 'akeneoenrichedentity/application/component/app/breadcrumb';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import {redirectToEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/router';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/index'

interface StateProps {
  context: {
    locale: string;
  };

  grid: {
    enrichedEntities: EnrichedEntity[];
    total: number;
    isLoading: boolean;
  };
};

interface DispatchProps {
  events: {
    onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => void
  }
}

const enrichedEntityListView = ({ grid, context, events }: StateProps & DispatchProps) => (
  <div className="AknDefault-contentWithColumn">
    <div className="AknDefault-thirdColumnContainer">
      <div className="AknDefault-thirdColumn"></div>
    </div>
    <div className="AknDefault-contentWithBottom">
      <div className="AknDefault-mainContent">
        <header className="AknTitleContainer">
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-mainContainer">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-breadcrumbs">
                  <Breadcrumb items={[
                    {
                      action: {
                        type: 'redirect',
                        route: 'akeneo_enriched_entities_enriched_entities_edit'
                      },
                      label: __('pim_enriched_entity.enriched_entity.title')
                    }
                  ]}/>
                </div>
                <div className="AknTitleContainer-buttonsContainer">
                  <div className="AknTitleContainer-userMenu">
                    <PimView className="AknTitleContainer-userMenu" viewName="pim-enriched-entity-index-user-navigation"/>
                  </div>
                </div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-title">
                  <span className={grid.isLoading ? 'AknLoadingPlaceHolder' : ''}>
                    {__('pim_enriched_entity.enriched_entity.index.grid.count', {count: grid.enrichedEntities.length})}
                  </span>
                </div>
                <div className="AknTitleContainer-state"></div>
              </div>
            </div>
            <div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-context AknButtonList"></div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-meta AknButtonList"></div>
              </div>
            </div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-navigation"></div>
          </div>
          <div className="AknTitleContainer-line">
            <div className="AknTitleContainer-search"></div>
          </div>
        </header>
        <div className="AknGrid--gallery">
          <div className="AknGridContainer AknGridContainer--withCheckbox">
            <Table
              onRedirectToEnrichedEntity={events.onRedirectToEnrichedEntity}
              locale={context.locale}
              enrichedEntities={grid.enrichedEntities}
            />
          </div>
        </div>
      </div>
    </div>
  </div>
);

export default connect((state: State): StateProps => {
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;
  const enrichedEntities = undefined === state.grid || undefined === state.grid.items ? [] : state.grid.items;
  const total = undefined === state.grid || undefined === state.grid.total ? 0 : state.grid.total;

  return {
    context: {
      locale
    },
    grid: {
      enrichedEntities,
      total,
      isLoading: state.grid.isFetching && state.grid.items.length === 0
    }
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onRedirectToEnrichedEntity: (enrichedEntity: EnrichedEntity) => {
        dispatch(redirectToEnrichedEntity(enrichedEntity));
      }
    }
  }
})(enrichedEntityListView);
