import { useEffect } from 'react';
import { useFormContext } from 'react-hook-form';

const useValueInitialization = (
  prefix: string,
  values: { [key: string]: any },
  validation?: { [key: string]: any },
  deps?: any[]
) => {
  const { register, setValue, watch } = useFormContext();

  const initializeValue = (prefix: string, field: string, value: any): void => {
    const name = '' === prefix ? field : `${prefix}.${field}`;
    const fieldValidation = validation ? validation[field] : {};
    register({ name }, fieldValidation);
    if (JSON.stringify(watch(name)) !== JSON.stringify(value)) {
      // Prevent the "dirty" field to be updated when updating ['camcorder'] to ['camcorder']
      setValue(name, value);
    }
  };

  useEffect(() => {
    Object.keys(values).forEach(key => {
      initializeValue(prefix, key, values[key]);
    });
  }, deps || []);
};

export { useValueInitialization };
