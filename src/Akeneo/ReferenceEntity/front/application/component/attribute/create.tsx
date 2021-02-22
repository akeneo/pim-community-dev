import React from 'react';
import {connect} from 'react-redux';
import {Checkbox, Key} from 'akeneo-design-system';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import Flag from 'akeneoreferenceentity/tools/component/flag';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import {EditState} from 'akeneoreferenceentity/application/reducer/reference-entity/edit';
import {
  attributeCreationCodeUpdated,
  attributeCreationLabelUpdated,
  attributeCreationCancel,
  attributeCreationTypeUpdated,
  attributeCreationValuePerLocaleUpdated,
  attributeCreationValuePerChannelUpdated,
  attributeCreationRecordTypeUpdated,
} from 'akeneoreferenceentity/domain/event/attribute/create';
import {createAttribute} from 'akeneoreferenceentity/application/action/attribute/create';
import Dropdown, {DropdownElement} from 'akeneoreferenceentity/application/component/app/dropdown';
import {createLocaleFromCode} from 'akeneoreferenceentity/domain/model/locale';
import {getAttributeTypes, AttributeType} from 'akeneoreferenceentity/application/configuration/attribute';
import referenceEntityFetcher from 'akeneoreferenceentity/infrastructure/fetcher/reference-entity';
import ReferenceEntity from 'akeneoreferenceentity/domain/model/reference-entity/reference-entity';
import {getImageShowUrl} from 'akeneoreferenceentity/tools/media-url-generator';
import {isRecordAttributeType} from 'akeneoreferenceentity/domain/model/attribute/minimal';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: string;
    value_per_locale: boolean;
    value_per_channel: boolean;
    record_type: string;
  };
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onTypeUpdated: (type: string) => void;
    onRecordTypeUpdated: (recordType: string) => void;
    onValuePerLocaleUpdated: (valuePerLocale: boolean) => void;
    onValuePerChannelUpdated: (valuePerChannel: boolean) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

const AttributeTypeItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}) => {
  const className = `AknDropdown-menuLink AknDropdown-menuLink--withImage ${
    isActive ? 'AknDropdown-menuLink--active' : ''
  }`;

  return (
    <div
      className={className}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick(element);
      }}
      tabIndex={isOpen ? 0 : -1}
    >
      <img className="AknDropdown-menuLinkImage" src={element.original.icon} />
      <span>{element.label}</span>
    </div>
  );
};

const RecordTypeItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}) => {
  const className = `AknDropdown-menuLink AknDropdown-menuLink--withImage ${
    isActive ? 'AknDropdown-menuLink--active' : ''
  }`;

  return (
    <div
      className={className}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      onKeyPress={event => {
        if (Key.Space === event.key) onClick(element);
      }}
      tabIndex={isOpen ? 0 : -1}
    >
      <img className="AknDropdown-menuLinkImage" src={getImageShowUrl(element.original.getImage(), 'thumbnail')} />
      <span>{element.label}</span>
    </div>
  );
};

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;
  state: {referenceEntities: ReferenceEntity[]} = {referenceEntities: []};

  async componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }

    const referenceEntities = await referenceEntityFetcher.fetchAll();
    this.setState({referenceEntities: referenceEntities});
  }

  private onCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onTypeUpdate = (value: DropdownElement) => {
    this.props.events.onTypeUpdated(value.identifier);
  };

  private onRecordTypeUpdate = (value: DropdownElement) => {
    this.props.events.onRecordTypeUpdated(value.identifier);
  };

  private onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (Key.Enter === event.key) this.props.events.onSubmit();
  };

  private getTypeOptions = (): DropdownElement[] => {
    return getAttributeTypes().map((type: AttributeType) => {
      return {
        identifier: type.identifier,
        label: __(type.label),
        original: type,
      };
    });
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in" aria-hidden="false" style={{zIndex: 1041}}>
        <div>
          <div className="AknFullPage">
            <div className="AknFullPage-content AknFullPage-content--withIllustration" style={{overflowX: 'visible'}}>
              <div>
                <img src="bundles/pimui/images/illustrations/Reference-entities.svg" className="AknFullPage-image" />
              </div>
              <div>
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">{__('pim_reference_entity.attribute.create.subtitle')}</div>
                  <div className="AknFullPage-title">{__('pim_reference_entity.attribute.create.title')}</div>
                  <div className="AknFullPage-description">
                    {__('pim_reference_entity.attribute.create.description')}
                  </div>
                </div>
                <div className="AknFormContainer">
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.attribute.create.input.label"
                      >
                        {__('pim_reference_entity.attribute.create.input.label')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        autoComplete="off"
                        ref={(input: HTMLInputElement) => {
                          this.labelInput = input;
                        }}
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.attribute.create.input.label"
                        name="label"
                        value={this.props.data.labels[this.props.context.locale] || ''}
                        onChange={this.onLabelUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                      <Flag
                        locale={createLocaleFromCode(this.props.context.locale)}
                        displayLanguage={false}
                        className="AknFieldContainer-inputSides"
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'labels')}
                  </div>
                  <div className="AknFieldContainer" data-code="code">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.attribute.create.input.code"
                      >
                        {__('pim_reference_entity.attribute.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        autoComplete="off"
                        className="AknTextField AknTextField--light"
                        id="pim_reference_entity.attribute.create.input.code"
                        name="code"
                        value={this.props.data.code}
                        onChange={this.onCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'code')}
                  </div>
                  <div className="AknFieldContainer" style={{position: 'static'}} data-code="type">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_reference_entity.attribute.create.input.type"
                      >
                        {__('pim_reference_entity.attribute.create.input.type')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <Dropdown
                        ItemView={AttributeTypeItemView}
                        label={__('pim_reference_entity.attribute.create.input.type')}
                        elements={this.getTypeOptions()}
                        selectedElement={this.props.data.type}
                        onSelectionChange={this.onTypeUpdate}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'type')}
                  </div>
                  {isRecordAttributeType(this.props.data.type) ? (
                    <div className="AknFieldContainer" style={{position: 'static'}} data-code="record_type">
                      <div className="AknFieldContainer-header AknFieldContainer-header--light">
                        <label
                          className="AknFieldContainer-label"
                          htmlFor="pim_reference_entity.attribute.create.input.record_type"
                        >
                          {__('pim_reference_entity.attribute.create.input.record_type')}
                        </label>
                      </div>
                      <div className="AknFieldContainer-inputContainer">
                        <Dropdown
                          ItemView={RecordTypeItemView}
                          label={__('pim_reference_entity.attribute.create.input.record_type')}
                          elements={this.state.referenceEntities.map((referenceEntity: ReferenceEntity) => ({
                            identifier: referenceEntity.getIdentifier().stringValue(),
                            label: referenceEntity.getLabel(this.props.context.locale),
                            original: referenceEntity,
                          }))}
                          selectedElement={this.props.data.record_type}
                          onSelectionChange={this.onRecordTypeUpdate}
                          allowEmpty={true}
                          placeholder={__('pim_reference_entity.attribute.create.placeholder.record_type')}
                        />
                      </div>
                      {getErrorsView(this.props.errors, 'recordType')}
                    </div>
                  ) : null}
                  <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerChannel">
                    <Checkbox
                      id="pim_reference_entity.attribute.create.input.value_per_channel"
                      checked={this.props.data.value_per_channel}
                      onChange={this.props.events.onValuePerChannelUpdated}
                    >
                      {__('pim_reference_entity.attribute.create.input.value_per_channel')}
                    </Checkbox>
                    {getErrorsView(this.props.errors, 'valuePerChannel')}
                  </div>
                  <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerLocale">
                    <Checkbox
                      id="pim_reference_entity.attribute.create.input.value_per_locale"
                      checked={this.props.data.value_per_locale}
                      onChange={this.props.events.onValuePerLocaleUpdated}
                    >
                      {__('pim_reference_entity.attribute.create.input.value_per_locale')}
                    </Checkbox>
                    {getErrorsView(this.props.errors, 'valuePerLocale')}
                  </div>
                  <button
                    className="AknButton AknButton--apply ok"
                    style={{position: 'static'}}
                    onClick={this.props.events.onSubmit}
                  >
                    {__('pim_reference_entity.attribute.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title={__('pim_reference_entity.attribute.create.cancel')}
          className="AknFullPage-cancel cancel"
          onClick={this.props.events.onCancel}
          tabIndex={0}
          onKeyPress={event => {
            if (Key.Space === event.key) this.props.events.onCancel();
          }}
        />
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    return {
      data: state.createAttribute.data,
      errors: state.createAttribute.errors,
      context: {
        locale: state.user.catalogLocale,
      },
    } as StateProps;
  },
  (dispatch: any): DispatchProps => {
    return {
      events: {
        onLabelUpdated: (value: string, locale: string) => {
          dispatch(attributeCreationLabelUpdated(value, locale));
        },
        onCodeUpdated: (value: string) => {
          dispatch(attributeCreationCodeUpdated(value));
        },
        onTypeUpdated: (value: string) => {
          dispatch(attributeCreationTypeUpdated(value));
        },
        onRecordTypeUpdated: (value: string) => {
          dispatch(attributeCreationRecordTypeUpdated(value));
        },
        onValuePerLocaleUpdated: (valuePerLocale: boolean) => {
          dispatch(attributeCreationValuePerLocaleUpdated(valuePerLocale));
        },
        onValuePerChannelUpdated: (valuePerChannel: boolean) => {
          dispatch(attributeCreationValuePerChannelUpdated(valuePerChannel));
        },
        onCancel: () => {
          dispatch(attributeCreationCancel());
        },
        onSubmit: () => {
          dispatch(createAttribute());
        },
      },
    } as DispatchProps;
  }
)(Create);
