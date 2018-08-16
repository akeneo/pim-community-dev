import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoenrichedentity/domain/event/attribute/create';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {CreateState} from 'akeneoenrichedentity/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoenrichedentity/application/component/attribute/create';
import AttributeModel, {denormalizeAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';

interface StateProps {
  createAttribute: CreateState;
  attributes: AttributeModel[];
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
  };
}
interface CreateProps extends StateProps, DispatchProps {}

const renderAttributes = (attributes: AttributeModel[]) => {
  return attributes.map((attribute: AttributeModel) => (
    <div key={attribute.getCode().stringValue()} className="AknFieldContainer" data-identifier={attribute.getCode().stringValue()} data-type={attribute.getType()}>
      <div className="AknFieldContainer-header">
        <label
          className="AknFieldContainer-label AknFieldContainer-label--withImage"
          htmlFor={`pim_enriched_entity.enriched_entity.properties.${attribute.getCode().stringValue()}`}
        >
          <img
            className="AknFieldContainer-labelImage"
            src={`bundles/pimui/images/attribute/icon-${attribute.type}.svg`}
          />
          <span>
            {__(`pim_enriched_entity.attribute.type.${attribute.type}`)}{' '}
            {attribute.required ? `(${__('pim_enriched_entity.attribute.required')})` : ''}
          </span>
        </label>
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          id={`pim_enriched_entity.enriched_entity.properties.${attribute.getCode().stringValue()}`}
          className="AknTextField AknTextField--withDashedBottomBorder AknTextField--disabled"
          value={attribute.getLabel('en_US')}
          readOnly
        />
      </div>
    </div>
  ));
};

class Attribute extends React.Component<CreateProps> {
  private addButton: HTMLButtonElement;

  componentDidMount() {
    if (this.addButton) {
      this.addButton.focus();
    }
  }

  render() {
    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--blockDown">
          <span className="group-label">{__('pim_enriched_entity.enriched_entity.attribute.title')}</span>
        </header>
        <div className="AknFormContainer AknFormContainer--withPadding">
          {0 !== this.props.attributes.length ? renderAttributes(this.props.attributes) : (<span className="empty-attributes">No attributes</span>)}
          <button
            className="AknButton AknButton--action"
            onClick={this.props.events.onAttributeCreationStart}
            ref={(button: HTMLButtonElement) => {
              this.addButton = button;
            }}
          >
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
      attributes: state.attributes.attributes.map(denormalizeAttribute),
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
