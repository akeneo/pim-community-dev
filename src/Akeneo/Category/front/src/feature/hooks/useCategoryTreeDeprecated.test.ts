import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../models';
import {act} from 'react-test-renderer';
import {aBackendCategoryTree, aCategoryTree} from '../../tests/provideCategoryHelper';
import {useCategoryTreeDeprecated} from './useCategoryTreeDeprecated';

describe('useCategoryTree', () => {
  const renderUseCategoryTree = (treeId: number) => {
    return renderHookWithProviders(() => useCategoryTreeDeprecated(treeId, '1'));
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
    expect(result.current.loadingStatus).toBe('idle');
    expect(result.current.loadTree).toBeDefined();
    expect(result.current.error).toBeNull();
  });

  test('it loads the category tree', async () => {
    const treeId = 1234;
    const categoryTree: CategoryTreeModel = aCategoryTree(
      'a_root_category',
      ['a_category', 'a_second_category'],
      true,
      false,
      treeId
    );
    const response = aBackendCategoryTree('a_root_category', ['a_category', 'a_second_category'], true, treeId);

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(response),
    });

    const {result} = renderUseCategoryTree(treeId);

    await act(async () => {
      result.current.loadTree();
    });

    expect(result.current.tree).toEqual(categoryTree);
    expect(result.current.loadingStatus).toBe('fetched');
  });

  test('it returns errors when the loading failed', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockReject(new Error('An unexpected server error'));

    const {result} = renderUseCategoryTree(1234);

    await act(async () => {
      result.current.loadTree();
    });

    expect(result.current.tree).toBeNull();
    expect(result.current.loadingStatus).toEqual('error');
    expect(result.current.error).toMatch(/unexpected server error/);
  });
});
