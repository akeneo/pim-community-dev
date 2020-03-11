import {useCallback, useState} from 'react';

type FormState = {
  family_code: string;
  family_label: string;
  standard_unit_code: string;
  standard_unit_label: string;
  standard_unit_symbol: string;
};
type SetValue = (path: string, value: string) => void;
type ClearValues = () => void;

const initialFormState = Object.freeze({
  family_code: '',
  family_label: '',
  standard_unit_code: '',
  standard_unit_label: '',
  standard_unit_symbol: '',
});

export const useCreateMeasurementFamilyState = (): [FormState, SetValue, ClearValues] => {
  const [state, setState] = useState<FormState>(initialFormState);

  const setValue = useCallback(
    (path: string, value: string) => {
      setState({
        ...state,
        [path]: value,
      });
    },
    [state, setState]
  );

  const clear = useCallback(() => {
    setState(initialFormState);
  }, [setState]);

  return [state, setValue, clear];
};
