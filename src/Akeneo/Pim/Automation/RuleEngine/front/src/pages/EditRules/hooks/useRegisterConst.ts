import React from 'react';
import { useFormContext } from 'react-hook-form';

const useRegisterConst = (name: string, value: any, prefix?: string) => {
  const { register, setValue, unregister, getValues } = useFormContext();
  const fullName = prefix ? `${prefix}.${name}` : name;
  const currentFormValue = getValues()[fullName];

  React.useEffect(() => {
    return () => {
      unregister(fullName);
    };
  }, []);

  React.useEffect(() => {
    if (currentFormValue === undefined) {
      register({ name: fullName });
      setValue(fullName, value);
    }
  }, [currentFormValue]);
};

const useRegisterConsts = (
  consts: { [name: string]: any },
  prefix?: string
) => {
  Object.keys(consts).forEach(name => {
    // eslint-disable-next-line react-hooks/rules-of-hooks
    useRegisterConst(name, consts[name], prefix);
  });
};

export { useRegisterConst, useRegisterConsts };
