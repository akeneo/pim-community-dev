import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoenrichedentity/domain/event/attribute/create';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import {CreateState} from 'akeneoenrichedentity/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoenrichedentity/application/component/attribute/create';
import {denormalizeAttribute, NormalizedAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import AttributeIdentifier from 'akeneoenrichedentity/domain/model/attribute/identifier';
import EnrichedEntity, {
  denormalizeEnrichedEntity,
} from 'akeneoenrichedentity/domain/model/enriched-entity/enriched-entity';
import {attributeEditionStartByIdentifier} from 'akeneoenrichedentity/application/action/attribute/edit';
import AttributeEditForm from 'akeneoenrichedentity/application/component/attribute/edit';
import Header from 'akeneoenrichedentity/application/component/enriched-entity/edit/header';
import {
  SecondaryAction,
  breadcrumbConfiguration,
} from 'akeneoenrichedentity/application/component/enriched-entity/edit';
import {deleteEnrichedEntity} from 'akeneoenrichedentity/application/action/enriched-entity/edit';
const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };
  acls: {
    createAttribute: boolean;
    delete: boolean;
  };
  enrichedEntity: EnrichedEntity;
  createAttribute: CreateState;
  attributes: NormalizedAttribute[];
  firstLoading: boolean;
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
    onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
    onDelete: (enrichedEntity: EnrichedEntity) => void;
  };
}
interface CreateProps extends StateProps, DispatchProps {}

const renderSystemAttribute = (type: string, identifier: string) => {
  return (
    <div
      className="AknFieldContainer"
      data-placeholder="false"
      data-identifier={`system_record_${identifier}`}
      data-type={type}
    >
      <div className="AknFieldContainer-header AknFieldContainer-header--light">
        <label
          className="AknFieldContainer-label AknFieldContainer-label--withImage"
          htmlFor={`pim_enriched_entity.enriched_entity.properties.system_record_${identifier}`}
        >
          <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-${type}.svg`} />
          <span>{__(`pim_enriched_entity.attribute.type.${type}`)}</span>
        </label>
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          id={`pim_enriched_entity.enriched_entity.properties.system_record_${identifier}`}
          className="AknTextField AknTextField--light AknTextField--disabled"
          value={__(`pim_enriched_entity.attribute.default.${identifier}`)}
          readOnly
        />
      </div>
    </div>
  );
};

const renderSystemAttributes = () => {
  return (
    <React.Fragment>
      {renderSystemAttribute('text', 'code')}
      {renderSystemAttribute('text', 'label')}
      {renderSystemAttribute('image', 'image')}
    </React.Fragment>
  );
};

const renderAttributesPlaceholder = () => {
  return Array(8)
    .fill('placeholder')
    .map((attributeIdentifier, key) => (
      <div key={key} className="AknFieldContainer" data-placeholder="true">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
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
    const {onAttributeEdit, locale} = this.props;
    const attribute = denormalizeAttribute(this.props.attribute);

    return (
      <div
        className="AknFieldContainer"
        data-placeholder="false"
        data-identifier={attribute.getCode().stringValue()}
        data-type={attribute.getType()}
      >
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
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
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={attribute.getLabel(locale)}
            readOnly
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
      <React.Fragment>
        <Header
          label={this.props.enrichedEntity.getLabel(this.props.context.locale)}
          image={this.props.enrichedEntity.getImage()}
          primaryAction={() => {
            return this.props.acls.createAttribute ? (
              <button className="AknButton AknButton--action" onClick={this.props.events.onAttributeCreationStart}>
                {__('pim_enriched_entity.attribute.button.add')}
              </button>
            ) : null;
          }}
          secondaryActions={() => {
            return this.props.acls.delete ? (
              <SecondaryAction
                onDelete={() => {
                  this.props.events.onDelete(this.props.enrichedEntity);
                }}
              />
            ) : null;
          }}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title AknSubsection-title--sticky" style={{top: '192px'}}>
            <span className="group-label">{__('pim_enriched_entity.enriched_entity.attribute.title')}</span>
          </header>
          {this.props.firstLoading || 0 < this.props.attributes.length ? (
            <div className="AknSubsection-container">
              <div className="AknFormContainer AknFormContainer--withPadding">
                {renderSystemAttributes()}
                {this.props.firstLoading ? (
                  renderAttributesPlaceholder()
                ) : (
                  <React.Fragment>
                    {this.props.attributes.map((attribute: NormalizedAttribute) => (
                      <AttributeView
                        key={attribute.identifier}
                        attribute={attribute}
                        onAttributeEdit={this.props.events.onAttributeEdit}
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
              <AttributeEditForm />
            </div>
          ) : (
            <React.Fragment>
              <div className="AknSubsection-container">
                <div className="AknFormContainer AknFormContainer--withPadding">{renderSystemAttributes()}</div>
              </div>
              <div className="AknGridContainer-noData AknGridContainer-noData--small">
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
            </React.Fragment>
          )}
          {this.props.createAttribute.active ? <CreateAttributeModal /> : null}
        </div>
      </React.Fragment>
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
      acls: {
        createAttribute: true,
        delete: securityContext.isGranted('akeneo_enrichedentity_enriched_entity_delete'),
      },
      enrichedEntity,
      createAttribute: state.createAttribute,
      firstLoading: null === state.attributes.attributes,
      attributes: null !== state.attributes.attributes ? state.attributes.attributes : [],
    };
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onAttributeCreationStart: () => {
          dispatch(attributeCreationStart());
        },
        onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => {
          dispatch(attributeEditionStartByIdentifier(attributeIdentifier));
        },
        onDelete: (enrichedEntity: EnrichedEntity) => {
          dispatch(deleteEnrichedEntity(enrichedEntity));
        },
      },
    };
  }
)(AttributesView);
