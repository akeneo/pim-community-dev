import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit'

interface StateProps {
  enrichedEntity: EnrichedEntity|null;
  context: {
    locale: string;
  };
};

interface DispatchProps {}

const enrichedEntityEditView = ({context, enrichedEntity}: StateProps & DispatchProps) => (
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
                  <div className="AknBreadcrumb">
                    <a href="#" className="AknBreadcrumb-item AknBreadcrumb-item--routable breadcrumb-tab" data-code="pim-menu-entities">
                      {__('pim_enriched_entity.enriched_entity.title')}
                    </a>
                  </div>
                </div>
                <div className="AknTitleContainer-buttonsContainer">
                  <div className="AknTitleContainer-userMenu">
                    <PimView viewName="pim-enriched-entity-index-user-navigation"/>
                  </div>
                </div>
              </div>
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-title">
                  {null !== enrichedEntity ? enrichedEntity.getLabel(context.locale) : ''}
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
        </header>
        <div className="">

        </div>
      </div>
    </div>
  </div>
);

export default connect((state: State): StateProps => {
  const enrichedEntity = undefined === state.enrichedEntity ? null : state.enrichedEntity;
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;

  return {
    enrichedEntity,
    context: {
      locale
    }
  }
}, (): DispatchProps => {
  return {}
})(enrichedEntityEditView);
