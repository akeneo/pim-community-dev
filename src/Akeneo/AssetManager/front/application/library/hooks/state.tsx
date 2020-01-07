import * as React from 'react';

export const useStoredState = function<T>(
  name: string,
  defaultValue: T,
  afterSet?: (newValue: T) => void
): [T, (newValue: T) => void, (name: string) => void] {
  const [value, setValue] = React.useState(defaultValue);
  const [firstLoad, setFirstLoad] = React.useState(true);

  const setValueAndStore = (newValue: T) => {
    if (value === newValue) return;

    localStorage.setItem(name, JSON.stringify(newValue));
    setValue(newValue);
    afterSet && afterSet(newValue);
  };

  const loadFromStorage = (name: string) => {
    const localeStorageValue = localStorage.getItem(name);
    if (null !== localeStorageValue) {
      setValue(JSON.parse(localeStorageValue));
    } else {
      setValue(defaultValue);
    }
  };

  if (firstLoad) {
    loadFromStorage(name);
    setFirstLoad(false);
  }

  return [value, setValueAndStore, loadFromStorage];
};
