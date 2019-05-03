import * as React from 'react';
import __ from 'akeneoreferenceentity/tools/translator';
import ValidationError from 'akeneoreferenceentity/domain/model/validation-error';
import {getErrorsView} from 'akeneoreferenceentity/application/component/app/validation-error';
import Checkbox from 'akeneoreferenceentity/application/component/app/checkbox';
import {IsDecimal} from 'akeneoreferenceentity/domain/model/attribute/type/number/is-decimal';
import {NumberAdditionalProperty, NumberAttribute} from 'akeneoreferenceentity/domain/model/attribute/type/number';

const NumberView = ({
  attribute,
  onAdditionalPropertyUpdated,
  errors,
  rights,
}: {
  attribute: NumberAttribute;
  onAdditionalPropertyUpdated: (property: string, value: NumberAdditionalProperty) => void;
  errors: ValidationError[];
  rights: {
    attribute: {
      create: boolean;
      edit: boolean;
      delete: boolean;
    };
  };
}) => {
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
    </React.Fragment>
  );
};

export const view = NumberView;
