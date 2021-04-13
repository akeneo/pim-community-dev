import React from 'react';
import {Field, SelectInput, TagInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, TextField} from '@akeneo-pim-community/shared';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {
  MediaFileAttribute,
  MediaFileAdditionalProperty,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file';
import {
  normalizeAllowedExtension,
  createAllowedExtensionFromArray,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/allowed-extensions';
import {
  maxFileSizeStringValue,
  isValidMaxFileSize,
  createMaxFileSizeFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/max-file-size';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';
import {createMediaTypeFromString} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';

const MediaFileView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: MediaFileAttribute;
  onAdditionalPropertyUpdated: (property: string, value: MediaFileAdditionalProperty) => void;
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
        value={maxFileSizeStringValue(attribute.maxFileSize)}
        onChange={value => {
          if (!isValidMaxFileSize(value)) return;

          onAdditionalPropertyUpdated('max_file_size', createMaxFileSizeFromString(value));
        }}
        readOnly={!rights.attribute.edit}
        onSubmit={onSubmit}
        errors={getErrorsForPath(errors, 'maxFileSize')}
        label={translate('pim_asset_manager.attribute.edit.input.max_file_size')}
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
      <Field label={translate('pim_asset_manager.attribute.edit.input.allowed_extensions')}>
        <TagInput
          onChange={(allowedExtensions: string[]) => {
            onAdditionalPropertyUpdated('allowed_extensions', createAllowedExtensionFromArray(allowedExtensions));
          }}
          readOnly={!rights.attribute.edit}
          value={normalizeAllowedExtension(attribute.allowedExtensions)}
        />
        {getErrorsView(errors, 'allowedExtensions')}
      </Field>
    </>
  );
};

export const view = MediaFileView;
