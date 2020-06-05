import React from "react";
import { Select2Props, Select2Value, Select2Wrapper } from "./Select2Wrapper";
import { useFormContext } from 'react-hook-form';

type Props = {
  name: string;
  validation?: { required?: string; validate?: (value: any) => string | true };
  value?: Select2Value | Select2Value[];
  onChange: (value: Select2Value) => void;
} & Select2Props;

const ReactHookFormSelect2Wrapper: React.FC<Props> = (props) => {
  const { register, setValue, unregister, getValues } = useFormContext();
  const { name, validation, value, onChange, ...remainingProps } = props;
  const [ lastKnownValue, setLastKnownValue ] = React.useState<any>(value);
  const currentFormValue = getValues()[name];

  React.useEffect(() => {
    return () => {
      unregister(name);
    };
  }, []);

  React.useEffect(() => {
    if (currentFormValue === undefined) {
      // After the save, the value was unregistered. We have to put it back.
      register({ name }, validation);
      setValue(name, lastKnownValue);
    }
  }, [ currentFormValue ]);

  const handleValueChange: (value: Select2Value | Select2Value[]) => void = (value) => {
    setLastKnownValue(value);
    setValue(name, value);
    if (onChange) {
      onChange(value);
    }
    console.log('value changed from SELECT2!', getValues());
  };

  React.useEffect(() => {
    console.log('Validation changed', name, validation);
//    unregister(name);
    register({ name }, validation);
  }, [ validation ]);

  React.useEffect(() => {
    console.log('VAlue changed to SELECT2', value);
    setLastKnownValue(value);
  }, [ value ]);

  return Select2Wrapper({ ...remainingProps, onChange: handleValueChange, value: lastKnownValue });
};

export { ReactHookFormSelect2Wrapper };
