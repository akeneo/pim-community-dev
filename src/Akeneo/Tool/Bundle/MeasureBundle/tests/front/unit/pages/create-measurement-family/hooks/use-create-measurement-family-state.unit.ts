'use strict';

import '@testing-library/jest-dom/extend-expect';
import {act, renderHook} from '@testing-library/react-hooks';
import {useCreateMeasurementFamilyState} from 'akeneomeasure/pages/create-measurement-family/hooks/use-create-measurement-family-state';

const initialFields = Object.freeze({
  family_code: '',
  family_label: '',
  standard_unit_code: '',
  standard_unit_label: '',
  standard_unit_symbol: '',
});

describe('useCreateMeasurementFamilyState', () => {
  test('It retrieves a default state with empty values', () => {
    const {result} = renderHook(() => useCreateMeasurementFamilyState());
    let [fields] = result.current;

    expect(fields).toEqual(initialFields);
  });

  test('It can update a field', () => {
    const {result} = renderHook(() => useCreateMeasurementFamilyState());
    let [, setFieldValue] = result.current;

    act(() => {
      setFieldValue('family_code', 'foo');
    });

    let [fields] = result.current;
    expect(fields).toEqual({
      ...initialFields,
      family_code: 'foo',
    });
  });

  test('It can clear all the fields', () => {
    const {result} = renderHook(() => useCreateMeasurementFamilyState());
    let [, setFieldValue, clearValues] = result.current;

    act(() => {
      setFieldValue('family_code', 'foo');
      clearValues();
    });

    let [fields] = result.current;
    expect(fields).toEqual(initialFields);
  });

  test('It throw an error if the field is unknown', () => {
    const {result} = renderHook(() => useCreateMeasurementFamilyState());
    let [, setFieldValue] = result.current;

    expect(() => {
      act(() => {
        setFieldValue('unknown_field_name', 'foo');
      });
    }).toThrow();
  });
});
