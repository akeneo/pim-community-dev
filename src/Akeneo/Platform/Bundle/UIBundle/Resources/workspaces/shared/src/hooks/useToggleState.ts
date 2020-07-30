import {EffectCallback, useCallback, useState} from 'react';

const useToggleState = (defaultValue: boolean): [boolean, EffectCallback, EffectCallback] => {
  const [value, setValue] = useState<boolean>(defaultValue);

  const setTrue = useCallback(() => setValue(true), [setValue, value]);
  const setFalse = useCallback(() => setValue(false), [setValue, value]);

  return [value, setTrue, setFalse];
};

export {useToggleState};
