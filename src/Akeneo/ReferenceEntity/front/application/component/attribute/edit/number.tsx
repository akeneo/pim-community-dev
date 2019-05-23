import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import Checkbox from 'akeneoreferenceentity/application/component/app/checkbox';
import {NumberAdditionalProperty, NumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {DecimalsAllowed} from 'akeneoreferenceentity/domain/model/attribute/type/number/decimals-allowed';
import {MinValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/min-value';
import {MaxValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/max-value';
import Key from 'akeneoreferenceentity/tools/key';
import {unformatNumber, formatNumberForUILocale} from 'akeneoreferenceentity/tools/format-number';

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
            htmlFor="pim_reference_entity.attribute.edit.input.decimals_allowed"
          >
            <Checkbox
              readOnly={!rights.attribute.edit}
              id="pim_reference_entity.attribute.edit.input.decimals_allowed"
              value={attribute.decimalsAllowed.booleanValue()}
              onChange={(decimalsAllowed: boolean) =>
                onAdditionalPropertyUpdated('decimals_allowed', DecimalsAllowed.createFromBoolean(decimalsAllowed))
              }
            />
            <span
              onClick={() => {
                if (rights.attribute.edit) {
                  onAdditionalPropertyUpdated(
                    'decimals_allowed',
                    DecimalsAllowed.createFromBoolean(!attribute.decimalsAllowed.booleanValue())
                  );
                }
              }}
            >
              {__('pim_reference_entity.attribute.edit.input.decimals_allowed')}
            </span>
          </label>
        </div>
        {getErrorsView(errors, 'decimalsAllowed')}
      </div>
      <div className="AknFieldContainer" data-code="minValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.min_value">
            {__('pim_reference_entity.attribute.edit.input.min_value')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_reference_entity.attribute.edit.input.min_value"
            name="min_value"
            value={formatNumberForUILocale(attribute.minValue.stringValue())}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              const cleanedNumber = unformatNumber(event.currentTarget.value);
              onAdditionalPropertyUpdated('min_value', MinValue.createFromString(cleanedNumber));
            }}
            readOnly={!rights.attribute.edit}
          />
        </div>
        {getErrorsView(errors, 'minValue')}
      </div>
      <div className="AknFieldContainer" data-code="maxValue">
        <div className="AknFieldContainer-header AknFieldContainer-header--light">
          <label className="AknFieldContainer-label" htmlFor="pim_reference_entity.attribute.edit.input.max_value">
            {__('pim_reference_entity.attribute.edit.input.max_value')}
          </label>
        </div>
        <div className="AknFieldContainer-inputContainer">
          <input
            type="text"
            autoComplete="off"
            className={inputTextClassName}
            id="pim_reference_entity.attribute.edit.input.max_value"
            name="max_value"
            value={formatNumberForUILocale(attribute.maxValue.stringValue())}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              const cleanedNumber = unformatNumber(event.currentTarget.value);
              onAdditionalPropertyUpdated('max_value', MaxValue.createFromString(cleanedNumber));
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
