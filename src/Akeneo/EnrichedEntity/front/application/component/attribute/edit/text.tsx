import * as React from 'react';
import __ from 'akeneoenrichedentity/tools/translator';
import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoenrichedentity/application/component/app/validation-error';
import Switch from 'akeneoenrichedentity/application/component/app/switch';
import {AdditionalProperty} from 'akeneoenrichedentity/domain/model/attribute/attribute';
import Dropdown, {DropdownElement} from 'akeneoenrichedentity/application/component/app/dropdown';
import {TextAttribute} from 'akeneoenrichedentity/domain/model/attribute/type/text';
import {RegularExpression} from 'akeneoenrichedentity/domain/model/attribute/type/text/regular-expression';
import {
  ValidationRuleOption,
  ValidationRule,
} from 'akeneoenrichedentity/domain/model/attribute/type/text/validation-rule';
import {IsRichTextEditor} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-rich-text-editor';
import {IsTextarea} from 'akeneoenrichedentity/domain/model/attribute/type/text/is-textarea';
import {MaxLength} from 'akeneoenrichedentity/domain/model/attribute/type/text/max-length';

const AttributeValidationRuleItemView = ({
  isOpen,
  element,
  isActive,
  onClick,
}: {
  isOpen: boolean;
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
      tabIndex={isOpen ? 0 : -1}
    >
      <span>{element.label}</span>
    </div>
  );
};

const getValidationRuleOptions = (): DropdownElement[] => {
  return Object.values(ValidationRuleOption).map((option: string) => {
    return {
      identifier: option,
      label: __(`pim_enriched_entity.attribute.edit.input.options.validation_rule.${option}`),
    };
  });
};

export default ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
}: {
  attribute: TextAttribute;
  onAdditionalPropertyUpdated: (property: string, value: AdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
}) => {
  return (
    <React.Fragment>
      <div className="AknFieldContainer" data-code="maxLength">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_enriched_entity.attribute.edit.input.max_length">
            {__('pim_enriched_entity.attribute.edit.input.max_length')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            className="AknTextField AknTextField--light"
            id="pim_enriched_entity.attribute.edit.input.max_length"
            name="max_length"
            value={attribute.maxLength.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if ('Enter' === event.key) {
                onSubmit();
              }
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!MaxLength.isValid(event.currentTarget.value)) {
                event.currentTarget.value = attribute.maxLength.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_length', MaxLength.createFromString(event.currentTarget.value));
            }}
          />
        </div>
        {getErrorsView(errors, 'maxLength')}
      </div>
      <div className="AknFieldContainer" data-code="isTextarea">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_enriched_entity.attribute.edit.input.textarea">
            {__('pim_enriched_entity.attribute.edit.input.textarea')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <Switch
            id="pim_enriched_entity.attribute.edit.input.textarea"
            value={attribute.isTextarea.booleanValue()}
            onChange={(isTextarea: boolean) =>
              onAdditionalPropertyUpdated('is_textarea', IsTextarea.createFromBoolean(isTextarea))
            }
          />
        </div>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea.booleanValue() && (
        <div className="AknFieldContainer" data-code="isRichTextEditor">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
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
              value={attribute.isRichTextEditor.booleanValue()}
              onChange={(isrichTextEditor: boolean) =>
                onAdditionalPropertyUpdated('is_rich_text_editor', IsRichTextEditor.createFromBoolean(isrichTextEditor))
              }
            />
          </div>
          {getErrorsView(errors, 'richTextEditor')}
        </div>
      )}
      {!attribute.isTextarea.booleanValue() && (
        <div className="AknFieldContainer" data-code="validationRule">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
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
              selectedElement={attribute.validationRule.stringValue()}
              onSelectionChange={(value: DropdownElement) =>
                onAdditionalPropertyUpdated('validation_rule', ValidationRule.createFromString(value.identifier))
              }
            />
          </div>
          {getErrorsView(errors, 'validationRule')}
        </div>
      )}
      {!attribute.isTextarea.booleanValue() &&
        attribute.validationRule.stringValue() === ValidationRuleOption.RegularExpression && (
          <div className="AknFieldContainer" data-code="regularExpression">
            <div className="AknFieldContainer-header AknFieldContainer-header--light">
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
                className="AknTextField AknTextField--light"
                id="pim_enriched_entity.attribute.edit.input.regular_expression"
                name="regular_expression"
                placeholder="/[a-z]+[0-9]*/"
                value={attribute.regularExpression.stringValue()}
                onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                  if ('Enter' === event.key) {
                    onSubmit();
                  }
                }}
                onChange={(event: React.FormEvent<HTMLInputElement>) =>
                  onAdditionalPropertyUpdated(
                    'regular_expression',
                    RegularExpression.createFromString(event.currentTarget.value)
                  )
                }
              />
            </div>
            {getErrorsView(errors, 'regularExpression')}
          </div>
        )}
    </React.Fragment>
  );
};
