import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {mockResponse} from '../../tests/test-utils';
import {useCategoryTree} from '../useCategoryTree';
import {CategoryResponse, CategoryTreeRoot} from '@akeneo-pim-community/shared';

const categoryTree: CategoryTreeRoot = {
  id: 42,
  code: 'masterCatalog',
  label: 'Master Catalog',
  selected: true,
};

const childrenResponse: CategoryResponse[] = [
  {
    attr: {
      id: '69',
      'data-code': 'subCategory',
    },
    children: [],
    data: 'Sub category',
    state: 'open leaf',
  },
];

describe('useCategoryTree', () => {
  test('it calls init', async () => {
    const {result, waitFor} = renderHook(() => useCategoryTree(categoryTree), {wrapper: createWrapper()});
    await waitFor(() => !!result.current);

    const expectCall = mockResponse('pim_enrich_categorytree_children', 'GET', {ok: true, json: childrenResponse});
    const init = await result.current.init();

    expectCall();
    expect(init).toEqual({
      code: 'masterCatalog',
      id: 42,
      label: 'Master Catalog',
      selectable: false,
      children: [
        {
          children: [],
          code: 'subCategory',
          id: 69,
          label: 'Sub category',
          readOnly: false,
          selectable: true,
          selected: false,
        },
      ],
    });
  });

  test('it calls children', async () => {
    const {result, waitFor} = renderHook(() => useCategoryTree(categoryTree), {wrapper: createWrapper()});
    await waitFor(() => !!result.current);

    const expectCall = mockResponse('pim_enrich_categorytree_children', 'GET', {ok: true, json: childrenResponse});
    const children = await result.current.childrenCallback(42);

    expectCall();
    expect(children).toEqual([
      {
        children: [],
        code: 'subCategory',
        id: 69,
        label: 'Sub category',
        readOnly: false,
        selectable: true,
        selected: false,
      },
    ]);
  });
});
