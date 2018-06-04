import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {saveEditForm} from 'akeneoenrichedentity/application/action/enriched-entity/form';
import Form from 'akeneoenrichedentity/application/component/enriched-entity/edit/form';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';

interface EditState {
  enrichedEntity: EnrichedEntity|null;
  context: {
    locale: string;
  };
};

interface EditDispatch {
  events: {
    onSaveEditForm: (enrichedEntity: EnrichedEntity) => void
  }
}

interface EditProps extends EditState, EditDispatch {}

class EnrichedEntityEditView extends React.Component<EditProps> {
  props: EditProps;
  state: EditState;

  constructor(props: EditProps) {
    super(props);

    this.state = {
      enrichedEntity: this.props.enrichedEntity,
      context: {
        locale: this.props.context.locale
      }
    }
  }

  updateEditFormHandler = (enrichedEntity: EnrichedEntity) => {
    this.setState({ enrichedEntity: enrichedEntity });
  };

  saveEditFormHandler = () => {
    if (null !== this.state.enrichedEntity) {
      this.props.events.onSaveEditForm(this.state.enrichedEntity);
    }
  };

  render(): JSX.Element | JSX.Element[] {
    return (
      <div className="AknDefault-contentWithColumn">
        <div className="AknDefault-thirdColumnContainer">
          <div className="AknDefault-thirdColumn"></div>
        </div>
        <div className="AknDefault-contentWithBottom">
          <div className="AknDefault-mainContent">
            <header className="AknTitleContainer navigation">
              <div className="AknTitleContainer-line">
                <div className="AknTitleContainer-imageContainer">
                  <img className="AknTitleContainer-image" src={getImageShowUrl(null, 'thumbnail')} />
                </div>
                <div className="AknTitleContainer-mainContainer">
                  <div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-breadcrumbs">
                        <div className="AknBreadcrumb">
                          <a href="#" className="AknBreadcrumb-item AknBreadcrumb-item--routable breadcrumb-tab" data-code="pim-menu-entities">
                            {__('pim_enriched_entity.enriched_entity.title')}
                          </a>
                        </div>
                      </div>
                      <div className="AknTitleContainer-buttonsContainer">
                        <div className="user-menu">
                          <PimView className="AknTitleContainer-userMenu" viewName="pim-enriched-entity-index-user-navigation"/>
                        </div>
                        <div className="AknButtonList" >
                          <div className="AknTitleContainer-rightButton">
                            <button className="AknButton AknButton--apply save" onClick={this.saveEditFormHandler}>
                              Save
                            </button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div className="AknTitleContainer-line">
                      <div className="AknTitleContainer-title">
                        {null !== this.props.enrichedEntity ? this.props.enrichedEntity.getLabel(this.props.context.locale) : ''}
                      </div>
                    </div>
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
            <div className="content">
              <div>
                <div className="tab-container tab-content">
                  <div className="tabbable object-attributes">
                    <div className="tab-content">
                      <div className="tab-pane active object-values">
                        <Form
                          updateEditForm={this.updateEditFormHandler}
                          locale={this.props.context.locale}
                          enrichedEntity={this.props.enrichedEntity}
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <PimView className="AknColumn" viewName="pim-enriched-entities-edit-form-left-column"/>
      </div>
    );
  }
}


export default connect((state: State): EditState => {
  const enrichedEntity = undefined === state.enrichedEntity ? null : state.enrichedEntity;
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;

  return {
    enrichedEntity,
    context: {
      locale
    }
  }
}, (dispatch: any): EditDispatch => {
  return {
    events: {
      onSaveEditForm: (enrichedEntity: EnrichedEntity) => {
          dispatch(saveEditForm(enrichedEntity))
      }
    }
  }
})(EnrichedEntityEditView);
