import {useState, useEffect, SetStateAction, Dispatch} from 'react';

const useSessionStorageState = <StateType>(
  defaultValue: StateType,
  key: string
): [StateType, Dispatch<SetStateAction<StateType>>] => {
  const storageValue = sessionStorage.getItem(key) as string;
  const [value, setValue] = useState<StateType>(null !== storageValue ? JSON.parse(storageValue) : defaultValue);

  useEffect(() => {
    sessionStorage.setItem(key, JSON.stringify(value));
  }, [value]);

  return [value, setValue];
};

export {useSessionStorageState};
