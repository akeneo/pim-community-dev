import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useCategoryTrees} from '../useCategoryTrees';
import {act, renderHook} from '@testing-library/react-hooks';
import {CategoryTreeRoot} from '@akeneo-pim-community/shared';

describe('useCategoryTrees', () => {
  test('it get root trees', async () => {
    const onChange = jest.fn();
    const childrenResponse = [
      {
        id: 42,
        code: 'masterCatalog',
        label: 'Master Catalog',
        selected: 'false',
      },
      {
        id: 69,
        code: 'print',
        label: 'Print',
        selected: 'true',
      },
    ];
    const expectCall = mockResponse('pim_enrich_categorytree_listtree', 'GET', {ok: true, json: childrenResponse});

    let hookResult = undefined;
    await act(async () => {
      const {result, waitFor} = renderHook(() => useCategoryTrees(onChange), {wrapper: createWrapper()});
      await waitFor(() => result.current?.length > 0);
      hookResult = result;
    });

    expectCall();
    expect((hookResult as unknown as {current: CategoryTreeRoot[]}).current).toEqual([
      {
        id: 42,
        code: 'masterCatalog',
        label: 'Master Catalog',
        selected: false,
      },
      {
        id: 69,
        code: 'print',
        label: 'Print',
        selected: true,
      },
    ]);
    expect(onChange).toBeCalledWith({
      id: 69,
      code: 'print',
      label: 'Print',
      selected: true,
    });
  });
});
