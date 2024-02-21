import React, {ReactNode, ReactElement, cloneElement, isValidElement, Children} from 'react';
import {SelectProps, Select} from './Select';

type FormValue = {
  [key: string]: string;
};

type FormProps = {
  children?: ReactNode;
  value: FormValue;
  onChange: (value: FormValue) => void;
};

const Form = ({value, onChange, children}: FormProps) => {
  return (
    <>
      {Children.map(children, (child, index) => {
        if (!isValidElement<SelectProps>(child) || child.type !== Select) {
          return child;
        }

        const selects = Children.toArray(children).filter(
          child => isValidElement<SelectProps>(child) && child.type === Select
        );
        const previousSelect = selects[index - 1] as ReactElement<SelectProps>;

        return cloneElement<SelectProps>(child, {
          onChange: (newValue: string) => onChange({...value, [child.props.name]: newValue}),
          isVisible: !(0 < index && undefined !== previousSelect && undefined === value[previousSelect.props.name]),
          value: undefined !== value[child.props.name] ? value[child.props.name] : null,
        });
      })}
    </>
  );
};

export {Form, FormValue};
