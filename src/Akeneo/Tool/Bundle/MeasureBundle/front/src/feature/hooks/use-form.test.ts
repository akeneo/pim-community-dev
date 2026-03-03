import {act, renderHook} from '@testing-library/react-hooks';
import {useForm} from './use-form';

type Form = {
  code: string;
  label: string;
};

const defaultValues = Object.freeze({
  code: '',
  label: '',
});

test('It retrieves a default state with empty values', () => {
  const {result} = renderHook(() => useForm<Form>(defaultValues));
  let [values] = result.current;

  expect(values).toEqual(defaultValues);
});

test('It can update a field', () => {
  const {result} = renderHook(() => useForm<Form>(defaultValues));
  let [, setValue] = result.current;

  act(() => {
    setValue('code', 'foo');
  });

  let [values] = result.current;
  expect(values).toEqual({
    ...defaultValues,
    code: 'foo',
  });
});

test('It can clear all the fields', () => {
  const {result} = renderHook(() => useForm<Form>(defaultValues));
  let [, setValue, clearValues] = result.current;

  act(() => {
    setValue('code', 'foo');
    setValue('label', 'bar');
    clearValues();
  });

  let [values] = result.current;
  expect(values).toEqual(defaultValues);
});

test('It throw an error if the field is unknown', () => {
  const {result} = renderHook(() => useForm<Form>(defaultValues));
  let [, setValue] = result.current;

  expect(() => {
    act(() => {
      setValue('unknown_field_name', 'foo');
    });
  }).toThrow();
});
