import React from 'react';
import styled from 'styled-components';
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
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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

type ValueCollectionProps = {
  asset: EditionAsset;
  channel: ChannelReference;
  locale: LocaleReference;
  errors: ValidationError[];
  onValueChange: (value: EditionValue) => void;
  onFieldSubmit: () => void;
  canEditLocale: boolean;
  canEditAsset: boolean;
};

const ValueCollection = ({
  asset,
  channel,
  locale,
  errors,
  onValueChange,
  onFieldSubmit,
  canEditLocale,
  canEditAsset,
}: ValueCollectionProps) => {
  const translate = useTranslate();
  const visibleValues = getValuesForChannelAndLocale(asset.values, channel, locale).sort(
    (firstValue: EditionValue, secondValue: EditionValue) => firstValue.attribute.order - secondValue.attribute.order
  );

  return (
    <div>
      {visibleValues.map((value: EditionValue) => {
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

        const canEditAttribute = !value.attribute.is_read_only;
        const canEditData =
          canEditAsset &&
          canEditAttribute &&
          (!value.attribute.value_per_locale || canEditLocale) &&
          !isTransformationTarget;

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
            <div className="AknFieldContainer-inputContainer">
              <ErrorBoundary
                errorMessage={translate('pim_asset_manager.asset.error.value', {
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
            </div>
            {isTransformationTarget && (
              <Helper inline={true}>{translate('pim_asset_manager.attribute.used_as_transformation_target')}</Helper>
            )}
            {getErrorsView(errors, value)}
          </NoZIndexFieldContainer>
        );
      })}
    </div>
  );
};

export {ValueCollection};
