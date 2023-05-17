import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {useGetConditionItems} from '../useGetConditionItems';
import {act, renderHook} from '@testing-library/react-hooks';
import {ATTRIBUTE_TYPE, CONDITION_NAMES, Conditions, Operator} from '../../models';
import {server} from '../../mocks/server';
import {rest} from 'msw';

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

    server.use(
      rest.get('/akeneo_identifier_generator_get_conditions', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(resultsPage1));
      })
    );

    const conditions: Conditions = [];
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      await waitFor(() => result.current?.conditionItems?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });
    expect(hookResult.current.conditionItems).toEqual(resultsPage1);

    server.use(
      rest.get('/akeneo_identifier_generator_get_conditions', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(resultsPage2));
      })
    );
    act(() => {
      hookResult.current.handleNextPage();
    });
    await hookWaitFor?.(() => hookResult.current?.conditionItems?.length > 2);
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

    server.use(
      rest.get('/akeneo_identifier_generator_get_conditions', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(resultsPage1));
      })
    );

    const conditions: Conditions = [];
    let hookResult = undefined;
    let hookWaitFor = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      await waitFor(() => result.current?.conditionItems?.length > 0);
      hookResult = result;
      hookWaitFor = waitFor;
    });
    expect(hookResult.current.conditionItems).toEqual(resultsPage1);

    server.use(
      rest.get('/akeneo_identifier_generator_get_conditions', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(resultsWithSearch));
      })
    );

    act(() => {
      hookResult.current.setSearchValue('fam');
    });
    act(() => {
      hookResult.current.setSearchValue('family');
    });
    await hookWaitFor(() => hookResult.current?.conditionItems?.length === 1);
    expect(hookResult.current.conditionItems).toEqual(resultsWithSearch);
  });

  test('it filters system items', async () => {
    const resultsPage1 = [
      {id: 'system', text: 'System', children: [{id: 'enabled', text: 'Enabled'}]},
      {id: 'marketing', text: 'Marketing', children: [{id: 'brand', text: 'Brand'}]},
    ];

    server.use(
      rest.get('/akeneo_identifier_generator_get_conditions', (req, res, ctx) => {
        return res(ctx.status(200), ctx.json(resultsPage1));
      })
    );

    let hookResult = undefined;
    const conditions: Conditions = [{type: CONDITION_NAMES.FAMILY, operator: Operator.EMPTY}];
    await act(async () => {
      const {result, waitFor} = renderHook(() => useGetConditionItems(true, conditions, 3), {wrapper: createWrapper()});
      hookResult = result;
      await waitFor(() => result.current?.conditionItems?.length > 0);
    });

    expect(hookResult.current.conditionItems).toEqual(resultsPage1);
  });
});
