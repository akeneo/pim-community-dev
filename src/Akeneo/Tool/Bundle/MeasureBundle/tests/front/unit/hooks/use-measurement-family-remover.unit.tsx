'use strict';

import React from 'react';
import '@testing-library/jest-dom/extend-expect';
import {renderHook} from '@testing-library/react-hooks';
import {
  useMeasurementFamilyRemover,
  MeasurementFamilyRemoverResult,
} from 'akeneomeasure/hooks/use-measurement-family-remover';
import {DependenciesProvider} from '@akeneo-pim-community/shared';

const wrapper = ({children}) => <DependenciesProvider>{children}</DependenciesProvider>;

afterEach(() => {
  global.fetch && global.fetch.mockClear();
  delete global.fetch;
});

test('It returns a success response when removing', async () => {
  const fetchMock = jest.fn().mockImplementation(() => ({
    status: 204,
  }));
  global.fetch = fetchMock;

  const {result} = renderHook(() => useMeasurementFamilyRemover(), {wrapper});
  const remove = result.current;

  expect(await remove('custom_metric')).toEqual(MeasurementFamilyRemoverResult.Success);
  expect(fetchMock).toHaveBeenCalledWith('akeneo_measurements_measurement_family_delete_rest', {
    headers: [['X-Requested-With', 'XMLHttpRequest']],
    method: 'DELETE',
  });
});

test('It returns a not found response when removing an unknown measurement family', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    status: 404,
  }));

  const {result} = renderHook(() => useMeasurementFamilyRemover(), {wrapper});
  const remove = result.current;

  expect(await remove('custom_metric')).toEqual(MeasurementFamilyRemoverResult.NotFound);
});

test('It returns a not allowed response when removing a read-only measurement family', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    status: 422,
  }));

  const {result} = renderHook(() => useMeasurementFamilyRemover(), {wrapper});
  const remove = result.current;

  expect(await remove('custom_metric')).toEqual(MeasurementFamilyRemoverResult.Unprocessable);
});

test('An error is thrown if the server does not respond correctly', async () => {
  global.fetch = jest.fn().mockImplementation(() => ({
    status: 500,
  }));

  const {result} = renderHook(() => useMeasurementFamilyRemover(), {wrapper});
  const remove = result.current;

  expect(remove('custom_metric')).rejects.toThrow();
});
