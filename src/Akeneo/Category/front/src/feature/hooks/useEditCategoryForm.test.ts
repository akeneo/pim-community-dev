import {set} from 'lodash/fp';
import {act} from 'react-test-renderer';
import {useCategory, CategoryResponse} from './useCategory';
import {useEditCategoryForm} from './useEditCategoryForm';
import {saveEditCategoryForm} from '../infrastructure';
import {CategoryPermissions, EnrichCategory} from '../models';
import {categoriesAreEqual, permissionsAreEqual} from '../helpers';
import {categoryRenderHookWithProviders} from '../../tests/CategoryRenderHook';

const aCategory: EnrichCategory = {
  id: 6,
  properties: {
    code: 'clothes',
    labels: {fr_FR: 'V\u00eatements', en_US: 'Clothes', de_DE: 'Kleidung'},
  },
  attributes: {
    'description_87939c45-1d85-4134-9579-d594fff65030_en_US': {
      data: 'All the shoes you need!',
      locale: 'en_US',
      attribute_code: 'description_87939c45-1d85-4134-9579-d594fff65030',
    },
    'description_87939c45-1d85-4134-9579-d594fff65030_fr_FR': {
      data: 'Les chaussures dont vous avez besoin !',
      locale: 'fr_FR',
      attribute_code: 'description_87939c45-1d85-4134-9579-d594fff65030',
    },
    'banner_8587cda6-58c8-47fa-9278-033e1d8c735c': {
      data: {
        size: 168107,
        file_path: '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
        mime_type: 'image/jpeg',
        extension: 'jpg',
        original_filename: 'shoes.jpg',
      },
      locale: null,
      attribute_code: 'banner_8587cda6-58c8-47fa-9278-033e1d8c735c',
    },
    'seo_meta_title_ebdf744c-17e0-11ed-835e-0b2d6a7798db': {
      data: 'Shoes at will',
      locale: null,
      attribute_code: 'seo_meta_title_ebdf744c-17e0-11ed-835e-0b2d6a7798db',
    },
    'seo_meta-description_ef7ace80-17e0-11ed-9ac6-2feec2ba2321_en_US': {
      data: 'At cheapshoes we have tons of shoes for everyone\nYou dream of a shoe, we have it.',
      locale: 'en_US',
      attribute_code: 'seo_meta-description_ef7ace80-17e0-11ed-9ac6-2feec2ba2321',
    },
    'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_en_US': {
      data: 'Shoes Slippers Sneakers',
      locale: 'en_US',
      attribute_code: 'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd',
    },
    'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd_fr_FR': {
      data: 'Chaussures Tongues Espadrilles',
      locale: 'fr_FR',
      attribute_code: 'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd',
    },
  },
  permissions: {view: [1, 2, 3], edit: [1, 2], own: [1]},
};

const mockedUseCategoryResult = (): CategoryResponse => {
  return {load: async () => {}, status: 'fetched', category: aCategory, error: null};
};

jest.mock('./useCategory');

jest.mock('../infrastructure');

let mockedSaveEditCategoryForm = saveEditCategoryForm as jest.MockedFunction<typeof saveEditCategoryForm>;
let mockedUseCategory = useCategory as jest.MockedFunction<typeof useCategory>;

