import React from 'react';
import {Checkbox, Field, SelectInput} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {getErrorsForPath, TextField} from '@akeneo-pim-community/shared';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
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

type TextViewProps = {
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
};

const TextView = ({attribute, onAdditionalPropertyUpdated, onSubmit, errors, rights}: TextViewProps) => {
  const translate = useTranslate();

  return (
    <>
      <TextField
        label={translate('pim_asset_manager.attribute.edit.input.max_length')}
        readOnly={!rights.attribute.edit}
        value={maxLengthStringValue(attribute.maxLength)}
        onSubmit={onSubmit}
        onChange={value => {
          if (!isValidMaxLength(value)) return;

          onAdditionalPropertyUpdated('max_length', createMaxLengthFromString(value));
        }}
        errors={getErrorsForPath(errors, 'maxLength')}
      />
      <div>
        <Checkbox
          readOnly={!rights.attribute.edit}
          checked={attribute.isTextarea}
          onChange={(isTextarea: boolean) => onAdditionalPropertyUpdated('is_textarea', isTextarea)}
        >
          {translate('pim_asset_manager.attribute.edit.input.textarea')}
        </Checkbox>
        {getErrorsView(errors, 'isTextarea')}
      </div>
      {attribute.isTextarea && (
        <div>
          <Checkbox
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
        <Field label={translate('pim_asset_manager.attribute.edit.input.validation_rule')}>
          <SelectInput
            readOnly={!rights.attribute.edit}
            emptyResultLabel={translate('pim_asset_manager.result_counter', {count: 0}, 0)}
            clearable={false}
            verticalPosition="up"
            value={attribute.validationRule.toString()}
            onChange={(value: string) => onAdditionalPropertyUpdated('validation_rule', value)}
          >
            {Object.values(ValidationRuleOption).map(option => (
              <SelectInput.Option key={option} value={option}>
                {translate(`pim_asset_manager.attribute.edit.input.options.validation_rule.${option}`)}
              </SelectInput.Option>
            ))}
          </SelectInput>
          {getErrorsView(errors, 'validationRule')}
        </Field>
      )}
      {!attribute.isTextarea && attribute.validationRule === ValidationRuleOption.RegularExpression && (
        <TextField
          label={translate('pim_asset_manager.attribute.edit.input.regular_expression')}
          placeholder="/[a-z]+[0-9]*/"
          value={regularExpressionStringValue(attribute.regularExpression)}
          onChange={value =>
            onAdditionalPropertyUpdated('regular_expression', createRegularExpressionFromString(value))
          }
          onSubmit={onSubmit}
          readOnly={!rights.attribute.edit}
          errors={getErrorsForPath(errors, 'regularExpression')}
        />
      )}
    </>
  );
};

export const view = TextView;
