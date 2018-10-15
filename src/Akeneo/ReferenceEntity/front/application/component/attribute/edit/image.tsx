import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import Select2 from 'akeneoreferenceentity/application/component/app/select2';
import {AdditionalProperty} from 'akeneoreferenceentity/domain/model/attribute/attribute';
import {ImageAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/image';
import {
  AllowedExtensionsOptions,
  AllowedExtensions,
} from 'akeneoreferenceentity/domain/model/attribute/type/image/allowed-extensions';
import {MaxFileSize} from 'akeneoreferenceentity/domain/model/attribute/type/image/max-file-size';

export default ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
}: {
  attribute: ImageAttribute;
  onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
}) => {
  return (
    <React.Fragment>
      <div className="AknFieldContainer" data-code="maxFileSize">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.max_file_size">
            {__('pim_reference_entity.attribute.edit.input.max_file_size')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField AknTextField--light"
            id="pim_reference_entity.attribute.edit.input.max_file_size"
            name="max_file_size"
            value={attribute.maxFileSize.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if ('Enter' === event.key) {
                onSubmit();
              }
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!MaxFileSize.isValid(event.currentTarget.value)) {
                event.currentTarget.value = attribute.maxFileSize.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_file_size', MaxFileSize.createFromString(event.currentTarget.value));
            }}
          />
        </div>
        {getErrorsView(errors, 'maxFileSize')}
      </div>
      <div className="AknFieldContainer" data-code="allowedExtensions">
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
            fieldId="pim_reference_entity.attribute.edit.input.allowed_extensions"
            fieldName="allowed_extensions"
            data={AllowedExtensionsOptions}
            value={attribute.allowedExtensions.arrayValue()}
            multiple={true}
            readonly={false}
            onChange={(allowedExtensions: string[]) => {
              onAdditionalPropertyUpdated('allowed_extensions', AllowedExtensions.createFromArray(allowedExtensions));
            }}
          />
        </div>
        {getErrorsView(errors, 'allowedExtensions')}
      </div>
    </React.Fragment>
  );
};
