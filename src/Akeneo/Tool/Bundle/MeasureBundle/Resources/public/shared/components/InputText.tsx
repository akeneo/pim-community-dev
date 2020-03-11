import React, {ChangeEventHandler, useContext} from 'react';
import {ValidationError} from 'akeneomeasure/shared/model/ValidationError';
import {InputErrors} from 'akeneomeasure/shared/components/InputErrors';
import {TranslateContext} from 'akeneomeasure/shared/translate/translate-context';
import {Flag} from 'akeneomeasure/shared/components/Flag';

type InputTextProps = {
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

export const InputText = ({
  id,
  label,
  errors,
  propertyPath,
  required = false,
  flag,
  ...props
}: InputTextProps & any) => {
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
