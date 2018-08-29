import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoenrichedentity/domain/event/attribute/create';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {CreateState} from 'akeneoenrichedentity/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoenrichedentity/application/component/attribute/create';
import AttributeModel, {
  denormalizeAttribute,
  NormalizedAttribute,
} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {deleteAttribute} from 'akeneoenrichedentity/application/action/attribute/list';
import {attributeEditionStart} from 'akeneoenrichedentity/application/action/attribute/edit';
import AttributeEditForm from 'akeneoenrichedentity/application/component/attribute/edit';

interface StateProps {
  context: {
    locale: string;
  };
  enrichedEntity: EnrichedEntity;
  createAttribute: CreateState;
  attributes: NormalizedAttribute[];
  firstLoading: boolean;
  editedAttribute: AttributeModel | null;
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
    onAttributeDelete: (attribute: AttributeModel) => void;
    onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
  };
}
interface CreateProps extends StateProps, DispatchProps {}

const renderAttributesPlaceholder = () => {
  return Array(8)
    .fill('placeholder')
    .map((attributeIdentifier, key) => (
      <div key={key} className="AknFieldContainer" data-placeholder="true">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--withImage AknLoadingPlaceHolder"
            htmlFor={`pim_enriched_entity.enriched_entity.properties.${attributeIdentifier}_${key}`}
          >
            <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-text.svg`} />
            <span>
              {__(`pim_enriched_entity.attribute.type.text`)} {`(${__('pim_enriched_entity.attribute.is_required')})`}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer AknLoadingPlaceHolder">
          <input
            type="text"
            id={`pim_enriched_entity.enriched_entity.properties.${attributeIdentifier}_${key}`}
            className="AknTextField AknTextField--transparent"
          />
          <button className="AknIconButton AknIconButton--trash" />
          <button className="AknIconButton AknIconButton--edit" />
        </div>
      </div>
    ));
};

interface AttributeViewProps {
  attribute: NormalizedAttribute;
  onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
  onAttributeDelete: (attribute: AttributeModel) => void;
  locale: string;
}

class AttributeView extends React.Component<AttributeViewProps> {
  public shouldComponentUpdate(nextProps: AttributeViewProps) {
    return (
      nextProps.attribute.labels[nextProps.locale] !== this.props.attribute.labels[this.props.locale] ||
      nextProps.attribute.is_required !== this.props.attribute.is_required
    );
  }

  render() {
    const {onAttributeDelete, onAttributeEdit, locale} = this.props;
    const attribute = denormalizeAttribute(this.props.attribute);

    return (
      <div
        className="AknFieldContainer"
        data-placeholder="false"
        data-identifier={attribute.getCode().stringValue()}
        data-type={attribute.getType()}
      >
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
              {attribute.isRequired ? `(${__('pim_enriched_entity.attribute.is_required')})` : ''}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            id={`pim_enriched_entity.enriched_entity.properties.${attribute.getCode().stringValue()}`}
            className="AknTextField AknTextField--withDashedBottomBorder AknTextField--disabled"
            value={attribute.getLabel(locale)}
            readOnly
          />
          <button
            className="AknIconButton AknIconButton--trash"
            onClick={() => {
              const message = __('pim_enriched_entity.attribute.delete.confirm');
              if (confirm(message)) {
                onAttributeDelete(attribute);
              }
            }}
          />
          <button
            className="AknIconButton AknIconButton--edit"
            onClick={() => onAttributeEdit(attribute.getIdentifier())}
            onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
              if (' ' === event.key) onAttributeEdit(attribute.getIdentifier());
            }}
          />
        </div>
      </div>
    );
  }
}

class AttributesView extends React.Component<CreateProps> {
  private addButton: HTMLButtonElement;

  componentDidMount() {
    if (this.addButton) {
      this.addButton.focus();
    }
  }

  render() {
    return (
      <div className="AknSubsection">
        <header className="AknSubsection-title AknSubsection-title--sticky" style={{top: '192px'}}>
          <span className="group-label">{__('pim_enriched_entity.enriched_entity.attribute.title')}</span>
        </header>
        {this.props.firstLoading || 0 < this.props.attributes.length ? (
          <div className="AknSubsection-container">
            <div className="AknFormContainer AknFormContainer--withPadding">
              {this.props.firstLoading ? (
                renderAttributesPlaceholder()
              ) : (
                <React.Fragment>
                  {this.props.attributes.map((attribute: NormalizedAttribute) => (
                    <AttributeView
                      key={attribute.identifier.identifier}
                      attribute={attribute}
                      onAttributeEdit={this.props.events.onAttributeEdit}
                      onAttributeDelete={this.props.events.onAttributeDelete}
                      locale={this.props.context.locale}
                    />
                  ))}
                  <button
                    className="AknButton AknButton--action"
                    onClick={this.props.events.onAttributeCreationStart}
                    ref={(button: HTMLButtonElement) => {
                      this.addButton = button;
                    }}
                  >
                    {__('pim_enriched_entity.attribute.button.add')}
                  </button>
                </React.Fragment>
              )}
            </div>
            {null !== this.props.editedAttribute ? <AttributeEditForm /> : null}
          </div>
        ) : (
          <div className="AknGridContainer-noData">
            <div className="AknGridContainer-noDataTitle">
              {__('pim_enriched_entity.attribute.no_data.title', {
                entityLabel: this.props.enrichedEntity.getLabel(this.props.context.locale),
              })}
            </div>
            <div className="AknGridContainer-noDataSubtitle">
              {__('pim_enriched_entity.attribute.no_data.subtitle')}
            </div>
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
        )}
        {this.props.createAttribute.active ? <CreateAttributeModal /> : null}
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const enrichedEntity = denormalizeEnrichedEntity(state.form.data);
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      context: {
        locale,
      },
      enrichedEntity,
      createAttribute: state.createAttribute,
      firstLoading: null === state.attributes.attributes,
      attributes: null !== state.attributes.attributes ? state.attributes.attributes : [],
      editedAttribute: state.attribute.active ? denormalizeAttribute(state.attribute.data) : null,
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAttributeCreationStart: () => {
          dispatch(attributeCreationStart());
        },
        onAttributeDelete: (attribute: AttributeModel) => {
          dispatch(deleteAttribute(attribute));
        },
        onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => {
          dispatch(attributeEditionStart(attributeIdentifier));
        },
      },
    };
  }
)(AttributesView);
