import {useCallback, useState} from 'react';
import {
  FormState,
  createFormState,
} from 'akeneomeasure/pages/create-measurement-family/form/create-measurement-family-form';

type SetValue = (path: string, value: string) => void;
type ClearValues = () => void;

const useCreateMeasurementFamilyState = (): [FormState, SetValue, ClearValues] => {
  const [state, setState] = useState<FormState>(createFormState());

  const setValue = useCallback(
    (path: string, value: string) => {
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
    setState(createFormState());
  }, [setState]);

  return [state, setValue, clear];
};

export {useCreateMeasurementFamilyState};
