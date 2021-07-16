import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useFetchers} from '../contexts/FetcherContext';

test('Fetch attributes by identifiers needs to be implemented', async () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.attribute.fetchByIdentifiers([]);
  }).toThrowError('Fetch attributes by identifiers needs to be implemented');
});

test('Fetch all channels needs to be implemented', async () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.channel.fetchAll();
  }).toThrowError('Fetch all channels needs to be implemented');
});

test('Fetch association type needs to be implemented', async () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.associationType.fetchByCodes();
  }).toThrowError('Fetch association types by codes needs to be implemented');
});
