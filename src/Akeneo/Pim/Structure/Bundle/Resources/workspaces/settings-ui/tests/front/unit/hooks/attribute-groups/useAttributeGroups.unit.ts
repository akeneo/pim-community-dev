import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {useAttributeGroups} from '@akeneo-pim-community/settings-ui/src/hooks/attribute-groups';
import {saveAttributeGroupsOrder} from '@akeneo-pim-community/settings-ui/src/infrastructure/savers';
import {anAttributeGroup} from '../../../utils/provideAttributeGroupHelper';
import {act} from 'react-test-renderer';
import fetchMock from 'jest-fetch-mock';

jest.mock('@akeneo-pim-community/settings-ui/src/infrastructure/savers/attributeGroupsSaver');

test('it initializes the state for AttributeGroups datagrid', async () => {
  fetchMock.mockResponseOnce(JSON.stringify([]), {
    status: 200,
  });

  const {result, waitForNextUpdate} = renderHookWithProviders(useAttributeGroups);
  await waitForNextUpdate();

  const [attributeGroups, reorderAttributeGroups, isPending] = result.current;
  expect(attributeGroups).toEqual([]);
  expect(reorderAttributeGroups).toBeDefined();
  expect(isPending).toBeDefined();
  expect(saveAttributeGroupsOrder).not.toBeCalled();
});

test('it loads the attribute groups list without the DQI feature', async () => {
  const groupA = anAttributeGroup('groupA', 1234, {}, 3);
  const groupB = anAttributeGroup('groupB', 4321, {}, 1);
  const groupC = anAttributeGroup('groupC', 4321, {}, 2);

  fetchMock.mockResponseOnce(JSON.stringify([groupB, groupC, groupA]), {
    status: 200,
  });

  const {result, waitForNextUpdate} = renderHookWithProviders(useAttributeGroups);
  await waitForNextUpdate();

  const [attributeGroups] = result.current;
  expect(attributeGroups).toEqual([groupB, groupC, groupA]);
  expect(saveAttributeGroupsOrder).not.toBeCalled();
});

test('it loads the attribute groups list with the DQI feature', async () => {
  const groupA = anAttributeGroup('groupA', 1234, {}, 3, true);
  const groupB = anAttributeGroup('groupB', 4321, {}, 1, false);
  const groupC = anAttributeGroup('groupC', 4321, {}, 2, true);

  fetchMock.mockResponseOnce(JSON.stringify([groupB, groupC, groupA]), {
    status: 200,
  });

  const {result, waitForNextUpdate} = renderHookWithProviders(useAttributeGroups);
  await waitForNextUpdate();

  const [attributeGroups] = result.current;

  expect(attributeGroups).toEqual([groupB, groupC, groupA]);
  expect(saveAttributeGroupsOrder).not.toBeCalled();
});

test('it refreshes the order of the attribute groups list', async () => {
  const groupA = anAttributeGroup('groupA', 1234, {}, 3);
  const groupB = anAttributeGroup('groupB', 4321, {}, 1);
  const groupC = anAttributeGroup('groupC', 4321, {}, 2);

  fetchMock.mockResponseOnce(JSON.stringify([groupA, groupB, groupC]), {
    status: 200,
  });

  const {result, waitForNextUpdate} = renderHookWithProviders(useAttributeGroups);
  await waitForNextUpdate();

  const [, reorderAttributeGroups] = result.current;
  await act(async () => {
    await reorderAttributeGroups([2, 0, 1]);
  });

  const [attributeGroups] = result.current;
  expect(attributeGroups).toEqual([groupC, groupA, groupB]);

  expect(saveAttributeGroupsOrder).toBeCalledWith({
    groupC: 0,
    groupA: 1,
    groupB: 2,
  });
});
