import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {CategoryTree, useCategoryTreeList} from '@akeneo-pim-community/settings-ui';
import {act} from 'react-test-renderer';
import {aListOfCategoryTrees} from '../../../utils/provideCategoryHelper';

jest.mock('@akeneo-pim-community/shared/src/fetcher/baseFetcher');

describe('useCategoryTreeList', () => {
  const renderUseCategoryTreeList = () => {
    return renderHookWithProviders(useCategoryTreeList);
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns default values', () => {
    const {result} = renderUseCategoryTreeList();
    expect(result.current.trees.length).toBe(0);
    expect(result.current.isPending).toBeFalsy();
    expect(result.current.load).toBeDefined();
  });

  test('it loads the list of category trees', async () => {
    const categoryTreeList: CategoryTree[] = aListOfCategoryTrees(['tree_1', 'tree_2', 'tree_3']);

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(categoryTreeList),
    });

    const {result} = renderUseCategoryTreeList();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.trees.length).toBe(3);
    expect(result.current.isPending).toEqual(false);
  });

  test('it returns an empty list of category trees when the loading failed', async () => {
    const logError = jest.fn();
    jest.spyOn(global.console, 'error').mockImplementation(logError);
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockReject(new Error('An unexpected server error'));

    const {result} = renderUseCategoryTreeList();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.trees.length).toBe(0);
    expect(result.current.isPending).toEqual(false);
    expect(logError).toHaveBeenCalled();
  });
});
