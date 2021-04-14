import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {CategoryTree, useCategoryTree} from '@akeneo-pim-community/settings-ui';
import {aBackendCategoryTree, aCategoryTree} from '../../../utils/provideCategoryHelper';
import {act} from 'react-test-renderer';

describe('useCategoryTree', () => {
  const renderUseCategoryTree = (treeId: number) => {
    return renderHookWithProviders(() => useCategoryTree(treeId));
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns default values', () => {
    const {result} = renderUseCategoryTree(1234);
    expect(result.current.tree).toBeNull();
    expect(result.current.status).toBe('idle');
    expect(result.current.load).toBeDefined();
    expect(result.current.error).toBeNull();
  });

  test('it loads the category tree', async () => {
    const treeId = 1234;
    const categoryTree: CategoryTree = aCategoryTree(
      'a_root_category',
      ['a_category', 'a_second_category'],
      true,
      treeId
    );
    const response = aBackendCategoryTree('a_root_category', ['a_category', 'a_second_category'], true, treeId);

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(response),
    });

    const {result} = renderUseCategoryTree(treeId);

    await act(async () => {
      result.current.load();
    });

    expect(result.current.tree).toEqual(categoryTree);
    expect(result.current.status).toBe('fetched');
  });

  test('it returns errors when the loading failed', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockReject(new Error('An unexpected server error'));

    const {result} = renderUseCategoryTree(1234);

    await act(async () => {
      result.current.load();
    });

    expect(result.current.tree).toBeNull();
    expect(result.current.status).toEqual('error');
    expect(result.current.error).toMatch(/unexpected server error/);
  });
});
