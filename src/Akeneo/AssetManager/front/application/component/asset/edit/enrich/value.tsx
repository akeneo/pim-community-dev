import React from 'react';
import styled from 'styled-components';
import __ from 'akeneoassetmanager/tools/translator';
import LocaleReference, {localeReferenceStringValue} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference, {channelReferenceStringValue} from 'akeneoassetmanager/domain/model/channel-reference';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getDataFieldView} from 'akeneoassetmanager/application/configuration/value';
import {getErrorsView} from 'akeneoassetmanager/application/component/asset/edit/validation-error';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import Flag from 'akeneoassetmanager/tools/component/flag';
import {createLocaleFromCode} from 'akeneoassetmanager/domain/model/locale';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {getValuesForChannelAndLocale, isValueEmpty} from 'akeneoassetmanager/domain/model/asset/value';
import {hasFieldAsTarget} from 'akeneoassetmanager/domain/model/asset-family/transformation';
import {getColor, Helper, LockIcon} from 'akeneo-design-system';

const NoZIndexFieldContainer = styled.div.attrs(() => ({className: 'AknFieldContainer'}))`
  z-index: unset;
`;

const ValueLabel = styled.label`
  display: flex;
  flex-grow: 1;
  color: ${getColor('grey', 100)};
  margin-bottom: 5px;

  > :first-child {
    margin-right: 5px;
  }
`;

const InputContainer = styled.div`
  max-width: 460px;
`;

export default (
  asset: EditionAsset,
  channel: ChannelReference,
  locale: LocaleReference,
  errors: ValidationError[],
  onValueChange: (value: EditionValue) => void,
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
  const visibleValues = getValuesForChannelAndLocale(asset.values, channel, locale).sort(
    (firstValue: EditionValue, secondValue: EditionValue) => firstValue.attribute.order - secondValue.attribute.order
  );

  return visibleValues.map((value: EditionValue) => {
    const DataView = getDataFieldView(value);
    const attributeLabel = getLabelInCollection(
      value.attribute.labels,
      localeReferenceStringValue(locale),
      true,
      value.attribute.code
    );

    const isTransformationTarget = hasFieldAsTarget(asset.assetFamily.transformations, {
      attribute: value.attribute.code,
      channel,
      locale,
    });

    const canEditAsset = rights.asset.edit;
    const canEditAttribute = !value.attribute.is_read_only;
    const canEditLocale = value.attribute.value_per_locale ? rights.locale.edit : true;
    const canEditData = canEditAsset && canEditAttribute && canEditLocale && !isTransformationTarget;

    return (
      <NoZIndexFieldContainer
        key={attributeIdentifierStringValue(value.attribute.identifier)}
        data-code={value.attribute.code}
      >
        <div className="AknFieldContainer-header AknFieldContainer-header--light AknFieldContainer-header AknFieldContainer-header--light--small">
          <ValueLabel title={attributeLabel} htmlFor={`pim_asset_manager.asset.enrich.${value.attribute.code}`}>
            {!canEditData && <LockIcon size={20} />}
            <span
              className={`AknBadge AknBadge--small AknBadge--highlight AknBadge--floating ${
                value.attribute.is_required && isValueEmpty(value) ? '' : 'AknBadge--hidden'
              }`}
            />
            {attributeLabel}
          </ValueLabel>
          <span className="AknFieldContainer-fieldInfo">
            <span>
              <span>{value.attribute.value_per_channel ? channelReferenceStringValue(value.channel) : null}</span>
              &nbsp;
              <span>
                {value.attribute.value_per_locale ? (
                  <Flag
                    locale={createLocaleFromCode(localeReferenceStringValue(value.locale))}
                    displayLanguage={true}
                  />
                ) : null}
              </span>
            </span>
          </span>
        </div>
        <InputContainer className="AknFieldContainer-inputContainer">
          <ErrorBoundary
            errorMessage={__('pim_asset_manager.asset.error.value', {
              fieldName: attributeLabel,
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
        </InputContainer>
        {isTransformationTarget && (
          <Helper inline={true}>{__('pim_asset_manager.attribute.used_as_transformation_target')}</Helper>
        )}
        {getErrorsView(errors, value)}
      </NoZIndexFieldContainer>
    );
  });
};
