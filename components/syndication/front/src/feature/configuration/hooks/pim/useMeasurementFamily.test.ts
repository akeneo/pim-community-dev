import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '../../tests';
import {useMeasurementFamily} from './useMeasurementFamily';

const flushPromises = () => new Promise(setImmediate);

test('It fetches a measurementFamily', async () => {
  const {result} = renderHookWithProviders(() => useMeasurementFamily('Weight'));
  await act(async () => {
    await flushPromises();
  });

  const measurementFamilyAfterFetch = result.current;
  expect(measurementFamilyAfterFetch).toEqual({
    code: 'Weight',
    units: [
      {
        code: 'meter',
        labels: {
          en_US: 'Meter',
          fr_FR: 'Metre',
        },
      },
    ],
  });
});

test('It returns null if the measurement family does not exists', async () => {
  const {result} = renderHookWithProviders(() => useMeasurementFamily('Length'));
  await act(async () => {
    await flushPromises();
  });

  const measurementFamilyAfterFetch = result.current;
  expect(measurementFamilyAfterFetch).toEqual(null);
});

test('It returns measurement family only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useMeasurementFamily('Weight'));
  unmount();
});
