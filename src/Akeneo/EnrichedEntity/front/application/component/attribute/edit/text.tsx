import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Switch from 'akeneoenrichedentity/application/component/app/switch';
import {AdditionalProperty, ValidationRuleOptions, TextAttribute} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';

const AttributeValidationRuleItemView = ({
   element,
   isActive,
   onClick,
 }: {
  element: DropdownElement;
  isActive: boolean;
  onClick: (element: DropdownElement) => void;
}) => {
  const className = `AknDropdown-menuLink AknDropdown-menuLink--withImage ${
    isActive ? 'AknDropdown-menuLink--active' : ''
  }`;

  return (
    <div
      className={className}
      data-identifier={element.identifier}
      onClick={() => onClick(element)}
      onKeyPress={event => {
        if (' ' === event.key) onClick(element);
      }}
      tabIndex={0}
    >
      <span>{element.label}</span>
    </div>
  );
};

const getValidationRuleOptions = (): DropdownElement[] => {
  return Object.values(ValidationRuleOptions).map((option: string) => {
    return {
      identifier: option,
      label: __(`pim_enriched_entity.attribute.edit.input.options.validation_rule.${option}`),
    };
  });
};

export default ({
  attribute,
  onAdditionalPropertyUpdated,
  errors
}: {
  attribute: TextAttribute;
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
            name="max_length"
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
            onChange={(isTextarea: boolean) => onAdditionalPropertyUpdated('is_textarea', isTextarea)}
          />
        </div>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea &&
      <div className="AknFieldContainer" data-code="richTextEditor">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label"
            htmlFor="pim_enriched_entity.attribute.edit.input.is_rich_text_editor"
          >
            {__('pim_enriched_entity.attribute.edit.input.is_rich_text_editor')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Switch
            id="pim_enriched_entity.attribute.edit.input.is_rich_text_editor"
            value={attribute.isRichTextEditor}
            onChange={(richTextEditor: boolean) => onAdditionalPropertyUpdated('is_rich_text_editor', richTextEditor)}
          />
        </div>
        {getErrorsView(errors, 'richTextEditor')}
      </div>
      }
      {!attribute.isTextarea &&
        <div className="AknFieldContainer" data-code="validation-rule">
          <div className="AknFieldContainer-header">
            <label
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.attribute.edit.input.validation_rule"
            >
              {__('pim_enriched_entity.attribute.edit.input.validation_rule')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Dropdown
              ItemView={AttributeValidationRuleItemView}
              label={__('pim_enriched_entity.attribute.edit.input.validation_rule')}
              elements={getValidationRuleOptions()}
              selectedElement={(attribute.validationRule) ? attribute.validationRule : ValidationRuleOptions.Email}
              onSelectionChange={(value: DropdownElement) => onAdditionalPropertyUpdated('validation_rule', value.identifier)}
            />
          </div>
          {getErrorsView(errors, 'validationRule')}
        </div>
      }
      {(!attribute.isTextarea && attribute.validationRule === ValidationRuleOptions.RegularExpression) &&
        <div className="AknFieldContainer" data-code="regular_expression">
          <div className="AknFieldContainer-header">
            <label
              className="AknFieldContainer-label"
              htmlFor="pim_enriched_entity.attribute.edit.input.regular_expression"
            >
              {__('pim_enriched_entity.attribute.edit.input.regular_expression')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              className="AknTextField"
              id="pim_enriched_entity.attribute.edit.input.regular_expression"
              name="regular_expression"
              onChange={(event: any) => onAdditionalPropertyUpdated(event.target.name, event.target.value)}
            />
          </div>
          {getErrorsView(errors, 'regularExpression')}
        </div>
      }
    </div>
  );
};
