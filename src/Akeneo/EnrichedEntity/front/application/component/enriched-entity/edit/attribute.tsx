import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoenrichedentity/domain/event/attribute/create';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {CreateState} from 'akeneoenrichedentity/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoenrichedentity/application/component/attribute/create';
interface StateProps {
  createAttribute: CreateState;
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
  };
}
interface CreateProps extends StateProps, DispatchProps {}

class Attribute extends React.Component<CreateProps> {
  render() {
    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--blockDown">
          <span className="group-label">{__('pim_enriched_entity.enriched_entity.attribute.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
          <button onClick={this.props.events.onAttributeCreationStart}>
            {__('pim_enriched_entity.attribute.button.add')}
          </button>
        </div>
        {this.props.createAttribute.active ? <CreateAttributeModal /> : null}
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      createAttribute: state.createAttribute,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAttributeCreationStart: () => {
          dispatch(attributeCreationStart());
        },
      },
    };
  }
)(Attribute);
