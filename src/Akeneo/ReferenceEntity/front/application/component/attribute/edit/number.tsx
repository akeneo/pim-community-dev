import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import Checkbox from 'akeneoreferenceentity/application/component/app/checkbox';
import {NumberAdditionalProperty, NumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';
import {IsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';
import {MinValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/min-value';
import {MaxValue} from 'akeneoreferenceentity/domain/model/attribute/type/number/max-value';
import Key from 'akeneoreferenceentity/tools/key';

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
      <div className="AknFieldContainer AknFieldContainer--packed" data-code="isDecimal">
        <div className="AknFieldContainer-header">
          <label
            className="AknFieldContainer-label AknFieldContainer-label--inline"
            htmlFor="pim_reference_entity.attribute.edit.input.is_decimal"
          >
            <Checkbox
              readOnly={!rights.attribute.edit}
              id="pim_reference_entity.attribute.edit.input.is_decimal"
              value={attribute.isDecimal.booleanValue()}
              onChange={(isDecimal: boolean) =>
                onAdditionalPropertyUpdated('is_decimal', IsDecimal.createFromBoolean(isDecimal))
              }
            />
            <span
              onClick={() => {
                if (rights.attribute.edit) {
                  onAdditionalPropertyUpdated(
                    'is_decimal',
                    IsDecimal.createFromBoolean(!attribute.isDecimal.booleanValue())
                  );
                }
              }}
            >
              {__('pim_reference_entity.attribute.edit.input.is_decimal')}
            </span>
          </label>
        </div>
        {getErrorsView(errors, 'isDecimal')}
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
            value={attribute.minValue.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!MinValue.isValid(event.currentTarget.value)) {
                event.currentTarget.value = attribute.minValue.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('min_value', MinValue.createFromString(event.currentTarget.value));
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
            value={attribute.maxValue.stringValue()}
            onKeyPress={(event: React.KeyboardEvent<HTMLInputElement>) => {
              if (Key.Enter === event.key) onSubmit();
            }}
            onChange={(event: React.FormEvent<HTMLInputElement>) => {
              if (!MaxValue.isValid(event.currentTarget.value)) {
                event.currentTarget.value = attribute.maxValue.stringValue();
                event.preventDefault();
                return;
              }

              onAdditionalPropertyUpdated('max_value', MaxValue.createFromString(event.currentTarget.value));
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
