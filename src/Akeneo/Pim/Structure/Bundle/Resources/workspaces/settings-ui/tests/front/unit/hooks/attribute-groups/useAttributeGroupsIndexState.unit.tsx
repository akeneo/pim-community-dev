import React from 'react';
import {renderHookWithProviders} from '@akeneo-pim-community/legacy-bridge/tests/front/unit/utils';
import {
  useAttributeGroupsIndexState,
  useInitialAttributeGroupsIndexState,
} from '@akeneo-pim-community/settings-ui/src/hooks/attribute-groups';
import {saveAttributeGroupsOrder} from '@akeneo-pim-community/settings-ui/src/infrastructure/savers';
import {anAttributeGroup} from '../../../utils/provideAttributeGroupHelper';
import {act} from 'react-test-renderer';
import {dependencies} from '@akeneo-pim-community/legacy-bridge';
import fetchMock from 'jest-fetch-mock';

jest.mock('@akeneo-pim-community/settings-ui/src/infrastructure/savers/attributeGroupsSaver');

describe('useInitialAttributeGroupsIndexState', () => {
  const renderUseInitialAttributeGroupsIndexState = () => {
    return renderHookWithProviders(useInitialAttributeGroupsIndexState);
  };
  beforeEach(() => {
    jest.clearAllMocks();
  });

  afterAll(() => {
    jest.resetAllMocks();
  });

  test('it initializes the state for AttributeGroups datagrid', () => {
    const {result} = renderUseInitialAttributeGroupsIndexState();

    expect(result.current.attributeGroups).toEqual([]);
    expect(result.current.load).toBeDefined();
    expect(result.current.saveOrder).toBeDefined();
    expect(result.current.redirect).toBeDefined();
    expect(result.current.refresh).toBeDefined();
    expect(result.current.refreshOrder).toBeDefined();
  });

  test('it loads the attribute groups list without the DQI feature', async () => {
    const groupA = anAttributeGroup('groupA', 1234, undefined, 3);
    const groupB = anAttributeGroup('groupB', 4321, undefined, 1);
    const groupC = anAttributeGroup('groupC', 4321, undefined, 2);

    fetchMock.mockResponseOnce(JSON.stringify([groupB, groupC, groupA]), {
      status: 200,
    });

    const {result} = renderUseInitialAttributeGroupsIndexState();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.attributeGroups).toEqual([groupB, groupC, groupA]);
  });

  test('it loads the attribute groups list with the DQI feature', async () => {
    const groupA = anAttributeGroup('groupA', 1234, undefined, 3, true);
    const groupB = anAttributeGroup('groupB', 4321, undefined, 1, false);
    const groupC = anAttributeGroup('groupC', 4321, undefined, 2, true);

    fetchMock.mockResponseOnce(JSON.stringify([groupB, groupC, groupA]), {
      status: 200,
    });

    const {result} = renderUseInitialAttributeGroupsIndexState();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.attributeGroups).toEqual([groupB, groupC, groupA]);
  });

  test('it refreshes the data of the attribute groups list', async () => {
    const groupA = anAttributeGroup('groupA', 1234, undefined, 3);
    const groupB = anAttributeGroup('groupB', 4321, undefined, 1);
    const groupC = anAttributeGroup('groupC', 4321, undefined, 2);

    fetchMock.mockResponseOnce(JSON.stringify([groupA, groupB, groupC]), {
      status: 200,
    });

    const {result} = renderUseInitialAttributeGroupsIndexState();

    await act(async () => {
      result.current.load();
    });

    const groupABis = anAttributeGroup('groupA', 1234, undefined, 1);
    const groupBBis = anAttributeGroup('groupB', 4321, undefined, 2);
    const groupCBis = anAttributeGroup('groupC', 4321, undefined, 3);

    act(() => {
      result.current.refresh([groupABis, groupBBis, groupCBis]);
    });

    expect(result.current.attributeGroups).toEqual([groupABis, groupBBis, groupCBis]);
  });

  test('it refreshes the order of the attribute groups list', async () => {
    const groupA = anAttributeGroup('groupA', 1234, undefined, 3);
    const groupB = anAttributeGroup('groupB', 4321, undefined, 1);
    const groupC = anAttributeGroup('groupC', 4321, undefined, 2);

    fetchMock.mockResponseOnce(JSON.stringify([groupA, groupB, groupC]), {
      status: 200,
    });

    const {result} = renderUseInitialAttributeGroupsIndexState();

    await act(async () => {
      result.current.load();
    });

    act(() => {
      result.current.refreshOrder([groupC, groupA, groupB]);
    });

    expect(result.current.attributeGroups[0].code).toBe('groupC');
    expect(result.current.attributeGroups[0].sort_order).toBe(0);

    expect(result.current.attributeGroups[1].code).toBe('groupA');
    expect(result.current.attributeGroups[1].sort_order).toBe(1);

    expect(result.current.attributeGroups[2].code).toBe('groupB');
    expect(result.current.attributeGroups[2].sort_order).toBe(2);
  });

  test('it saves the order of the attribute groups list', async () => {
    const groupA = anAttributeGroup('groupA', 1234, undefined, 3);
    const groupB = anAttributeGroup('groupB', 4321, undefined, 1);
    const groupC = anAttributeGroup('groupC', 4321, undefined, 2);

    const groupABis = anAttributeGroup('groupA', 1234, undefined, 1);
    const groupBBis = anAttributeGroup('groupB', 4321, undefined, 2);
    const groupCBis = anAttributeGroup('groupC', 4321, undefined, 0);

    fetchMock.mockResponseOnce(JSON.stringify([groupA, groupB, groupC]), {
      status: 200,
    });

    // @ts-ignore
    saveAttributeGroupsOrder.mockResolvedValue({
      groupCBis,
      groupABis,
      groupBBis,
    });

    const {result} = renderUseInitialAttributeGroupsIndexState();

    await act(async () => {
      result.current.load();
    });

    await act(() => {
      result.current.refreshOrder([groupC, groupA, groupB]);
    });

    expect(saveAttributeGroupsOrder).toBeCalledWith({
      groupC: 0,
      groupA: 1,
      groupB: 2,
    });

    expect(result.current.attributeGroups).toEqual([groupCBis, groupABis, groupBBis]);
  });

  test('it leads to the attribute groups edition', () => {
    const groupA = anAttributeGroup('groupA', 1234);

    const {result} = renderUseInitialAttributeGroupsIndexState();

    act(() => {
      result.current.redirect(groupA);
    });

    expect(dependencies.router.generate).toBeCalled();
    expect(dependencies.router.redirect).toBeCalled();
  });
});

describe('useAttributeGroupsIndexState', () => {
  const renderUseAttributeGroupsIndexState = () => {
    return renderHookWithProviders(useAttributeGroupsIndexState);
  };
  beforeEach(() => {
    jest.clearAllMocks();
    jest.restoreAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it throws an error if it used outside AttributeGroups datagrid context', () => {
    jest.spyOn(React, 'useContext').mockImplementation(() => undefined);

    const {result} = renderUseAttributeGroupsIndexState();

    expect(result.error).not.toBeNull();
  });

  test('it returns context', () => {
    const {result} = renderUseAttributeGroupsIndexState();

    expect(result.current.attributeGroups).toEqual([]);
    expect(result.current.load).toBeDefined();
    expect(result.current.saveOrder).toBeDefined();
    expect(result.current.redirect).toBeDefined();
    expect(result.current.refresh).toBeDefined();
    expect(result.current.refreshOrder).toBeDefined();
  });
});
