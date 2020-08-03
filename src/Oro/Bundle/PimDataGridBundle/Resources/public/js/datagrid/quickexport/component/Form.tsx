import React, {ReactNode, ReactNodeArray, ReactElement, cloneElement, isValidElement, Children} from 'react';
import {SelectProps} from './Select';

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
        if (!isValidElement<SelectProps>(child)) {
          return child;
        }

        const options = children as ReactNodeArray;
        const previousOption = options[index - 1] as ReactElement<SelectProps>;

        return cloneElement<SelectProps>(child, {
          onChange: (newValue: string) => onChange({...value, [child.props.name]: newValue}),
          isVisible: !(0 < index && undefined !== previousOption && undefined === value[previousOption.props.name]),
          value: undefined !== value[child.props.name] ? value[child.props.name] : null,
        });
      })}
    </>
  );
};

export {Form, FormValue};
