import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from 'feature/tests';
import {useMeasurementFamily} from 'feature/hooks/useMeasurementFamily';

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
        code: 'gram',
        labels: {
          en_US: 'Gram',
          fr_FR: 'Gramme',
        },
      },
      {
        code: 'kilogram',
        labels: {
          en_US: 'Kilogram',
          fr_FR: 'Kilogramme',
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
