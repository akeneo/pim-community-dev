import React from 'react';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
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
import {Key, Checkbox} from 'akeneo-design-system';
import {unformatNumber, formatNumberForUILocale} from 'akeneoassetmanager/tools/format-number';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';

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
      <div data-code="decimalsAllowed">
        <Checkbox
          readOnly={!rights.attribute.edit}
          id="pim_asset_manager.attribute.edit.input.decimals_allowed"
          checked={attribute.decimalsAllowed}
          onChange={(decimalsAllowed: boolean) => onAdditionalPropertyUpdated('decimals_allowed', decimalsAllowed)}
        >
          {translate('pim_asset_manager.attribute.edit.input.decimals_allowed')}
        </Checkbox>
        {getErrorsView(errors, 'decimalsAllowed')}
      </div>
      <div className="AknFieldContainer--packed" data-code="minValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.min_value">
            {translate('pim_asset_manager.attribute.edit.input.min_value')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.min_value"
            name="min_value"
            value={formatNumberForUILocale(minValueStringValue(attribute.minValue))}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              const cleanedNumber = unformatNumber(event.currentTarget.value);
              onAdditionalPropertyUpdated('min_value', createMinValueFromString(cleanedNumber));
            }}
            readOnly={!rights.attribute.edit}
          />
        </div>
        {getErrorsView(errors, 'minValue')}
      </div>
      <div className="AknFieldContainer--packed" data-code="maxValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.max_value">
            {translate('pim_asset_manager.attribute.edit.input.max_value')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_asset_manager.attribute.edit.input.max_value"
            name="max_value"
            value={formatNumberForUILocale(maxValueStringValue(attribute.maxValue))}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              const cleanedNumber = unformatNumber(event.currentTarget.value);
              onAdditionalPropertyUpdated('max_value', createMaxValueFromString(cleanedNumber));
            }}
            readOnly={!rights.attribute.edit}
          />
        </div>
        {getErrorsView(errors, 'maxValue')}
      </div>
    </>
  );
};

export const view = NumberView;
