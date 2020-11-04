import React from 'react';
import {Select2Props, Select2Value, Select2Wrapper} from './Select2Wrapper';
import {useFormContext} from 'react-hook-form';

type Props = {
  name: string;
  validation?: {
    required?: string;
    validate?: (value: any) => string | true | Promise<string | true>;
  };
  value?: Select2Value | Select2Value[];
  onChange: (value: Select2Value) => void;
} & Select2Props;

const ReactHookFormSelect2Wrapper: React.FC<Props> = props => {
  const {register, setValue, unregister, getValues} = useFormContext();
  const {name, validation, value, onChange, ...remainingProps} = props;
  const [lastKnownValue, setLastKnownValue] = React.useState<any>(value);
  const currentFormValue = getValues()[name];

  React.useEffect(() => {
    register({name}, validation);
    setValue(name, lastKnownValue);

    return () => {
      unregister(name);
    };
  }, []);

  React.useEffect(() => {
    if (currentFormValue === undefined) {
      // After the save, the value was unregistered. We have to put it back.
      register({name}, validation);
      setValue(name, lastKnownValue);
    }
  }, [currentFormValue]);

  const handleValueChange: (
    value: Select2Value | Select2Value[]
  ) => void = value => {
    setLastKnownValue(value);
    setValue(name, value);
    if (onChange) {
      onChange(value);
    }
  };

  React.useEffect(() => {
    register({name}, validation);
  }, [validation]);

  React.useEffect(() => {
    register({name}, validation);
    setValue(name, lastKnownValue);
  }, [name]);

  return Select2Wrapper({
    ...remainingProps,
    onChange: handleValueChange,
    value: lastKnownValue,
  });
};

export {ReactHookFormSelect2Wrapper};
