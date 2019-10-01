import * as React from 'react';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference, {channelReferenceStringValue} from 'akeneoassetmanager/domain/model/channel-reference';
import Value from 'akeneoassetmanager/domain/model/asset/value';
import ValidationError from 'akeneoassetmanager/domain/model/validation-error';
import Asset from 'akeneoassetmanager/domain/model/asset/asset';
import {getDataFieldView} from 'akeneoassetmanager/application/configuration/value';
import {getErrorsView} from 'akeneoassetmanager/application/component/asset/edit/validaton-error';
import __ from 'akeneoassetmanager/tools/translator';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';

export default (
  asset: Asset,
  channel: ChannelReference,
  locale: LocaleReference,
  errors: ValidationError[],
  onValueChange: (value: Value) => void,
  onFieldSubmit: () => void,
  rights: {
    locale: {
      edit: boolean;
    };
    asset: {
      edit: boolean;
      delete: boolean;
    };
  }
) => {
  const visibleValues = asset
    .getValueCollection()
    .getValuesForChannelAndLocale(channel, locale)
    .sort((firstValue: Value, secondValue: Value) => firstValue.attribute.order - secondValue.attribute.order);

  return visibleValues.map((value: Value) => {
    const DataView = getDataFieldView(value);

    const canEditData = value.attribute.valuePerLocale ? rights.asset.edit && rights.locale.edit : rights.asset.edit;
    return (
      <div
        key={attributeIdentifierStringValue(value.attribute.getIdentifier())}
        className="AknFieldContainer"
        data-code={value.attribute.getCode()}
      >
        <div className="AknFieldContainer-header AknFieldContainer-header--light AknFieldContainer-header AknFieldContainer-header--light--small">
          <label
            title={value.attribute.getLabel(localeReferenceStringValue(locale))}
            className="AknFieldContainer-label"
            htmlFor={`pim_asset_manager.asset.enrich.${value.attribute.getCode()}`}
          >
            <span
              className={`AknBadge AknBadge--small AknBadge--highlight AknBadge--floating ${
                value.attribute.isRequired && value.data.isEmpty() ? '' : 'AknBadge--hidden'
              }`}
            />
            {value.attribute.getLabel(localeReferenceStringValue(locale))}
          </label>
          <span className="AknFieldContainer-fieldInfo">
            <span>
              <span>{value.attribute.valuePerChannel ? channelReferenceStringValue(value.channel) : null}</span>
              &nbsp;
              <span>
                {value.attribute.valuePerLocale ? (
                  <Flag
                    locale={createLocaleFromCode(localeReferenceStringValue(value.locale))}
                    displayLanguage={true}
                  />
                ) : null}
              </span>
            </span>
          </span>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <ErrorBoundary
            errorMessage={__('pim_asset_manager.asset.error.value', {
              fieldName: value.attribute.getLabel(localeReferenceStringValue(locale)),
            })}
          >
            <DataView
              value={value}
              onChange={onValueChange}
              onSubmit={onFieldSubmit}
              channel={channel}
              locale={locale}
              canEditData={canEditData}
            />
          </ErrorBoundary>
        </div>
        {getErrorsView(errors, value)}
      </div>
    );
  });
};
