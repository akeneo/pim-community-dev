import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
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
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import Key from 'akeneoassetmanager/tools/key';

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

const getValidationRuleOptions = (): DropdownElement[] => {
  return Object.values(ValidationRuleOption).map((option: string) => {
    return {
      identifier: option,
      label: __(`pim_asset_manager.attribute.edit.input.options.validation_rule.${option}`),
    };
  });
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
  const inputTextClassName = `AknTextField AknTextField--light ${
    !rights.attribute.edit ? 'AknTextField--disabled' : ''
  }`;

  return (
    <React.Fragment>
      <div className="AknFieldContainer" data-code="maxLength">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.max_length">
            {__('pim_asset_manager.attribute.edit.input.max_length')}
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
      <div className="AknFieldContainer AknFieldContainer--packed" data-code="isTextarea">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--inline"
            htmlFor="pim_asset_manager.attribute.edit.input.textarea"
          >
            <Checkbox
              readOnly={!rights.attribute.edit}
              id="pim_asset_manager.attribute.edit.input.textarea"
              value={attribute.isTextarea}
              onChange={(isTextarea: boolean) => onAdditionalPropertyUpdated('is_textarea', isTextarea)}
            />
            <span
              onClick={() => {
                if (rights.attribute.edit) {
                  onAdditionalPropertyUpdated('is_textarea', !attribute.isTextarea);
                }
              }}
            >
              {__('pim_asset_manager.attribute.edit.input.textarea')}
            </span>
          </label>
        </div>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea && (
        <div className="AknFieldContainer AknFieldContainer--packed" data-code="isRichTextEditor">
          <div className="AknFieldContainer-header">
            <label
              className="AknFieldContainer-label AknFieldContainer-label--inline"
              htmlFor="pim_asset_manager.attribute.edit.input.is_rich_text_editor"
            >
              <Checkbox
                id="pim_asset_manager.attribute.edit.input.is_rich_text_editor"
                readOnly={!rights.attribute.edit}
                value={attribute.isRichTextEditor}
                onChange={(isrichTextEditor: boolean) =>
                  onAdditionalPropertyUpdated('is_rich_text_editor', isrichTextEditor)
                }
              />
              <span
                onClick={() => {
                  onAdditionalPropertyUpdated('is_rich_text_editor', !attribute.isRichTextEditor);
                }}
              >
                {__('pim_asset_manager.attribute.edit.input.is_rich_text_editor')}
              </span>
            </label>
          </div>
          {getErrorsView(errors, 'richTextEditor')}
        </div>
      )}
      {!attribute.isTextarea && (
        <div className="AknFieldContainer" data-code="validationRule">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.validation_rule">
              {__('pim_asset_manager.attribute.edit.input.validation_rule')}
            </label>
          </div>
          <div className="AknFieldContainer-inputContainer">
            <Dropdown
              readOnly={!rights.attribute.edit}
              ItemView={AttributeValidationRuleItemView}
              label={__('pim_asset_manager.attribute.edit.input.validation_rule')}
              elements={getValidationRuleOptions()}
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
        <div className="AknFieldContainer" data-code="regularExpression">
          <div className="AknFieldContainer-header AknFieldContainer-header--light">
            <label
              className="AknFieldContainer-label"
              htmlFor="pim_asset_manager.attribute.edit.input.regular_expression"
            >
              {__('pim_asset_manager.attribute.edit.input.regular_expression')}
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
    </React.Fragment>
  );
};

export const view = TextView;
