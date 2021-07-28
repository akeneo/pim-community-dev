import {act} from '@testing-library/react-hooks';
import {useAssociationType, useAssociationTypes} from './useAssociationTypes';
import {renderHookWithProviders} from '../tests';

const flushPromises = () => new Promise(setImmediate);

test('It fetches the association types', async () => {
  const associationTypeCodes = ['XSELL', 'PACK'];
  const {result} = renderHookWithProviders(() => useAssociationTypes(associationTypeCodes));
  await act(async () => {
    await flushPromises();
  });

  const associationTypes = result.current;
  expect(associationTypes).toEqual([
    {
      code: 'XSELL',
      labels: {
        en_US: 'Cross sell',
      },
      is_quantified: false,
    },
    {
      code: 'PACK',
      labels: {},
      is_quantified: true,
    },
  ]);
});

test('It fetches an association type', async () => {
  const {result} = renderHookWithProviders(() => useAssociationType('XSELL'));
  await act(async () => {
    await flushPromises();
  });

  const [isFetching, associationType] = result.current;
  expect(isFetching).toBe(false);
  expect(associationType).toEqual({
    code: 'XSELL',
    labels: {
      en_US: 'Cross sell',
    },
    is_quantified: false,
  });
});

test('It returns null if no association type', async () => {
  const {result} = renderHookWithProviders(() => useAssociationType('nonexistent_association_type'));
  await act(async () => {
    await flushPromises();
  });

  const [isFetching, associationType] = result.current;
  expect(isFetching).toBe(false);
  expect(associationType).toBeNull();
});

test('It returns null during loading', async () => {
  const {result} = renderHookWithProviders(() => useAssociationType('XSELL'));
  const [isFetching, associationType] = result.current;
  expect(isFetching).toBe(true);
  expect(associationType).toBeNull();

  await act(async () => {
    await flushPromises();
  });

  const [isFetchingAfterFetch, associationTypeAfterFetch] = result.current;
  expect(isFetchingAfterFetch).toBe(false);
  expect(associationTypeAfterFetch).toEqual({
    code: 'XSELL',
    labels: {
      en_US: 'Cross sell',
    },
    is_quantified: false,
  });
});

test('It return association types only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useAssociationTypes(['XSELL']));
  unmount();
});

test('It returns association type only if hook is mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useAssociationType('XSELL'));
  unmount();
});
