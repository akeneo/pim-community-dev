import React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import Select2 from 'akeneoassetmanager/application/component/app/select2';
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
import {Key, TagInput} from 'akeneo-design-system';
import {MediaTypes} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';
import {
  normalizeMediaType,
  createMediaTypeFromNormalized,
} from 'akeneoassetmanager/domain/model/attribute/type/media-file/media-type';

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
  const inputTextClassName = `AknTextField AknTextField--light ${
    !rights.attribute.edit ? 'AknTextField--disabled' : ''
  }`;

  return (
    <>
      <div className="AknFieldContainer--packed" data-code="maxFileSize">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.max_file_size">
            {__('pim_asset_manager.attribute.edit.input.max_file_size')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.max_file_size"
            name="max_file_size"
            value={maxFileSizeStringValue(attribute.maxFileSize)}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              let newMaxFileSize = event.currentTarget.value;
              if (!isValidMaxFileSize(newMaxFileSize)) {
                event.currentTarget.value = maxFileSizeStringValue(attribute.maxFileSize);
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_file_size', createMaxFileSizeFromString(newMaxFileSize));
            }}
            readOnly={!rights.attribute.edit}
          />
        </div>
        {getErrorsView(errors, 'maxFileSize')}
      </div>
      <div className="AknFieldContainer--packed" data-code="mediaType">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.media_type">
            {__('pim_asset_manager.attribute.edit.input.media_type')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Select2
            id="pim_asset_manager.attribute.edit.input.media_type"
            name="media_type"
            data={(MediaTypes as any) as {[choiceValue: string]: string}}
            value={normalizeMediaType(attribute.mediaType)}
            readOnly={!rights.attribute.edit}
            configuration={{
              allowClear: true,
            }}
            onChange={(mediaType: string) => {
              onAdditionalPropertyUpdated('media_type', createMediaTypeFromNormalized(mediaType));
            }}
          />
        </div>
        {getErrorsView(errors, 'mediaType')}
      </div>
      <div className="AknFieldContainer--packed" data-code="allowedExtensions">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_asset_manager.attribute.edit.input.allowed_extensions"
          >
            {__('pim_asset_manager.attribute.edit.input.allowed_extensions')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <TagInput
            onChange={(allowedExtensions: string[]) => {
              onAdditionalPropertyUpdated('allowed_extensions', createAllowedExtensionFromArray(allowedExtensions));
            }}
            readOnly={!rights.attribute.edit}
            value={normalizeAllowedExtension(attribute.allowedExtensions)}
            id="pim_asset_manager.attribute.edit.input.allowed_extensions"
            placeholder="Placeholder"
            name="allowed_extensions"
          />
        </div>
        {getErrorsView(errors, 'allowedExtensions')}
      </div>
    </>
  );
};

export const view = MediaFileView;
