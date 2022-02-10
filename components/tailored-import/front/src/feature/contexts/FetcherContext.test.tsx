import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useFetchers} from '../contexts/FetcherContext';

test('Fetch attributes by identifiers needs to be implemented', () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.attribute.fetchByIdentifiers([]);
  }).toThrowError('Fetch attributes by identifiers needs to be implemented');
});

test('Fetch attribute identifier needs to be implemented', () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.attribute.fetchAttributeIdentifier();
  }).toThrowError('Fetch attribute identifier needs to be implemented');
});

test('Fetch all channels needs to be implemented', () => {
  const {result} = renderHookWithProviders(() => useFetchers());

  expect(() => {
    result.current.channel.fetchAll();
  }).toThrowError('Fetch all channels needs to be implemented');
});