describe('useEditCategoryForm', () => {
  let categoryResult = mockedUseCategoryResult();

  const renderUseEditCategoryForm = (categoryId: number) => {
    // return renderHookWithProviders(() => useEditCategoryForm(categoryId));
    return categoryRenderHookWithProviders(() => useEditCategoryForm(categoryId));
  };

  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
    mockedUseCategory.mockReturnValue(categoryResult);
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  test('it returns default values', () => {
    const {result} = renderUseEditCategoryForm(42);

    expect(result.current.categoryFetchingStatus).toBe('fetched');
    expect(result.current.category).toStrictEqual(aCategory);
    expect(result.current.onChangeCategoryLabel).toBeDefined();
    expect(result.current.onChangePermissions).toBeDefined();
    expect(result.current.onChangeApplyPermissionsOnChildren).toBeDefined();
    expect(result.current.saveCategory).toBeDefined();
    expect(result.current.isModified).toBe(false);
  });

  test('it changes the value of a category label', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
    });

    expect(result.current.isModified).toBe(true);
    expect(result.current.category).toStrictEqual(set(['properties', 'labels', 'en_US'], 'Foo', aCategory));

    act(() => {
      result.current.onChangeCategoryLabel('en_US', aCategory.properties.labels.en_US);
    });

    expect(result.current.isModified).toBe(false);
    expect(result.current.category).toStrictEqual(aCategory);
  });

  test('it changes the view permissions of a category', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('view', [1, 3]);
    });

    expect(result.current.isModified).toBe(true);

    const expectedPermissions: CategoryPermissions = {
      view: [1, 3],
      edit: [1], // looses user group 2 to respect permissions inclusion invariant
      own: [1],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);

    act(() => {
      result.current.onChangePermissions('edit', [1, 2]);
    });

    expect(result.current.isModified).toBe(false);
    // order or user group in permissions is permuted but the categories are equivalent
    // hence the use of categegoriesAreEquals here, which consider these categories as identical
    expect(categoriesAreEqual(result.current.category!, aCategory)).toBe(true);
  });

  test('it changes the edit permissions of a category', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('edit', [4]);
    });
    expect(result.current.isModified).toBe(true);

    let expectedPermissions: CategoryPermissions = {
      view: [1, 2, 3, 4], // gain user group 4 to respect permissions inclusion invariant
      edit: [4],
      own: [], // looses user group 1 to respect permissions inclusion invariant
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);

    act(() => {
      result.current.onChangePermissions('own', [1]);
    });

    // no changes in view and edit because they already has the user group 1
    expectedPermissions = {
      view: [1, 2, 3, 4],
      edit: [1, 4],
      own: [1],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions('view', [1, 2, 3]);
    });

    // no changes in view and edit because they already has the user group 1
    expectedPermissions = {
      view: [1, 2, 3],
      edit: [1], // looses user group 4 to respect permissions inclusion invariant
      own: [1],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions('edit', [1, 2]);
    });

    expect(result.current.isModified).toBe(false);
    expect(categoriesAreEqual(result.current.category!, aCategory)).toBe(true);
  });

  test('it changes the own permissions of a category', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('own', [4]);
    });

    expect(result.current.isModified).toBe(true);

    // user group 4 is now include in view and edit
    const expectedPermissions: CategoryPermissions = {
      view: [1, 2, 3, 4],
      edit: [1, 2, 4],
      own: [4],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions('view', [1, 2, 3]); // edit and own will loose user group 4
    });

    act(() => {
      result.current.onChangePermissions('own', [1]);
    });

    expect(result.current.isModified).toBe(false);
    expect(categoriesAreEqual(result.current.category!, aCategory)).toBe(true);
  });

  test('it changes the value of apply permissions on children', async () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangeApplyPermissionsOnChildren(false);
    });

    expect(result.current.isModified).toBe(false);

    mockedSaveEditCategoryForm.mockImplementation(async () => ({
      success: true,
      category: aCategory,
    }));

    await act(async () => {
      await result.current.saveCategory();
    });

    expect(saveEditCategoryForm).toHaveBeenCalled();

    const options = mockedSaveEditCategoryForm.mock.calls[0][2];
    expect(options.applyPermissionsOnChildren).toStrictEqual(false);
  });

  test('it saves a category and refreshes the category data on success', async () => {
    const modifiedCategory: EnrichCategory = set(['labels', 'en_US'], 'Foo', aCategory);

    mockedSaveEditCategoryForm.mockResolvedValue({
      success: true,
      category: modifiedCategory,
    });

    const {result} = renderUseEditCategoryForm(42);
    await act(async () => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
      result.current.saveCategory();
    });

    expect(mockedSaveEditCategoryForm).toHaveBeenCalled();

    expect(result.current.isModified).toBe(false);
    expect(result.current.category).toStrictEqual(modifiedCategory);
  });

  test('it saves a category and refreshes the category data on fail', async () => {
    mockedSaveEditCategoryForm.mockResolvedValue({
      success: false,
      errors: {},
    });

    const modifiedCategory: EnrichCategory = set(['properties', 'labels', 'en_US'], 'Foo', aCategory);

    const {result} = renderUseEditCategoryForm(42);

    act(() => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
    });

    await act(async () => {
      result.current.saveCategory();
    });

    expect(result.current.isModified).toBe(true);
    expect(result.current.category).toStrictEqual(modifiedCategory);
  });
});
