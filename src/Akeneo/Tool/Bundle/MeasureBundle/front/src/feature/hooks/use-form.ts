import {useCallback, useState} from 'react';

type SetValue = (path: string, value: any) => void;
type ClearValues = () => void;

const useForm = <T>(defaultValues: T): [T, SetValue, ClearValues] => {
  const [state, setState] = useState<T>(defaultValues);

  const setValue = useCallback(
    (path: string, value: any) => {
      if (!(path in state)) {
        throw Error(`The field ${path} does not belong to this form.`);
      }

      setState({
        ...state,
        [path]: value,
      });
    },
    [state, setState]
  );

  const clear = useCallback(() => {
    setState(defaultValues);
  }, [setState, defaultValues]);

  return [state, setValue, clear];
};

export {useForm};
