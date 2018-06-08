import * as React from "react";
import { connect } from "react-redux";
import __ from 'akeneoenrichedentity/tools/translator';
import { getImageShowUrl } from 'akeneoenrichedentity/tools/media-url-generator';
import { State } from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import PimView from 'akeneoenrichedentity/infrastructure/component/pim-view';
import Form from 'akeneoenrichedentity/application/component/enriched-entity/edit/form';
import { saveEditForm } from 'akeneoenrichedentity/application/action/enriched-entity/form';

interface PropertiesState {
  enrichedEntity: EnrichedEntity|null;
  context: {
    locale: string;
  };
}

interface PropertiesDispatch {
  events: {
    onSaveEditForm: (enrichedEntity: EnrichedEntity) => void
  }
}

interface PropertiesProps extends PropertiesState, PropertiesDispatch {}

class Properties extends React.Component<PropertiesProps> {
  props: PropertiesProps;
  state: PropertiesState;

  constructor(props: PropertiesProps) {
    super(props);

    this.state = {
      enrichedEntity: this.props.enrichedEntity,
      context: {
        locale: this.props.context.locale
      }
    };
  }

  saveEditForm = () => {
    if (null !== this.state.enrichedEntity) {
      this.props.events.onSaveEditForm(this.state.enrichedEntity);
    }
  };

  updateEditForm = (enrichedEntity: EnrichedEntity) => {
    this.setState({ enrichedEntity });
  };

  render() {
    return(
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
                        <button className="AknButton AknButton--apply save" onClick={this.saveEditForm}>
                          {__('pim_enriched_entity.button.save')}
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
              <div>
                <div className="AknTitleContainer-line">
                  <div className="AknTitleContainer-context AknButtonList"></div>
                </div>
                <div className="AknTitleContainer-line">
                  <div className="AknTitleContainer-meta AknButtonList"></div>
                </div>
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
                      updateEditForm={this.updateEditForm}
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
    );
  }
}

export default connect((state: State): PropertiesState => {
  const enrichedEntity = undefined === state.enrichedEntity ? null : state.enrichedEntity;
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;

  return {
    enrichedEntity,
    context: {
      locale
    },
  }
}, (dispatch: any): PropertiesDispatch => {
  return {
    events: {
      onSaveEditForm: (enrichedEntity: EnrichedEntity) => {
        dispatch(saveEditForm(enrichedEntity))
      }
    }
  }
})(Properties);
