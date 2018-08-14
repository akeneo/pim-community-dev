import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Switch from 'akeneoenrichedentity/application/component/app/switch';
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
  return (
    <div className="AknFormContainer">
      <div className="AknFieldContainer" data-code="max-length">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.max_length"
          >
            {__('pim_enriched_entity.attribute.edit.input.max_length')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField"
            id="pim_enriched_entity.attribute.edit.input.max_length"
            name="maxLength"
            onChange={(event: any) => onAdditionalPropertyUpdated(event.target.name, event.target.value)}
          />
        </div>
        {getErrorsView(errors, 'maxLength')}
      </div>
      <div className="AknFieldContainer" data-code="isTextarea">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.text_area"
          >
            {__('pim_enriched_entity.attribute.edit.input.text_area')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Switch
            id="pim_enriched_entity.attribute.edit.input.text_area"
            value={attribute.isTextarea}
            onChange={(isTextarea: boolean) => onAdditionalPropertyUpdated('isTextarea', isTextarea)}
          />
        </div>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea &&
      <div className="AknFieldContainer" data-code="richTextEditor">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.rich_text_editor"
          >
            {__('pim_enriched_entity.attribute.edit.input.rich_text_editor')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Switch
            id="pim_enriched_entity.attribute.edit.input.rich_text_editor"
            value={attribute.richTextEditor}
            onChange={(richTextEditor: boolean) => onAdditionalPropertyUpdated('richTextEditor', richTextEditor)}
          />
        </div>
        {getErrorsView(errors, 'richTextEditor')}
      </div>
      }
    </div>
  );
};
