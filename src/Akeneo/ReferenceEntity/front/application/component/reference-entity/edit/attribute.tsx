import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import {connect} from 'react-redux';
import {attributeCreationStart} from 'akeneoreferenceentity/domain/event/attribute/create';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {CreateState} from 'akeneoreferenceentity/application/reducer/attribute/create';
import CreateAttributeModal from 'akeneoreferenceentity/application/component/attribute/create';
import ManageOptionsView from 'akeneoreferenceentity/application/component/attribute/edit/option';
import AttributeIdentifier from 'akeneoreferenceentity/domain/model/attribute/identifier';
import ReferenceEntity, {
  denormalizeReferenceEntity,
} from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {attributeEditionStartByIdentifier} from 'akeneoreferenceentity/application/action/attribute/edit';
import AttributeEditForm from 'akeneoreferenceentity/application/component/attribute/edit';
import Header from 'akeneoreferenceentity/application/component/reference-entity/edit/header';
import {breadcrumbConfiguration} from 'akeneoreferenceentity/application/component/reference-entity/edit';
import denormalizeAttribute from 'akeneoreferenceentity/application/denormalizer/attribute/attribute';
import {NormalizedAttribute} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {getAttributeIcon} from 'akeneoreferenceentity/application/configuration/attribute';
import Key from 'akeneoreferenceentity/tools/key';
import ErrorBoundary from 'akeneoreferenceentity/application/component/app/error-boundary';
import {EditOptionState} from 'akeneoreferenceentity/application/reducer/attribute/type/option';

const securityContext = require('pim/security-context');

interface StateProps {
  context: {
    locale: string;
  };
  acls: {
    createAttribute: boolean;
    delete: boolean;
  };
  referenceEntity: ReferenceEntity;
  createAttribute: CreateState;
  options: EditOptionState;
  attributes: NormalizedAttribute[];
  firstLoading: boolean;
}
interface DispatchProps {
  events: {
    onAttributeCreationStart: () => void;
    onAttributeEdit: (attributeIdentifier: AttributeIdentifier) => void;
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
          htmlFor={`pim_reference_entity.reference_entity.properties.system_record_${identifier}`}
        >
          <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-${type}.svg`} />
          <span>{identifier}</span>
        </label>
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          tabIndex={-1}
          id={`pim_reference_entity.reference_entity.properties.system_record_${identifier}`}
          className="AknTextField AknTextField--light AknTextField--disabled"
          value={__(`pim_reference_entity.attribute.default.${identifier}`)}
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

const renderAttributePlaceholders = () => {
  return Array(8)
    .fill('placeholder')
    .map((attributeIdentifier, key) => (
      <div key={key} className="AknFieldContainer" data-placeholder="true">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--withImage AknLoadingPlaceHolder"
            htmlFor={`pim_reference_entity.reference_entity.properties.${attributeIdentifier}_${key}`}
          >
            <img className="AknFieldContainer-labelImage" src={`bundles/pimui/images/attribute/icon-text.svg`} />
            <span>
              {__(`pim_reference_entity.attribute.type.text`)} {`(${__('pim_reference_entity.attribute.is_required')})`}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer AknLoadingPlaceHolder">
          <input
            type="text"
            id={`pim_reference_entity.reference_entity.properties.${attributeIdentifier}_${key}`}
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
    const icon = getAttributeIcon(attribute.getType());

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
            htmlFor={`pim_reference_entity.reference_entity.properties.${attribute.getCode().stringValue()}`}
          >
            <img className="AknFieldContainer-labelImage" src={icon} />
            <span>
              {attribute.getCode().stringValue()}{' '}
              {attribute.isRequired ? `(${__('pim_reference_entity.attribute.is_required')})` : ''}
            </span>
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            id={`pim_reference_entity.reference_entity.properties.${attribute.getCode().stringValue()}`}
            className="AknTextField AknTextField--light AknTextField--disabled"
            value={attribute.getLabel(locale)}
            readOnly
            tabIndex={-1}
          />
          <button
            className="AknIconButton AknIconButton--edit"
            onClick={() => onAttributeEdit(attribute.getIdentifier())}
            onKeyPress={(event: React.KeyboardEvent<HTMLButtonElement>) => {
              if (Key.Space === event.key) onAttributeEdit(attribute.getIdentifier());
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
          label={this.props.referenceEntity.getLabel(this.props.context.locale)}
          image={this.props.referenceEntity.getImage()}
          primaryAction={() => {
            return this.props.acls.createAttribute ? (
              <button className="AknButton AknButton--action" onClick={this.props.events.onAttributeCreationStart}>
                {__('pim_reference_entity.attribute.button.add')}
              </button>
            ) : null;
          }}
          secondaryActions={() => null}
          withLocaleSwitcher={true}
          withChannelSwitcher={false}
          isDirty={false}
          breadcrumbConfiguration={breadcrumbConfiguration}
        />
        <div className="AknSubsection">
          <header className="AknSubsection-title AknSubsection-title--sticky" style={{top: '192px'}}>
            <span className="group-label">{__('pim_reference_entity.reference_entity.attribute.title')}</span>
          </header>
          {this.props.firstLoading || 0 < this.props.attributes.length ? (
            <div className="AknSubsection-container">
              <div className="AknFormContainer AknFormContainer--withPadding">
                {renderSystemAttributes()}
                {this.props.firstLoading ? (
                  renderAttributePlaceholders()
                ) : (
                  <React.Fragment>
                    {this.props.attributes.map((attribute: NormalizedAttribute) => (
                      <ErrorBoundary
                        key={attribute.identifier}
                        errorMessage={__('pim_reference_entity.reference_entity.attribute.error.render_list')}
                      >
                        <AttributeView
                          attribute={attribute}
                          onAttributeEdit={this.props.events.onAttributeEdit}
                          locale={this.props.context.locale}
                        />
                      </ErrorBoundary>
                    ))}
                    <button
                      className="AknButton AknButton--action"
                      onClick={this.props.events.onAttributeCreationStart}
                      ref={(button: HTMLButtonElement) => {
                        this.addButton = button;
                      }}
                    >
                      {__('pim_reference_entity.attribute.button.add')}
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
                  {__('pim_reference_entity.attribute.no_data.title', {
                    entityLabel: this.props.referenceEntity.getLabel(this.props.context.locale),
                  })}
                </div>
                <div className="AknGridContainer-noDataSubtitle">
                  {__('pim_reference_entity.attribute.no_data.subtitle')}
                </div>
                <button
                  className="AknButton AknButton--action"
                  onClick={this.props.events.onAttributeCreationStart}
                  ref={(button: HTMLButtonElement) => {
                    this.addButton = button;
                  }}
                >
                  {__('pim_reference_entity.attribute.button.add')}
                </button>
              </div>
            </React.Fragment>
          )}
          {this.props.createAttribute.active ? <CreateAttributeModal /> : null}
          {this.props.options.isActive ? <ManageOptionsView /> : null}
        </div>
      </React.Fragment>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const referenceEntity = denormalizeReferenceEntity(state.form.data);
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      context: {
        locale,
      },
      acls: {
        createAttribute: true,
        delete: securityContext.isGranted('akeneo_referenceentity_reference_entity_delete'),
      },
      referenceEntity,
      createAttribute: state.createAttribute,
      options: state.options,
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
      },
    };
  }
)(AttributesView);
