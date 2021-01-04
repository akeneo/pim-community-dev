import {useCallback, useState} from 'react';

const useBooleanState = (defaultValue: boolean = false) => {
  const [value, setValue] = useState<boolean>(defaultValue);

  const setTrue = useCallback(() => setValue(true), []);
  const setFalse = useCallback(() => setValue(false), []);

  return [value, setTrue, setFalse] as const;
};

export {useBooleanState};
