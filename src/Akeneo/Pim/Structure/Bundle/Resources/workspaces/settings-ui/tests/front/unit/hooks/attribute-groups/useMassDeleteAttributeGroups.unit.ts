import {AttributeGroup, useMassDeleteAttributeGroups} from '@akeneo-pim-community/settings-ui';
import {act} from '@testing-library/react-hooks';
import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import fetchMock from 'jest-fetch-mock';

test('It launch mass delete attribute group', async () => {
  // @ts-ignore
  global.fetch = jest.fn().mockImplementation(() => ({ok: true}));

  const attributeGroups: AttributeGroup[] = [
    {
      code: 'attribute1',
      sort_order: 0,
      labels: {},
      is_dqi_activated: false,
      attribute_count: 10,
    },
  ];

  const {result} = renderHookWithProviders(() => useMassDeleteAttributeGroups());
  const [isFetching, launchMassDeleteAttributeGroup] = result.current;
  expect(isFetching).toBe(false);
  await act(async () => {
    await launchMassDeleteAttributeGroup(attributeGroups);
  });

  expect(isFetching).toBe(false);
});

test('It throw an error when mass delete attribute group failed', async () => {
  // @ts-ignore
  global.fetch = jest.fn().mockImplementation(() => ({ok: false}));

  const attributeGroups: AttributeGroup[] = [
    {
      code: 'attribute1',
      sort_order: 0,
      labels: {},
      is_dqi_activated: false,
      attribute_count: 10,
    },
  ];

  const {result} = renderHookWithProviders(() => useMassDeleteAttributeGroups());
  const [isFetching, launchMassDeleteAttributeGroup] = result.current;
  expect(isFetching).toBe(false);
  expect(async () => {
    await launchMassDeleteAttributeGroup(attributeGroups);
  }).rejects.toThrow(Error);

  expect(isFetching).toBe(false);
});
