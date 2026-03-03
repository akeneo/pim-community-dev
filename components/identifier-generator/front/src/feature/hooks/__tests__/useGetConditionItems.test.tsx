import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useGetConditionItems} from '../useGetConditionItems';
import {act, renderHook} from '@testing-library/react-hooks';
import {ATTRIBUTE_TYPE, CONDITION_NAMES, Conditions, Operator} from '../../models';

describe('useGetConditionItems', () => {
  test('it paginate items', async () => {
    const resultsPage1 = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand', type: ATTRIBUTE_TYPE.TEXT}]},
    ];

    const resultsPage2 = [
      {
        id: 'marketing',
        text: 'Marketing',
        children: [{id: 'color', text: 'Color', type: ATTRIBUTE_TYPE.SIMPLE_SELECT}],
      },
      {
        id: 'design',
        text: 'Design',
        children: [{id: 'main_color', text: 'Main Color', type: ATTRIBUTE_TYPE.MULTI_SELECT}],
      },
    ];

    const mergedResults = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {
        id: 'marketing',
        text: 'Marketing',
        children: [
          {id: 'brand', text: 'Brand', type: ATTRIBUTE_TYPE.TEXT},
          {id: 'color', text: 'Color', type: ATTRIBUTE_TYPE.SIMPLE_SELECT},
        ],
      },
      {
        id: 'design',
        text: 'Design',
        children: [{id: 'main_color', text: 'Main Color', type: ATTRIBUTE_TYPE.MULTI_SELECT}],
      },
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    const conditions: Conditions = [];
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      await waitFor(() => result.current?.conditionItems?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });
    expectCallPage1();
    expect(hookResult.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage2,
    });
    act(() => {
      hookResult.current.handleNextPage();
    });
    await hookWaitFor(() => hookResult.current?.conditionItems?.length > 2);
    expectCallPage2();
    expect(hookResult.current.conditionItems).toEqual(mergedResults);
  });

  test('it resets items on search', async () => {
    const resultsPage1 = [
      {
        id: 'system',
        text: 'System',
        children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ],
      },
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand'}]},
    ];

    const resultsWithSearch = [{id: 'system', text: 'System', children: [{id: 'family', text: 'Family'}]}];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    const conditions: Conditions = [];
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      await waitFor(() => result.current?.conditionItems?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });
    expectCallPage1();
    expect(hookResult.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsWithSearch,
    });
    act(() => {
      hookResult.current.setSearchValue('fam');
    });
    act(() => {
      hookResult.current.setSearchValue('family');
    });
    await hookWaitFor(() => hookResult.current?.conditionItems?.length === 1);
    expectCallPage2();
    expect(hookResult.current.conditionItems).toEqual(resultsWithSearch);
  });

  test('it filters system items', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [{id: 'enabled', text: 'Enabled'}]},
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand'}]},
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {
      ok: true,
      json: resultsPage1,
    });

    let hookResult = undefined;
    const conditions: Conditions = [{type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY}];
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      hookResult = result;
      await waitFor(() => result.current?.conditionItems?.length > 0);
    });

    expectCallPage1();
    expect(hookResult.current.conditionItems).toEqual(resultsPage1);
  });
});
