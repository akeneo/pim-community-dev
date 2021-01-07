import * as React from 'react';
import __ from 'akeneoassetmanager/tools/translator';
import {ValidationError} from 'akeneoassetmanager/domain/model/validation-error';
import {getErrorsView} from 'akeneoassetmanager/application/component/app/validation-error';
import Checkbox from 'akeneoassetmanager/application/component/app/checkbox';
import {NumberAdditionalProperty, NumberAttribute} from 'akeneoassetmanager/domain/model/attribute/type/number';
import {
  minValueStringValue,
  createMinValueFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/number/min-value';
import {
  maxValueStringValue,
  createMaxValueFromString,
} from 'akeneoassetmanager/domain/model/attribute/type/number/max-value';
import {Key} from 'akeneo-design-system';
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
      <div className="AknFieldContainer AknFieldContainer--packed" data-code="decimalsAllowed">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--inline"
            htmlFor="pim_asset_manager.attribute.edit.input.decimals_allowed"
          >
            <Checkbox
              readOnly={!rights.attribute.edit}
              id="pim_asset_manager.attribute.edit.input.decimals_allowed"
              value={attribute.decimalsAllowed}
              onChange={(decimalsAllowed: boolean) => onAdditionalPropertyUpdated('decimals_allowed', decimalsAllowed)}
            />
            <span
              onClick={() => {
                if (rights.attribute.edit) {
                  onAdditionalPropertyUpdated('decimals_allowed', !attribute.decimalsAllowed);
                }
              }}
            >
              {__('pim_asset_manager.attribute.edit.input.decimals_allowed')}
            </span>
          </label>
        </div>
        {getErrorsView(errors, 'decimalsAllowed')}
      </div>
      <div className="AknFieldContainer" data-code="minValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.min_value">
            {__('pim_asset_manager.attribute.edit.input.min_value')}
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
      <div className="AknFieldContainer" data-code="maxValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_asset_manager.attribute.edit.input.max_value">
            {__('pim_asset_manager.attribute.edit.input.max_value')}
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
    </React.Fragment>
  );
};

export const view = NumberView;
