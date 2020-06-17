import React from 'react';
import { useFormContext } from 'react-hook-form';

const useUnregisterAtUnmount = (name: string) => {
  const { unregister } = useFormContext();

  React.useEffect(() => {
    return () => {
      unregister(name);
    };
  }, []);
};

const useUnregisterAllAtUnmount = (names: string[], prefix?: string) => {
  const { unregister } = useFormContext();

  React.useEffect(() => {
    return () => {
      names.forEach(name => {
        const fullName = prefix ? `${prefix}.${name}` : name;
        unregister(fullName);
      });
    };
  }, []);
};

export { useUnregisterAtUnmount, useUnregisterAllAtUnmount };
