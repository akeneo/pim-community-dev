import {act} from 'react-test-renderer';
import {renderHookWithProviders} from '@akeneo-pim-community/shared';
import {useCategory, useEditCategoryForm} from '@akeneo-pim-community/settings-ui';
import {aCategory} from '../../../utils/provideCategoryHelper';
import {saveEditCategoryForm} from "@akeneo-pim-community/settings-ui/src/infrastructure/savers";

jest.mock('@akeneo-pim-community/settings-ui/src/hooks/categories/useCategory');
jest.mock('@akeneo-pim-community/settings-ui/src/infrastructure/savers/saveEditCategoryForm');

describe('useEditCategoryForm', () => {
  beforeEach(() => {
    jest.clearAllMocks();
    jest.resetAllMocks();
  });

  afterAll(() => {
    jest.restoreAllMocks();
  });

  const renderUseEditCategoryForm = (categoryId: number) => {
    return renderHookWithProviders(() => useEditCategoryForm(categoryId));
  };

  test('it returns default values', () => {
    const useCategoryResult = anUseCategoryResult();
    // @ts-ignore
    useCategory.mockReturnValue(useCategoryResult);

    const {result} = renderUseEditCategoryForm(42);
    expect(result.current.categoryLoadingStatus).toBe('fetched');
    expect(result.current.category).toStrictEqual(useCategoryResult.categoryData.category);
    expect(result.current.formData).toStrictEqual(useCategoryResult.categoryData.form);
    expect(result.current.onChangeCategoryLabel).toBeDefined();
    expect(result.current.onChangePermissions).toBeDefined();
    expect(result.current.onChangeApplyPermissionsOnChildren).toBeDefined();
    expect(result.current.saveCategory).toBeDefined();
    expect(result.current.thereAreUnsavedChanges).toBe(false);
  });

  test('it changes the value of a category label', () => {
    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
    });

    expect(result.current.thereAreUnsavedChanges).toBe(true);
    expect(result.current.formData).toStrictEqual({
      ...aCategoryForm,
      label: {...aCategoryForm.label, en_US: {...aCategoryForm.label.en_US, value: 'Foo'}}
    });

    act(() => {
      result.current.onChangeCategoryLabel('en_US', aCategoryForm.label.en_US.value);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(aCategoryForm);
  });

  test('it changes the view permissions of a category', () => {
    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('view', ['1']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(true);
    expect(result.current.formData).toStrictEqual({
      ...aCategoryForm,
      permissions: {
        ...aCategoryForm.permissions,
        view: {...aCategoryForm.permissions.view, value: ['1']},
        edit: {...aCategoryForm.permissions.edit, value: ['1']}
      }
    });

    act(() => {
      result.current.onChangePermissions('edit', ['1', '2']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(aCategoryForm);
  });

  test('it changes the edit permissions of a category', () => {
    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('edit', ['1']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(true);
    expect(result.current.formData).toStrictEqual({
      ...aCategoryForm,
      permissions: {...aCategoryForm.permissions, edit: {...aCategoryForm.permissions.edit, value: ['1']}}
    });

    act(() => {
      result.current.onChangePermissions('edit', ['1', '2']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(aCategoryForm);
  });

  test('it changes the own permissions of a category', () => {
    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangePermissions('own', ['1', '2']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(true);
    expect(result.current.formData).toStrictEqual({
      ...aCategoryForm,
      permissions: {...aCategoryForm.permissions, own: {...aCategoryForm.permissions.own, value: ['1', '2']}}
    });

    act(() => {
      result.current.onChangePermissions('own', ['1']);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(aCategoryForm);
  });

  test('it changes the value of apply permissions on children', () => {
    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangeApplyPermissionsOnChildren(false);
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual({
      ...aCategoryForm,
      permissions: {
        ...aCategoryForm.permissions,
        apply_on_children: {...aCategoryForm.permissions.apply_on_children, value: '0'}
      }
    });

    act(() => {
      result.current.onChangeApplyPermissionsOnChildren(true);
    });
    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(aCategoryForm);
  });

  test('it saves a category and refresh the form data on success', async () => {
    const refreshedForm = {
      ...aCategoryForm,
      label: {...aCategoryForm.label, en_US: {...aCategoryForm.label.en_US, value: 'Foo'}},
      _token: {...aCategoryForm._token, value: 'sNX5OmLv9rpxRmMKU_AE2v46J--2Fpiu9B1vhpEwsqM'}
    };

    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());
    // @ts-ignore
    saveEditCategoryForm.mockResolvedValue({
      success: true,
      form: refreshedForm,
    });

    const {result} = renderUseEditCategoryForm(42);
    await act(async () => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
      result.current.saveCategory();
    });

    expect(result.current.thereAreUnsavedChanges).toBe(false);
    expect(result.current.formData).toStrictEqual(refreshedForm);
  });

  test('it saves a category and refresh the form data on fail', async () => {
    const refreshedForm = {
      ...aCategoryForm,
      errors: ['A random error message'],
      _token: {...aCategoryForm._token, value: 'sNX5OmLv9rpxRmMKU_AE2v46J--2Fpiu9B1vhpEwsqM'}
    };

    // @ts-ignore
    useCategory.mockReturnValue(anUseCategoryResult());
    // @ts-ignore
    saveEditCategoryForm.mockResolvedValue({
      success: false,
      form: refreshedForm,
    });

    const {result} = renderUseEditCategoryForm(42);
    act(() => {
      result.current.onChangeCategoryLabel('en_US', 'Foo');
    });
    await act(async () => {
      result.current.saveCategory();
    });

    expect(result.current.thereAreUnsavedChanges).toBe(true);
    expect(result.current.formData).toStrictEqual({
      ...refreshedForm,
      errors: [],
      label: {...refreshedForm.label, en_US: {...refreshedForm.label.en_US, value: 'Foo'}}
    });
  });

  const aCategoryForm = {
    'label': {
      'en_US': {
        'value': 'Ziggy',
        'fullName': 'pim_category[label][en_US]',
        'label': 'English (United States)'
      },
    },
    'errors': [],
    '_token': {
      'value': 'dm4vOMTG-1OFeMWAeYNLTk1kQwkJa6epzTOez0QImX8',
      'fullName': 'pim_category[_token]'
    },
    'permissions': {
      'view': {
        'value': ['1', '2'],
        'fullName': 'pim_category[permissions][view][]',
        'choices': [
          {
            'label': 'IT support',
            'value': '1'
          },
          {
            'label': 'Manager',
            'value': '2'
          },
        ]
      },
      'edit': {
        'value': ['1', '2'],
        'fullName': 'pim_category[permissions][edit][]',
        'choices': [
          {
            'label': 'IT support',
            'value': '1'
          },
          {
            'label': 'Manager',
            'value': '2'
          },
        ]
      },
      'own': {
        'value': ['1'],
        'fullName': 'pim_category[permissions][own][]',
        'choices': [
          {
            'label': 'IT support',
            'value': '1'
          },
          {
            'label': 'Manager',
            'value': '2'
          },
        ]
      },
      'apply_on_children': {
        'value': '1',
        'fullName': 'pim_category[permissions][apply_on_children]'
      }
    }
  };

  const anUseCategoryResult = () => {
    const root = aCategory('root', {}, 1, null);
    const category = aCategory('cat_1', {en_US: 'Ziggy'}, 42, root);

    return {
      categoryData: {
        category,
        form: aCategoryForm,
      },
      load: () => Promise.resolve('fetched'),
      status: 'fetched',
      error: null,
    };
  };
});
