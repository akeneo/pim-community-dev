import {renderHookWithProviders} from '@akeneo-pim-community/shared/tests/front/unit/utils';
import {Category, useCategoryTreeList} from '@akeneo-pim-community/settings-ui';
import {act} from 'react-test-renderer';
import {aListOfCategories} from '../../../utils/provideCategoryHelper';

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
    expect(result.current.status).toBe('idle');
    expect(result.current.error).toBeNull();
    expect(result.current.load).toBeDefined();
  });

  test('it loads the list of category trees', async () => {
    const categoryTreeList: Category[] = aListOfCategories(['tree_1', 'tree_2', 'tree_3']);

    // @ts-ignore;
    jest.spyOn(global, 'fetch').mockResolvedValue({
      json: () => Promise.resolve(categoryTreeList),
    });

    const {result} = renderUseCategoryTreeList();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.trees.length).toBe(3);
    expect(result.current.status).toBe('fetched');
  });

  test('it returns an empty list of category trees when the loading failed', async () => {
    // @ts-ignore
    jest.spyOn(global, 'fetch').mockReject(new Error('An unexpected server error'));

    const {result} = renderUseCategoryTreeList();

    await act(async () => {
      result.current.load();
    });

    expect(result.current.trees.length).toBe(0);
    expect(result.current.status).toBe('error');
    expect(result.current.error).toMatch(/unexpected server error/);
  });
});
