import React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import Select2 from 'akeneoreferenceentity/application/component/app/select2';
import {ImageAttribute, ImageAdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/type/image';
import {
  AllowedExtensionsOptions,
  AllowedExtensions,
} from 'akeneoreferenceentity/domain/model/attribute/type/image/allowed-extensions';
import {MaxFileSize} from 'akeneoreferenceentity/domain/model/attribute/type/image/max-file-size';
import Key from 'akeneoreferenceentity/tools/key';

const ImageView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: ImageAttribute;
  onAdditionalPropertyUpdated: (property: string, value: ImageAdditionalProperty) => void;
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
          <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.max_file_size">
            {__('pim_reference_entity.attribute.edit.input.max_file_size')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_reference_entity.attribute.edit.input.max_file_size"
            name="max_file_size"
            value={attribute.maxFileSize.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              let newMaxFileSize = event.currentTarget.value;
              if (!MaxFileSize.isValid(newMaxFileSize)) {
                event.currentTarget.value = attribute.maxFileSize.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_file_size', MaxFileSize.createFromString(newMaxFileSize));
            }}
            readOnly={!rights.attribute.edit}
          />
        </div>
        {getErrorsView(errors, 'maxFileSize')}
      </div>
      <div className="AknFieldContainer--packed" data-code="allowedExtensions">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_reference_entity.attribute.edit.input.allowed_extensions"
          >
            {__('pim_reference_entity.attribute.edit.input.allowed_extensions')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Select2
            id="pim_reference_entity.attribute.edit.input.allowed_extensions"
            name="allowed_extensions"
            data={(AllowedExtensionsOptions as any) as {[choiceValue: string]: string}}
            value={attribute.allowedExtensions.arrayValue()}
            multiple={true}
            readOnly={!rights.attribute.edit}
            configuration={{
              allowClear: true,
            }}
            onChange={(allowedExtensions: string[]) => {
              onAdditionalPropertyUpdated('allowed_extensions', AllowedExtensions.createFromArray(allowedExtensions));
            }}
          />
        </div>
        {getErrorsView(errors, 'allowedExtensions')}
      </div>
    </>
  );
};

export const view = ImageView;
