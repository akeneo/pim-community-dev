import React from 'react';
import { useFormContext } from 'react-hook-form';

const useRegisterConst = (name: string, value: any) => {
  const { register, setValue, unregister, getValues } = useFormContext();
  const currentFormValue = getValues()[name];

  React.useEffect(() => {
    return () => {
      unregister(name);
    };
  }, []);

  React.useEffect(() => {
    if (currentFormValue === undefined) {
      register({ name });
      setValue(name, value);
    }
  }, [currentFormValue]);
};

const useRegisterConsts = (
  consts: { [name: string]: any },
  prefix?: string
) => {
  Object.keys(consts).forEach(name => {
    const fullName = prefix ? `${prefix}.${name}` : name;
    // eslint-disable-next-line react-hooks/rules-of-hooks
    useRegisterConst(fullName, consts[name]);
  });
};

export { useRegisterConst, useRegisterConsts };
