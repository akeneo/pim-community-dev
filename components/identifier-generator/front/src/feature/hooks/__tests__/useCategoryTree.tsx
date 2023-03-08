import {renderHook} from '@testing-library/react-hooks';
import {createWrapper} from '../../tests/hooks/config/createWrapper';
import {useCategoryTree} from '../useCategoryTree';
import {CategoryTreeRoot} from '@akeneo-pim-community/shared';

const categoryTree: CategoryTreeRoot = {
  id: 42,
  code: 'masterCatalog',
  label: 'Master Catalog',
  selected: true,
};

describe('useCategoryTree', () => {
  test('it calls init', async () => {
    const {result, waitFor} = renderHook(() => useCategoryTree(categoryTree), {wrapper: createWrapper()});
    await waitFor(() => !!result.current);

    const init = await result.current.init();

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

    const children = await result.current.childrenCallback(42);

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
