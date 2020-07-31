import {useState, useEffect, SetStateAction, Dispatch} from 'react';

const useStorageState = <StateType>(
  defaultValue: StateType,
  key: string
): [StateType, Dispatch<SetStateAction<StateType>>] => {
  const storageValue = localStorage.getItem(key) as string;
  const [value, setValue] = useState<StateType>(null !== storageValue ? JSON.parse(storageValue) : defaultValue);

  useEffect(() => {
    localStorage.setItem(key, JSON.stringify(value));
  }, [value]);

  return [value, setValue];
};

export {useStorageState};
