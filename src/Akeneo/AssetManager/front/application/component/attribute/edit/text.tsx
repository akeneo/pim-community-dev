import React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import Dropdown, {DropdownElement} from 'akeneoassetmanager/application/component/app/dropdown';
import {
  TextAttribute,
  TextAdditionalProperty,
  isValidMaxLength,
  maxLengthStringValue,
  createMaxLengthFromString,
  createRegularExpressionFromString,
  regularExpressionStringValue,
  ValidationRuleOption,
} from 'akeneoassetmanager/domain/model/attribute/type/text';
import {Key, Checkbox} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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
        if (Key.Space === event.key) onClick(element);
      }}
      tabIndex={isOpen ? 0 : -1}
    >
      <span>{element.label}</span>
    </div>
  );
};

const TextView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: TextAttribute;
  onAdditionalPropertyUpdated: (property: string, value: TextAdditionalProperty) => void;
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
  const inputTextClassName = `AknTextField AknTextField--light ${
    !rights.attribute.edit ? 'AknTextField--disabled' : ''
  }`;

  return (
    <>
      <div className="AknFieldContainer--packed" data-code="maxLength">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.max_length">
            {translate('pim_asset_manager.attribute.edit.input.max_length')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.max_length"
            name="max_length"
            readOnly={!rights.attribute.edit}
            value={maxLengthStringValue(attribute.maxLength)}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!isValidMaxLength(event.currentTarget.value)) {
                event.currentTarget.value = maxLengthStringValue(attribute.maxLength);
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_length', createMaxLengthFromString(event.currentTarget.value));
            }}
          />
        </div>
        {getErrorsView(errors, 'maxLength')}
      </div>
      <div data-code="isTextarea">
        <Checkbox
          readOnly={!rights.attribute.edit}
          id="pim_asset_manager.attribute.edit.input.textarea"
          checked={attribute.isTextarea}
          onChange={(isTextarea: boolean) => onAdditionalPropertyUpdated('is_textarea', isTextarea)}
        >
          {translate('pim_asset_manager.attribute.edit.input.textarea')}
        </Checkbox>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea && (
        <div data-code="isRichTextEditor">
          <Checkbox
            id="pim_asset_manager.attribute.edit.input.is_rich_text_editor"
            readOnly={!rights.attribute.edit}
            checked={attribute.isRichTextEditor}
            onChange={(isRichTextEditor: boolean) =>
              onAdditionalPropertyUpdated('is_rich_text_editor', isRichTextEditor)
            }
          >
            {translate('pim_asset_manager.attribute.edit.input.is_rich_text_editor')}
          </Checkbox>
          {getErrorsView(errors, 'richTextEditor')}
        </div>
      )}
      {!attribute.isTextarea && (
        <div className="AknFieldContainer--packed" data-code="validationRule">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.validation_rule">
              {translate('pim_asset_manager.attribute.edit.input.validation_rule')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Dropdown
              readOnly={!rights.attribute.edit}
              ItemView={AttributeValidationRuleItemView}
              label={translate('pim_asset_manager.attribute.edit.input.validation_rule')}
              elements={Object.values(ValidationRuleOption).map((option: string) => {
                return {
                  identifier: option,
                  label: translate(`pim_asset_manager.attribute.edit.input.options.validation_rule.${option}`),
                };
              })}
              selectedElement={attribute.validationRule}
              onSelectionChange={(value: DropdownElement) =>
                onAdditionalPropertyUpdated('validation_rule', value.identifier)
              }
              isOpenUp={true}
            />
          </div>
          {getErrorsView(errors, 'validationRule')}
        </div>
      )}
      {!attribute.isTextarea && attribute.validationRule === ValidationRuleOption.RegularExpression && (
        <div className="AknFieldContainer--packed" data-code="regularExpression">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.attribute.edit.input.regular_expression"
            >
              {translate('pim_asset_manager.attribute.edit.input.regular_expression')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <input
              type="text"
              autoComplete="off"
              className={inputTextClassName}
              id="pim_asset_manager.attribute.edit.input.regular_expression"
              name="regular_expression"
              placeholder="/[a-z]+[0-9]*/"
              value={regularExpressionStringValue(attribute.regularExpression)}
              onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
                if (Key.Enter === event.key) onSubmit();
              }}
              onChange={(event: React.FormEvent<HTMLInputElement>) =>
                onAdditionalPropertyUpdated(
                  'regular_expression',
                  createRegularExpressionFromString(event.currentTarget.value)
                )
              }
            />
          </div>
          {getErrorsView(errors, 'regularExpression')}
        </div>
      )}
    </>
  );
};

export const view = TextView;
