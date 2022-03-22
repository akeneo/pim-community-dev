import React from 'react';
import {Checkbox} from 'akeneo-design-system';
import {getErrorsForPath, TextField, useTranslate, useUserContext, ValidationError} from '@akeneo-pim-community/shared';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import {NumberAdditionalProperty, NumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {
  minValueStringValue,
  createMinValueFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {
  maxValueStringValue,
  createMaxValueFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';
import {unformatNumber, formatNumberForUILocale} from 'akeneoassetmanager/tools/format-number';

const NumberView = ({
  attribute,
  onAdditionalPropertyUpdated,
  onSubmit,
  errors,
  rights,
}: {
  attribute: NumberAttribute;
  onAdditionalPropertyUpdated: (property: string, value: NumberAdditionalProperty) => void;
  onSubmit: () => void;
  errors: ValidationError[];
  rights: {
    attribute: {
      edit: boolean;
    };
  };
}) => {
  const translate = useTranslate();
  const decimalSeparator = useUserContext().get('ui_locale_decimal_separator');

  return (
    <>
      <div>
        <Checkbox
          readOnly={!rights.attribute.edit}
          checked={attribute.decimalsAllowed}
          onChange={(decimalsAllowed: boolean) => onAdditionalPropertyUpdated('decimals_allowed', decimalsAllowed)}
        >
          {translate('pim_asset_manager.attribute.edit.input.decimals_allowed')}
        </Checkbox>
        {getErrorsView(errors, 'decimalsAllowed')}
      </div>
      <TextField
        label={translate('pim_asset_manager.attribute.edit.input.min_value')}
        value={formatNumberForUILocale(minValueStringValue(attribute.minValue), decimalSeparator)}
        onSubmit={onSubmit}
        onChange={value => {
          const cleanedNumber = unformatNumber(value, decimalSeparator);
          onAdditionalPropertyUpdated('min_value', createMinValueFromString(cleanedNumber));
        }}
        readOnly={!rights.attribute.edit}
        errors={getErrorsForPath(errors, 'minValue')}
      />
      <TextField
        label={translate('pim_asset_manager.attribute.edit.input.max_value')}
        value={formatNumberForUILocale(maxValueStringValue(attribute.maxValue), decimalSeparator)}
        onSubmit={onSubmit}
        onChange={value => {
          const cleanedNumber = unformatNumber(value, decimalSeparator);
          onAdditionalPropertyUpdated('max_value', createMaxValueFromString(cleanedNumber));
        }}
        readOnly={!rights.attribute.edit}
        errors={getErrorsForPath(errors, 'maxValue')}
      />
    </>
  );
};

export const view = NumberView;
