import * as React from 'react';

export const useStoredState = function<T>(
  name: string,
  defaultValue: T
): [T, (newValue: T) => void] {
  const loadValueFromLocaleStorage = (): T => {
    try {
      const item = localStorage.getItem(name);

      return null !== item ? JSON.parse(item) : defaultValue;
    } catch (error) {
      return defaultValue;
    }
  };

  const [value, setValue] = React.useState<T>(loadValueFromLocaleStorage);
  const [key, setKey] = React.useState<string>(name);

  const setAndStoreValue = (value: T) => {
    try {
      setValue(value);
      localStorage.setItem(name, JSON.stringify(value));
    } catch (error) {
      console.error(error);
    }
  };

  // When the key has changed, we need to reload because we are now using
  // a different locale storage item
  if (key !== name) {
    setKey(name);
    setValue(loadValueFromLocaleStorage());
  }

  return [value, setAndStoreValue];
};
