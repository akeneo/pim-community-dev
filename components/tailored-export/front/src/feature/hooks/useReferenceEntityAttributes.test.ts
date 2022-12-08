import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useReferenceEntityAttributes} from './useReferenceEntityAttributes';
import {ReferenceEntityAttribute} from 'feature/models';

const referenceEntityAttributes: ReferenceEntityAttribute[] = [
  {
    type: 'text',
    code: 'name',
    identifier: 'name_1234',
    labels: {fr_FR: 'French name', en_US: 'English name'},
    value_per_channel: false,
    value_per_locale: false,
  },
  {
    type: 'image',
    code: 'image',
    identifier: 'image_1234',
    labels: {fr_FR: 'French image', en_US: 'English image'},
    value_per_channel: true,
    value_per_locale: true,
  },
];

beforeEach(() => {
  global.fetch = jest.fn().mockImplementation(async () => ({
    ok: true,
    json: async () => referenceEntityAttributes,
  }));
});

test('it fetches reference entity attributes', async () => {
  const {result, waitForNextUpdate} = renderHookWithProviders(() => useReferenceEntityAttributes('designer'));

  await act(async () => {
    await waitForNextUpdate();
  });

  expect(result.current).toEqual(referenceEntityAttributes);
});

test('it only set states when mounted', async () => {
  const {unmount} = renderHookWithProviders(() => useReferenceEntityAttributes('designer'));
  unmount();
});
