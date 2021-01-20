import React from 'react';
import {ValidationError, InputErrors} from '@akeneo-pim-community/shared';
import {Checkbox} from 'akeneo-design-system';

type CheckboxFieldProps = {
  id: string;
  label: string;
  readOnly?: boolean;

  value: boolean;
  onChange: (value: boolean) => void;
  errors?: ValidationError[];
};

const CheckboxField = ({id, label, readOnly, value, onChange, errors}: CheckboxFieldProps) => {
  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-inputContainer">
        <label className="AknFieldContainer-label" htmlFor={id}>
          <Checkbox
            id={id}
            checked={value}
            onChange={(value: boolean | 'mixed') => onChange(true === value)}
            readOnly={readOnly}
          >
            {label}
          </Checkbox>
        </label>
      </div>
      {errors && <InputErrors errors={errors} />}
    </div>
  );
};

export {CheckboxField};
