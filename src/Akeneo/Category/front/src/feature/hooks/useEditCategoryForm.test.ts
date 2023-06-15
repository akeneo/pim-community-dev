import {set} from 'lodash/fp';
import {act} from 'react-test-renderer';
import {useCategory, CategoryResponse} from './useCategory';
import {useEditCategoryForm} from './useEditCategoryForm';
import {saveEditCategoryForm} from '../infrastructure';
import {EnrichCategory} from '../models';
import {categoriesAreEqual, permissionsAreEqual} from '../helpers';
import {renderHookWithCategoryProviders} from '../../tests/CategoryRenderHook';
import {CategoryPermissions} from '../models/CategoryPermission';
import {UserGroup} from './useFetchUserGroups';

const aCategory: EnrichCategory = {
  id: 6,
  isRoot: false,
  template_uuid: null,
  root: null,
  properties: {
    code: 'clothes',
    labels: {fr_FR: 'V\u00eatements', en_US: 'Clothes', de_DE: 'Kleidung'},
  },
  attributes: {
    'description|87939c45-1d85-4134-9579-d594fff65030|print|en_US': {
      data: 'All the shoes you need!',
      channel: 'print',
      locale: 'en_US',
      attribute_code: 'description_87939c45-1d85-4134-9579-d594fff65030',
    },
    'description|87939c45-1d85-4134-9579-d594fff65030|print|fr_FR': {
      data: 'Les chaussures dont vous avez besoin !',
      channel: 'print',
      locale: 'fr_FR',
      attribute_code: 'description_87939c45-1d85-4134-9579-d594fff65030',
    },
    'banner|8587cda6-58c8-47fa-9278-033e1d8c735c|print|en_US': {
      data: {
        size: 168107,
        file_path: '8/8/3/d/883d041fc9f22ce42fee07d96c05b0b7ec7e66de_shoes.jpg',
        mime_type: 'image/jpeg',
        extension: 'jpg',
        original_filename: 'shoes.jpg',
      },
      channel: 'print',
      locale: null,
      attribute_code: 'banner_8587cda6-58c8-47fa-9278-033e1d8c735c',
    },
    'seo_meta_title|ebdf744c-17e0-11ed-835e-0b2d6a7798db|print|en_US': {
      data: 'Shoes at will',
      channel: 'print',
      locale: null,
      attribute_code: 'seo_meta_title_ebdf744c-17e0-11ed-835e-0b2d6a7798db',
    },
    'seo_meta-description|ef7ace80-17e0-11ed-9ac6-2feec2ba2321|print|en_US': {
      data: 'At cheapshoes we have tons of shoes for everyone\nYou dream of a shoe, we have it.',
      channel: 'print',
      locale: 'en_US',
      attribute_code: 'seo_meta-description_ef7ace80-17e0-11ed-9ac6-2feec2ba2321',
    },
    'seo_keywords|54f6725a-17e1-11ed-a002-73412755f3bd|print|en_US': {
      data: 'Shoes Slippers Sneakers',
      channel: 'print',
      locale: 'en_US',
      attribute_code: 'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd',
    },
    'seo_keywords|54f6725a-17e1-11ed-a002-73412755f3bd|print|fr_FR': {
      data: 'Chaussures Tongues Espadrilles',
      channel: 'print',
      locale: 'fr_FR',
      attribute_code: 'seo_keywords_54f6725a-17e1-11ed-a002-73412755f3bd',
    },
  },
  permissions: {
    view: [
      {id: 1, label: 'Manager'},
      {id: 2, label: 'IT Support'},
      {id: 3, label: 'Redactor'},
    ],
    edit: [
      {id: 1, label: 'Manager'},
      {id: 2, label: 'IT Support'},
    ],
    own: [{id: 1, label: 'Manager'}],
  },
};

