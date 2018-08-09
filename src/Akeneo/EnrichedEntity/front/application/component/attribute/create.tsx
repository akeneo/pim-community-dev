import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import Flag from 'akeneoenrichedentity/tools/component/flag';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import {EditState} from 'akeneoenrichedentity/application/reducer/enriched-entity/edit';
import Switch from 'akeneoenrichedentity/application/component/app/switch';
import {
  attributeCreationCodeUpdated,
  attributeCreationLabelUpdated,
  attributeCreationCancel,
  attributeCreationTypeUpdated,
  attributeCreationValuePerLocaleUpdated,
  attributeCreationValuePerChannelUpdated,
} from 'akeneoenrichedentity/domain/event/attribute/create';
import {AttributeType} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {createAttribute} from 'akeneoenrichedentity/application/action/attribute/create';
import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';

interface StateProps {
  context: {
    locale: string;
  };
  data: {
    code: string;
    labels: {
      [localeCode: string]: string;
    };
    type: AttributeType;
    valuePerLocale: boolean;
    valuePerChannel: boolean;
  };
  errors: ValidationError[];
}

interface DispatchProps {
  events: {
    onCodeUpdated: (value: string) => void;
    onLabelUpdated: (value: string, locale: string) => void;
    onTypeUpdated: (type: string) => void;
    onValuePerLocaleUpdated: (valuePerLocale: boolean) => void;
    onValuePerChannelUpdated: (valuePerChannel: boolean) => void;
    onCancel: () => void;
    onSubmit: () => void;
  };
}

interface CreateProps extends StateProps, DispatchProps {}

const AttributeTypeItemView = ({
  element,
  isActive,
  onClick,
}: {
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
        if (' ' === event.key) onClick(element);
      }}
      tabIndex={0}
    >
      <img
        className="AknDropdown-menuLinkImage"
        src={`bundles/pimui/images/attribute/icon-${element.identifier}.svg`}
      />
      <span>{element.label}</span>
    </div>
  );
};

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;

  componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }
  }

  private onCodeUpdate = (event: any) => {
    this.props.events.onCodeUpdated(event.target.value);
  };

  private onLabelUpdate = (event: any) => {
    this.props.events.onLabelUpdated(event.target.value, this.props.context.locale);
  };

  private onTypeUpdate = (value: DropdownElement) => {
    this.props.events.onTypeUpdated(value.identifier);
  };

  private onKeyPress = (event: any) => {
    if ('Enter' === event.key) {
      this.props.events.onSubmit();
    }
  };

  private getTypeOptions = (): DropdownElement[] => {
    return Object.values(AttributeType).map((type: string) => {
      return {
        identifier: type,
        label: __(`pim_enriched_entity.attribute.type.${type}`),
      };
    });
  };

  render(): JSX.Element | JSX.Element[] | null {
    return (
      <div className="modal in modal--fullPage" aria-hidden="false" style={{zIndex: 1041}}>
        <div>
          <div className="AknFullPage AknFullPage--modal">
            <div className="AknFullPage-content">
              <div className="AknFullPage-left">
                <img src="bundles/pimui/images/illustrations/Family.svg" className="AknFullPage-image" />
              </div>
              <div className="AknFullPage-right">
                <div className="AknFullPage-subTitle">{__('pim_enriched_entity.attribute.create.subtitle')}</div>
                <div className="AknFullPage-title">{__('pim_enriched_entity.attribute.create.title')}</div>
                <div>
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.attribute.create.input.label"
                      >
                        {__('pim_enriched_entity.attribute.create.input.label')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        ref={(input: HTMLInputElement) => {
                          this.labelInput = input;
                        }}
                        className="AknTextField"
                        id="pim_enriched_entity.attribute.create.input.label"
                        name="label"
                        value={this.props.data.labels[this.props.context.locale]}
                        onChange={this.onLabelUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                      <Flag locale={this.props.context.locale} displayLanguage={false} />
                    </div>
                    {getErrorsView(this.props.errors, 'labels')}
                  </div>
                  <div className="AknFieldContainer" data-code="code">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.attribute.create.input.code"
                      >
                        {__('pim_enriched_entity.attribute.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        className="AknTextField"
                        id="pim_enriched_entity.attribute.create.input.code"
                        name="code"
                        value={this.props.data.code}
                        onChange={this.onCodeUpdate}
                        onKeyPress={this.onKeyPress}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'identifier')}
                  </div>
                  <div className="AknFieldContainer" data-code="type">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.attribute.create.input.type"
                      >
                        {__('pim_enriched_entity.attribute.create.input.type')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <Dropdown
                        ItemView={AttributeTypeItemView}
                        label={__('pim_enriched_entity.attribute.create.input.type')}
                        elements={this.getTypeOptions()}
                        selectedElement={this.props.data.type}
                        onSelectionChange={this.onTypeUpdate}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'type')}
                  </div>
                  <div className="AknFieldContainer" data-code="valuePerLocale">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.attribute.create.input.value_per_locale"
                      >
                        {__('pim_enriched_entity.attribute.create.input.value_per_locale')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <Switch
                        id="pim_enriched_entity.attribute.create.input.value_per_locale"
                        value={this.props.data.valuePerLocale}
                        onChange={this.props.events.onValuePerLocaleUpdated}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'valuePerLocale')}
                  </div>
                  <div className="AknFieldContainer" data-code="valuePerChannel">
                    <div className="AknFieldContainer-header">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_enriched_entity.attribute.create.input.value_per_channel"
                      >
                        {__('pim_enriched_entity.attribute.create.input.value_per_channel')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <Switch
                        id="pim_enriched_entity.attribute.create.input.value_per_channel"
                        value={this.props.data.valuePerChannel}
                        onChange={this.props.events.onValuePerChannelUpdated}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'valuePerChannel')}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div className="AknButtonList AknButtonList--right modal-footer">
          <button
            className="AknButtonList-item AknButton AknButton--apply ok icons-holder-text"
            onClick={this.props.events.onSubmit}
          >
            {__('pim_enriched_entity.attribute.create.confirm')}
          </button>
          <span
            title="{__('pim_enriched_entity.attribute.create.cancel')}"
            className="AknButtonList-item AknButton AknButton--grey cancel icons-holder-text"
            onClick={this.props.events.onCancel}
            tabIndex={0}
            onKeyPress={event => {
              if (' ' === event.key) {
                this.props.events.onCancel();
              }
            }}
          >
            {__('pim_enriched_entity.attribute.create.cancel')}
          </span>
        </div>
      </div>
    );
  }
}

export default connect(
  (state: EditState): StateProps => {
    const locale = undefined === state.user || undefined === state.user.catalogLocale ? '' : state.user.catalogLocale;

    return {
      data: state.createAttribute.data,
      errors: state.createAttribute.errors,
      context: {
        locale: locale,
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
