import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Select2 from 'akeneoenrichedentity/application/component/app/select2';
import {AdditionalProperty} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import {ImageAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/image';
import {AllowedExtensionsOptions, AllowedExtensions} from "akeneoenrichedentity/domain/model/attribute/type/image/allowed-extensions";
import {MaxFileSize} from "akeneoenrichedentity/domain/model/attribute/type/image/max-file-size";

export default ({
  attribute,
  onAdditionalPropertyUpdated,
  errors
}: {
  attribute: ImageAttribute;
  onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
  errors: ValidationError[];
}) => {
  return (
    <div>
      <div className="AknFieldContainer" data-code="maxFileSize">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.max_file_size"
          >
            {__('pim_enriched_entity.attribute.edit.input.max_file_size')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField"
            id="pim_enriched_entity.attribute.edit.input.max_file_size"
            name="max_file_size"
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
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.allowed_extensions"
          >
            {__('pim_enriched_entity.attribute.edit.input.allowed_extensions')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Select2
            fieldId="pim_enriched_entity.attribute.edit.input.allowed_extensions"
            fieldName="allowed_extensions"
            data={AllowedExtensionsOptions}
            value={attribute.allowedExtensions.arrayValue()}
            multiple={true}
            readonly={false}
            onChange={(allowedExtensions: string[]) => {
              onAdditionalPropertyUpdated('allowed_extensions', AllowedExtensions.createFromArray(allowedExtensions))
            }}
          />
        </div>
        {getErrorsView(errors, 'allowedExtensions')}
      </div>
    </div>
  );
};
