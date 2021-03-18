import React from 'react';
import styled from 'styled-components';
import LocaleReference, {
  localeReferenceAreEqual,
  localeReferenceStringValue,
} from 'akeneoassetmanager/domain/model/locale-reference';
import ChannelReference, {channelReferenceAreEqual} from 'akeneoassetmanager/domain/model/channel-reference';
import EditionValue from 'akeneoassetmanager/domain/model/asset/edition-value';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getDataFieldView} from 'akeneoassetmanager/application/configuration/value';
import ErrorBoundary from 'akeneoassetmanager/application/component/app/error-boundary';
import {getLabelInCollection} from 'akeneoassetmanager/domain/model/label-collection';
import EditionAsset from 'akeneoassetmanager/domain/model/asset/edition-asset';
import {getValuesForChannelAndLocale, isValueEmpty} from 'akeneoassetmanager/domain/model/asset/value';
import {hasFieldAsTarget} from 'akeneoassetmanager/domain/model/asset-family/transformation';
import {Field, Helper} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {attributeIdentifierStringValue} from 'akeneoassetmanager/domain/model/attribute/identifier';
import {isTextAreaAttribute} from 'akeneoassetmanager/domain/model/attribute/type/text';

const ValueCollectionContainer = styled.div`
  display: flex;
  flex-direction: column;
  gap: 20px;
  margin-bottom: 20px;
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
    <ValueCollectionContainer>
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
        const canEditData = canEditAsset && canEditAttribute && canEditLocale && !isTransformationTarget;
        const fieldErrors = errors.filter(
          (error: ValidationError) =>
            `values.${value.attribute.code}` === error.propertyPath &&
            channelReferenceAreEqual(error.invalidValue.channel, value.channel) &&
            localeReferenceAreEqual(error.invalidValue.locale, value.locale)
        );

        return (
          <ErrorBoundary
            key={attributeIdentifierStringValue(value.attribute.identifier)}
            errorMessage={translate('pim_asset_manager.asset.error.value', {
              fieldName: attributeLabel,
            })}
          >
            <Field
              label={attributeLabel}
              incomplete={value.attribute.is_required && isValueEmpty(value)}
              channel={value.channel}
              locale={value.locale}
              fullWidth={isTextAreaAttribute(value.attribute)}
            >
              <DataView
                value={value}
                onChange={onValueChange}
                onSubmit={onFieldSubmit}
                channel={channel}
                locale={locale}
                invalid={0 !== fieldErrors.length}
                canEditData={canEditData}
              />
              {fieldErrors.map(({messageTemplate, parameters}: ValidationError, index: number) => (
                <Helper key={index} level="error">
                  {translate(messageTemplate, parameters)}
                </Helper>
              ))}
              {isTransformationTarget && (
                <Helper inline={true}>{translate('pim_asset_manager.attribute.used_as_transformation_target')}</Helper>
              )}
            </Field>
          </ErrorBoundary>
        );
      })}
    </ValueCollectionContainer>
  );
};

export {ValueCollection};
