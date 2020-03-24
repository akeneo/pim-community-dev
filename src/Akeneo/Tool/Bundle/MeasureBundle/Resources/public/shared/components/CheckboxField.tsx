import React from 'react';
import {Checkbox} from 'akeneomeasure/shared/components/Checkbox';
import styled from 'styled-components';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {ValidationError} from 'akeneomeasure/model/validation-error';

const Label = styled.span`
  user-select: none;
`;

type CheckboxFieldProps = {
  id: string;
  label: string;
  readOnly?: boolean;

  value: boolean;
  onChange: (value: boolean) => void;
  errors?: ValidationError[];
};

const CheckboxField = ({
  id,
  label,
  readOnly,
  value,
  onChange,
  errors,
}: CheckboxFieldProps) => {
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
      {errors && <InputErrors errors={errors} />}
    </div>
  );
};

export {CheckboxField};
