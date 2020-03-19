import React from 'react';
import {Checkbox} from 'akeneomeasure/shared/components/Checkbox';

type CheckboxFieldProps = {
  id: string;
  label: string;

  value: boolean;
  onChange: (value: boolean) => void;
  readOnly?: boolean;
};

const CheckboxField = ({id, label, value, onChange, readOnly}: CheckboxFieldProps) => {
  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-inputContainer">
        <label className="AknFieldContainer-label" htmlFor={id}>
          <Checkbox
            id={id}
            value={value}
            onChange={onChange}
            readOnly={readOnly}
          />
          {label}
        </label>
      </div>
    </div>
  );
};

export {
  CheckboxField,
};
