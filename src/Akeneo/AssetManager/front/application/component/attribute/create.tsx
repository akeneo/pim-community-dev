import * as React from 'react';
import {connect} from 'react-redux';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {EditState} from 'akeneoassetmanager/application/reducer/asset-family/edit';
import {
  attributeCreationCodeUpdated,
  attributeCreationLabelUpdated,
  attributeCreationCancel,
  attributeCreationTypeUpdated,
  attributeCreationValuePerLocaleUpdated,
  attributeCreationValuePerChannelUpdated,
} from 'akeneoassetmanager/domain/event/attribute/create';
import {createAttribute} from 'akeneoassetmanager/application/action/attribute/create';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {getAttributeTypes, AttributeType} from 'akeneoassetmanager/application/configuration/attribute';
import assetFamilyFetcher from 'akeneoassetmanager/infrastructure/fetcher/asset-family';
import {AssetFamily} from 'akeneoassetmanager/domain/model/asset-family/asset-family';
import Key from 'akeneoassetmanager/tools/key';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import {AssetsIllustration} from 'akeneo-design-system';

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

class Create extends React.Component<CreateProps> {
  private labelInput: HTMLInputElement;
  public props: CreateProps;
  state: {assetFamilies: AssetFamily[]} = {assetFamilies: []};

  async componentDidMount() {
    if (this.labelInput) {
      this.labelInput.focus();
    }

    const assetFamilies = await assetFamilyFetcher.fetchAll();
    this.setState({assetFamilies: assetFamilies});
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
                <AssetsIllustration />
              </div>
              <div>
                <div className="AknFullPage-titleContainer">
                  <div className="AknFullPage-subTitle">{__('pim_asset_manager.attribute.create.subtitle')}</div>
                  <div className="AknFullPage-title">{__('pim_asset_manager.attribute.create.title')}</div>
                  <div className="AknFullPage-description">{__('pim_asset_manager.attribute.create.description')}</div>
                </div>
                <div className="AknFormContainer">
                  <div className="AknFieldContainer" data-code="label">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_asset_manager.attribute.create.input.label"
                      >
                        {__('pim_asset_manager.attribute.create.input.label')}
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
                        id="pim_asset_manager.attribute.create.input.label"
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
                        htmlFor="pim_asset_manager.attribute.create.input.code"
                      >
                        {__('pim_asset_manager.attribute.create.input.code')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <input
                        type="text"
                        autoComplete="off"
                        className="AknTextField AknTextField--light"
                        id="pim_asset_manager.attribute.create.input.code"
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
                        htmlFor="pim_asset_manager.attribute.create.input.type"
                      >
                        {__('pim_asset_manager.attribute.create.input.type')}
                      </label>
                    </div>
                    <div className="AknFieldContainer-inputContainer">
                      <Dropdown
                        ItemView={AttributeTypeItemView}
                        label={__('pim_asset_manager.attribute.create.input.type')}
                        elements={this.getTypeOptions()}
                        selectedElement={this.props.data.type}
                        onSelectionChange={this.onTypeUpdate}
                      />
                    </div>
                    {getErrorsView(this.props.errors, 'type')}
                  </div>
                  <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerChannel">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_asset_manager.attribute.create.input.value_per_channel"
                      >
                        <Checkbox
                          id="pim_asset_manager.attribute.create.input.value_per_channel"
                          value={this.props.data.value_per_channel}
                          onChange={this.props.events.onValuePerChannelUpdated}
                        />
                        <span
                          onClick={() => {
                            this.props.events.onValuePerChannelUpdated(!this.props.data.value_per_channel);
                          }}
                        >
                          {__('pim_asset_manager.attribute.create.input.value_per_channel')}
                        </span>
                      </label>
                    </div>
                    {getErrorsView(this.props.errors, 'valuePerChannel')}
                  </div>
                  <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerLocale">
                    <div className="AknFieldContainer-header AknFieldContainer-header--light">
                      <label
                        className="AknFieldContainer-label"
                        htmlFor="pim_asset_manager.attribute.create.input.value_per_locale"
                      >
                        <Checkbox
                          id="pim_asset_manager.attribute.create.input.value_per_locale"
                          value={this.props.data.value_per_locale}
                          onChange={this.props.events.onValuePerLocaleUpdated}
                        />
                        <span
                          onClick={() => {
                            this.props.events.onValuePerLocaleUpdated(!this.props.data.value_per_locale);
                          }}
                        >
                          {__('pim_asset_manager.attribute.create.input.value_per_locale')}
                        </span>
                      </label>
                    </div>
                    {getErrorsView(this.props.errors, 'valuePerLocale')}
                  </div>
                  <button
                    className="AknButton AknButton--apply ok"
                    style={{position: 'static'}}
                    onClick={this.props.events.onSubmit}
                  >
                    {__('pim_asset_manager.attribute.create.confirm')}
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          title={__('pim_asset_manager.attribute.create.cancel')}
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
