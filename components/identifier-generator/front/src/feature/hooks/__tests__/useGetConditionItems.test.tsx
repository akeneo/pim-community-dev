//import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useGetConditionItems} from '../useGetConditionItems';
import {act, renderHook} from '@testing-library/react-hooks';
import {CONDITION_NAMES, Operator} from '../../models';

describe('useGetConditionItems', () => {
  test('it paginate items', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ]},
      {id: 'marketing', text: 'Marketing', children: [
          {id: 'brand', text: 'Brand'},
        ]}
    ];

    const resultsPage2 = [
      {id: 'marketing', text: 'Marketing', children: [
          {id: 'color', text: 'Color'},
        ]},
      {id: 'design', text: 'Design', children: [
          {id: 'main_color', text: 'Main Color'}
        ]},
    ];

    const mergedResults = [
      {id: 'system', text: 'System', children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ]},
      {id: 'marketing', text: 'Marketing', children: [
          {id: 'brand', text: 'Brand'},
          {id: 'color', text: 'Color'},
        ]},
      {id: 'design', text: 'Design', children: [
          {id: 'main_color', text: 'Main Color'}
        ]},
    ]

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {ok: true, json: resultsPage1});
    const {result, waitFor} = renderHook(() => useGetConditionItems(true, [], 3), {wrapper: createWrapper()});
    await waitFor(() => result.current?.conditionItems?.length > 0);
    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {ok: true, json: resultsPage2});
    act(() => {
      result.current.handleNextPage();
    });
    await waitFor(() => result.current?.conditionItems?.length > 2);
    expectCallPage2();
    expect(result.current.conditionItems).toEqual(mergedResults);
  });

  test('it resets items on search', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [
          {id: 'family', text: 'Family'},
          {id: 'enabled', text: 'Enabled'},
        ]},
      {id: 'marketing', text: 'Marketing', children: [
          {id: 'brand', text: 'Brand'},
        ]}
    ];

    const resultsWithSearch = [
      {id: 'system', text: 'System', children: [
          {id: 'family', text: 'Family'},
        ]},
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {ok: true, json: resultsPage1});
    const {result, waitFor} = renderHook(() => useGetConditionItems(true, [], 3), {wrapper: createWrapper()});
    await waitFor(() => result.current?.conditionItems?.length > 0);
    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);

    const expectCallPage2 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {ok: true, json: resultsWithSearch});
    act(() => {
      result.current.setSearchValue('fam');
    });
    act(() => {
      result.current.setSearchValue('family');
    });
    await waitFor(() => result.current?.conditionItems?.length === 1);
    expectCallPage2();
    expect(result.current.conditionItems).toEqual(resultsWithSearch);
  });

  test('it filters system items', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [
          {id: 'enabled', text: 'Enabled'},
        ]},
      {id: 'marketing', text: 'Marketing', children: [
          {id: 'brand', text: 'Brand'},
        ]}
    ];

    const expectCallPage1 = mockResponse('akeneo_identifier_generator_get_conditions', 'GET', {ok: true, json: resultsPage1});
    const {result, waitFor} = renderHook(() => useGetConditionItems(true, [
      {type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY},
    ], 3), {wrapper: createWrapper()});
    await waitFor(() => result.current?.conditionItems?.length > 0);
    expectCallPage1();
    expect(result.current.conditionItems).toEqual(resultsPage1);
  });
});
