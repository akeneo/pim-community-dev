import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Select2 from 'akeneoenrichedentity/application/component/app/select2';
import {AdditionalProperty} from 'akeneoenrichedentity/domain/model/attribute/attribute';

export default ({
  attribute,
  onAdditionalPropertyUpdated,
  errors
}: {
  attribute: any;
  onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
  errors: ValidationError[];
}) => {
  const allowedExtensionsList = {
    gif: 'gif',
    jfif: 'jfif',
    jif: 'jif',
    jpeg: 'jpeg',
    jpg: 'jpg',
    pdf: 'pdf',
    png: 'png',
    psd: 'psd',
    tif: 'tif',
    tiff: 'tiff',
  };

  return (
    <div>
      <div className="AknFieldContainer" data-code="max-file-size">
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
            name="maxFileSize"
            onChange={(event: any) => onAdditionalPropertyUpdated(event.target.name, event.target.value)}
          />
        </div>
        {getErrorsView(errors, 'maxFileSize')}
      </div>
      <div className="AknFieldContainer" data-code="allowed-extensions">
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
            data={allowedExtensionsList}
            value={attribute.allowedExtensions}
            multiple={true}
            readonly={false}
            onChange={(allowedExtensions: string[]) => {
              onAdditionalPropertyUpdated('allowedExtensions', allowedExtensions)
            }}
          />
        </div>
        {getErrorsView(errors, 'allowedExtensions')}
      </div>
    </div>
  );
};
