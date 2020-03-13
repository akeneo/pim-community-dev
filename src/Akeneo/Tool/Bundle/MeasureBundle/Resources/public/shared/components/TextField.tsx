import React, {ChangeEventHandler, useContext} from 'react';
import {ValidationError} from 'akeneomeasure/model/validation-error';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/context/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';

type TextFieldProps = {
  id: string;
  name: string;
  label: string;
  locale?: string;
  required?: boolean;
  flag?: string;

  value: string;
  onChange: ChangeEventHandler<Element>;

  errors?: ValidationError[];
  propertyPath?: string;
}

export const TextField = ({
  id,
  label,
  errors,
  propertyPath,
  required = false,
  flag,
  ...props
}: TextFieldProps & any) => {
  const __ = useContext(TranslateContext);

  return (
    <div className="AknFieldContainer">
      <div className="AknFieldContainer-header">
        <label className="AknFieldContainer-label" htmlFor={id}>
          {label} {required && __('measurements.form.required_suffix')}
        </label>
        {flag && <Flag localeCode={flag}/>}
      </div>
      <div className="AknFieldContainer-inputContainer">
        <input
          type="text"
          autoComplete="off"
          className="AknTextField"
          {...props}
        />
      </div>
      {errors && <InputErrors errors={errors} propertyPath={propertyPath}/>}
    </div>
  )
};
