import React, {useRef} from 'react';
import {connect} from 'react-redux';
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
import {AssetsIllustration, Key, Checkbox, Button, useAutoFocus} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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

const Create = ({data, errors, events, context}: CreateProps) => {
  const translate = useTranslate();
  const labelInputRef = useRef<HTMLInputElement>(null);
  useAutoFocus(labelInputRef);

  const onCodeUpdate = (event: React.ChangeEvent<HTMLInputElement>) => events.onCodeUpdated(event.target.value);
  const onLabelUpdate = (event: React.ChangeEvent<HTMLInputElement>) =>
    events.onLabelUpdated(event.target.value, context.locale);
  const onTypeUpdate = (value: DropdownElement) => events.onTypeUpdated(value.identifier);
  const onKeyPress = (event: React.KeyboardEvent<HTMLInputElement>) => Key.Enter === event.key && events.onSubmit();

  const getTypeOptions = (): DropdownElement[] =>
    getAttributeTypes().map((type: AttributeType) => ({
      identifier: type.identifier,
      label: translate(type.label),
      original: type,
    }));

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
                <div className="AknFullPage-subTitle">{translate('pim_asset_manager.attribute.create.subtitle')}</div>
                <div className="AknFullPage-title">{translate('pim_asset_manager.attribute.create.title')}</div>
                <div className="AknFullPage-description">
                  {translate('pim_asset_manager.attribute.create.description')}
                </div>
              </div>
              <div className="AknFormContainer">
                <div className="AknFieldContainer" data-code="label">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.create.input.label">
                      {translate('pim_asset_manager.attribute.create.input.label')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      type="text"
                      autoComplete="off"
                      ref={labelInputRef}
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.attribute.create.input.label"
                      name="label"
                      value={data.labels[context.locale] || ''}
                      onChange={onLabelUpdate}
                      onKeyPress={onKeyPress}
                    />
                    <Flag
                      locale={createLocaleFromCode(context.locale)}
                      displayLanguage={false}
                      className="AknFieldContainer-inputSides"
                    />
                  </div>
                  {getErrorsView(errors, 'labels')}
                </div>
                <div className="AknFieldContainer" data-code="code">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.create.input.code">
                      {translate('pim_asset_manager.attribute.create.input.code')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <input
                      type="text"
                      autoComplete="off"
                      className="AknTextField AknTextField--light"
                      id="pim_asset_manager.attribute.create.input.code"
                      name="code"
                      value={data.code}
                      onChange={onCodeUpdate}
                      onKeyPress={onKeyPress}
                    />
                  </div>
                  {getErrorsView(errors, 'code')}
                </div>
                <div className="AknFieldContainer" style={{position: 'static'}} data-code="type">
                  <div className="AknFieldContainer-header AknFieldContainer-header--light">
                    <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.create.input.type">
                      {translate('pim_asset_manager.attribute.create.input.type')}
                    </label>
                  </div>
                  <div className="AknFieldContainer-inputContainer">
                    <Dropdown
                      ItemView={AttributeTypeItemView}
                      label={translate('pim_asset_manager.attribute.create.input.type')}
                      elements={getTypeOptions()}
                      selectedElement={data.type}
                      onSelectionChange={onTypeUpdate}
                    />
                  </div>
                  {getErrorsView(errors, 'type')}
                </div>
                <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerChannel">
                  <Checkbox
                    id="pim_asset_manager.attribute.create.input.value_per_channel"
                    checked={data.value_per_channel}
                    onChange={events.onValuePerChannelUpdated}
                  >
                    {translate('pim_asset_manager.attribute.create.input.value_per_channel')}
                  </Checkbox>
                  {getErrorsView(errors, 'valuePerChannel')}
                </div>
                <div className="AknFieldContainer" style={{position: 'static'}} data-code="valuePerLocale">
                  <Checkbox
                    id="pim_asset_manager.attribute.create.input.value_per_locale"
                    checked={data.value_per_locale}
                    onChange={events.onValuePerLocaleUpdated}
                  >
                    {translate('pim_asset_manager.attribute.create.input.value_per_locale')}
                  </Checkbox>
                  {getErrorsView(errors, 'valuePerLocale')}
                </div>
                <Button onClick={events.onSubmit}>{translate('pim_asset_manager.attribute.create.confirm')}</Button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        title={translate('pim_asset_manager.attribute.create.cancel')}
        className="AknFullPage-cancel cancel"
        onClick={events.onCancel}
        tabIndex={0}
        onKeyPress={event => {
          if (Key.Space === event.key) events.onCancel();
        }}
      />
    </div>
  );
};

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