const userGroups: UserGroup[] = [
  {
    id: '1',
    label: 'IT support',
    isDefault: false,
  },
  {
    id: '2',
    label: 'Manager',
    isDefault: false,
  },
  {
    id: '3',
    label: 'Furniture manager',
    isDefault: false,
  },
  {
    id: '4',
    label: 'Clothes manager',
    isDefault: false,
  },
];

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
    return renderHookWithCategoryProviders(() => useEditCategoryForm(categoryId));
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
      result.current.onChangePermissions(userGroups, 'view', [1, 3]);
    });

    expect(result.current.isModified).toBe(true);

    const expectedPermissions: CategoryPermissions = {
      view: [
        {id: 1, label: 'Manager'},
        {id: 3, label: 'Redactor'},
      ],
      edit: [{id: 1, label: 'Manager'}],
      own: [{id: 1, label: 'Manager'}],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);

    act(() => {
      result.current.onChangePermissions(userGroups, 'edit', [1, 2]);
    });

    expect(result.current.isModified).toBe(false);
    // order or user group in permissions is permuted but the categories are equivalent
    // hence the use of categegoriesAreEquals here, which consider these categories as identical
    expect(categoriesAreEqual(result.current.category!, aCategory)).toBe(true);
  });

  test('it changes the edit permissions of a category', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions(userGroups, 'edit', [4]);
    });
    expect(result.current.isModified).toBe(true);

    let expectedPermissions: CategoryPermissions = {
      view: [
        {id: 1, label: 'Manager'},
        {id: 2, label: 'IT Support'},
        {id: 3, label: 'Redactor'},
        {id: 4, label: 'Furniture Manager'},
      ],
      edit: [{id: 4, label: 'Furniture Manager'}],
      own: [],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);

    act(() => {
      result.current.onChangePermissions(userGroups, 'own', [1]);
    });

    // no changes in view and edit because they already has the user group 1
    expectedPermissions = {
      view: [
        {id: 1, label: 'Manager'},
        {id: 2, label: 'IT Support'},
        {id: 3, label: 'Redactor'},
        {id: 4, label: 'Furniture Manager'},
      ],
      edit: [
        {id: 1, label: 'Manager'},
        {id: 4, label: 'Furniture Manager'},
      ],
      own: [{id: 1, label: 'Manager'}],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions(userGroups, 'view', [1, 2, 3]);
    });

    // no changes in view and edit because they already has the user group 1
    expectedPermissions = {
      view: [
        {id: 1, label: 'Manager'},
        {id: 2, label: 'IT Support'},
        {id: 3, label: 'Redactor'},
      ],
      edit: [{id: 1, label: 'Manager'}],
      own: [{id: 1, label: 'Manager'}],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions(userGroups, 'edit', [1, 2]);
    });

    expect(result.current.isModified).toBe(false);
    expect(categoriesAreEqual(result.current.category!, aCategory)).toBe(true);
  });

  test('it changes the own permissions of a category', () => {
    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions(userGroups, 'own', [4]);
    });

    expect(result.current.isModified).toBe(true);

    // user group 4 is now include in view and edit
    const expectedPermissions: CategoryPermissions = {
      view: [
        {id: 1, label: 'Manager'},
        {id: 2, label: 'IT Support'},
        {id: 3, label: 'Redactor'},
        {id: 4, label: 'Furniture Manager'},
      ],
      edit: [
        {id: 1, label: 'Manager'},
        {id: 2, label: 'IT Support'},
        {id: 4, label: 'Furniture Manager'},
      ],
      own: [{id: 4, label: 'Furniture Manager'}],
    };
    expect(permissionsAreEqual(result.current.category!.permissions, expectedPermissions)).toBe(true);
    expect(result.current.isModified).toBe(true);

    act(() => {
      result.current.onChangePermissions(userGroups, 'view', [1, 2, 3]); // edit and own will loose user group 4
    });

    act(() => {
      result.current.onChangePermissions(userGroups, 'own', [1]);
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
      error: {
        message: '',
      },
    });

    const modifiedCategory: EnrichCategory = set(['properties', 'labels', 'en_US'], 'Foo', aCategory);

    const {result} = renderUseEditCategoryForm(42);

    act(() => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
    });

    await act(async () => {
      await result.current.saveCategory();
    });

    expect(result.current.isModified).toBe(true);
    expect(result.current.category).toStrictEqual(modifiedCategory);
  });
});
