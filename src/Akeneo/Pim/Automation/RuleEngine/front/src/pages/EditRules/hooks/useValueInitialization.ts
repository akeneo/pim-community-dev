import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

const useValueInitialization = (
  prefix: string,
  values: { [key: string]: any },
  validation?: { [key: string]: any },
  deps?: any[],
) => {
  const { register, setValue } = useFormContext();

  const initializeValue = (prefix: string, field: string, value: any): void => {
    const name = `${prefix}.${field}`;
    const fieldValidation = validation ? validation[field] : {};
    register({ name }, fieldValidation);
    setValue(name, value);
  };

  useEffect(() => {
    Object.keys(values).forEach(key => {
      initializeValue(prefix, key, values[key]);
    });
  }, deps || []);
};

export { useValueInitialization };
