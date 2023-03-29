import {AttributeGroup, useDeleteAttributeGroup} from '@akeneo-pim-community/settings-ui';
import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';

test('It delete attribute group', async () => {
  // @ts-ignore
  global.fetch = jest.fn().mockImplementation(() => ({ok: true}));

  const {result} = renderHookWithProviders(() => useDeleteAttributeGroup());
  const [isLoading, deleteAttributeGroup] = result.current;
  expect(isLoading).toBe(false);
  await act(async () => {
    await deleteAttributeGroup();
  });

  expect(isLoading).toBe(false);
});

test('It throw an error when delete attribute group failed', async () => {
  // @ts-ignore
  global.fetch = jest.fn().mockImplementation(() => ({ok: false}));

  const {result} = renderHookWithProviders(() => useDeleteAttributeGroup());
  const [isLoading, deleteAttributeGroup] = result.current;
  expect(isLoading).toBe(false);
  expect(async () => {
    await deleteAttributeGroup('attribute1', 'new_attribute_group');
  }).rejects.toThrow(Error);

  expect(isLoading).toBe(false);
});
