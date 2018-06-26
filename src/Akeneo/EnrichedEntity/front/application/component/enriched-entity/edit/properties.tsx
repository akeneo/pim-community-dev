import * as React from 'react';
import {connect} from 'react-redux';
import {State} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import EnrichedEntity from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import Form from 'akeneoenrichedentity/application/component/enriched-entity/edit/form';
import {updateEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/edit';
import __ from 'akeneoenrichedentity/tools/translator';

interface StateProps {
  enrichedEntity: EnrichedEntity|null;
  context: {
    locale: string;
  };
}

interface DispatchProps {
  events: {
    onEnrichedEntityUpdated: (enrichedEntity: EnrichedEntity) => void
  }
}

interface PropertiesProps extends StateProps, DispatchProps {
  code: string;
}

class Properties extends React.Component<PropertiesProps> {
  props: PropertiesProps;

  updateEditForm = (enrichedEntity: EnrichedEntity) => {
    this.props.events.onEnrichedEntityUpdated(enrichedEntity);
  };

  render() {
    return(
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--blockDown">
            <span className="group-label">{__('pim_enriched_entity.enriched_entity.properties.title')}</span>
        </header>
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
    );
  }
}

export default connect((state: State): StateProps => {
  const enrichedEntity = undefined === state.enrichedEntity ? null : state.enrichedEntity;
  const locale = undefined === state.user || undefined === state.user.uiLocale ? '' : state.user.uiLocale;

  return {
    enrichedEntity,
    context: {
      locale
    },
  }
}, (dispatch: any): DispatchProps => {
  return {
    events: {
      onEnrichedEntityUpdated: (enrichedEntity: EnrichedEntity) => {
        dispatch(updateEnrichedEntity(enrichedEntity));
      }
    }
  }
})(Properties);
