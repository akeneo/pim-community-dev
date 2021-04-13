import React from 'react';
import {Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, TextField, ValidationError} from '@akeneo-pim-community/shared';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {MediaLinkAttribute} from 'akeneoassetmanager/domain/model/attribute/type/media-link';
import {
  createPrefixFromString,
  isValidPrefix,
  NormalizedPrefix,
  prefixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/prefix';
import {
  createSuffixFromString,
  isValidSuffix,
  NormalizedSuffix,
  suffixStringValue,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/suffix';
import {
  NormalizableAdditionalProperty,
  wrapNormalizableAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/attribute';
import {
  MediaTypes,
  createMediaTypeFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/media-link/media-type';

const MediaLinkView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: MediaLinkAttribute;
  onAdditionalPropertyUpdated: (property: string, value: NormalizableAdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
  rights: {
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
}) => {
  const translate = useTranslate();

  return (
    <>
      <TextField
        label={translate('pim_asset_manager.attribute.edit.input.prefix')}
        readOnly={!rights.attribute.edit}
        value={prefixStringValue(attribute.prefix)}
        onChange={value => {
          if (!isValidPrefix(value)) return;

          onAdditionalPropertyUpdated(
            'prefix',
            wrapNormalizableAdditionalProperty<NormalizedPrefix>(createPrefixFromString(value)).normalize()
          );
        }}
        onSubmit={onSubmit}
        errors={getErrorsForPath(errors, 'prefix')}
      />
      <TextField
        label={translate('pim_asset_manager.attribute.edit.input.suffix')}
        readOnly={!rights.attribute.edit}
        value={suffixStringValue(attribute.suffix)}
        onChange={value => {
          if (!isValidSuffix(value)) return;

          onAdditionalPropertyUpdated(
            'suffix',
            wrapNormalizableAdditionalProperty<NormalizedSuffix>(createSuffixFromString(value)).normalize()
          );
        }}
        onSubmit={onSubmit}
        errors={getErrorsForPath(errors, 'suffix')}
      />
      <Field label={translate('pim_asset_manager.attribute.edit.input.media_type')}>
        <SelectInput
          readOnly={!rights.attribute.edit}
          emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
          clearable={false}
          verticalPosition="up"
          value={attribute.mediaType.toString()}
          onChange={mediaType => {
            onAdditionalPropertyUpdated('media_type', createMediaTypeFromString(mediaType ?? MediaTypes.image));
          }}
        >
          {Object.values(MediaTypes).map(mediaType => (
            <SelectInput.Option key={mediaType} value={mediaType}>
              {mediaType}
            </SelectInput.Option>
          ))}
        </SelectInput>
        {getErrorsView(errors, 'mediaType')}
      </Field>
    </>
  );
};

export const view = MediaLinkView;
