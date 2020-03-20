import React from 'react';
import {Checkbox} from 'akeneomeasure/shared/components/Checkbox';
import styled from 'styled-components';

const Label = styled.span`
  user-select: none;
`;

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
          <Label>{label}</Label>
        </label>
      </div>
    </div>
  );
};

export {
  CheckboxField,
};
