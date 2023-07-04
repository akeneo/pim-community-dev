import {EditCategoryForm} from '../models';
import {computeNewEditPermissions, computeNewOwnPermissions, computeNewViewPermissions} from './permissionsHelper';

describe('permissionsHelper', () => {
  //This test is only to have a better idea of the default state before each test
  test('it ensures the default state is OK', () => {
    expect(formData.permissions?.view.value).toStrictEqual(['1', '2', '5']);
    expect(formData.permissions?.edit.value).toStrictEqual(['1', '2']);
    expect(formData.permissions?.own.value).toStrictEqual(['1', '2']);
  });

  test('it removes a view permission that does not exist in other levels', () => {
    const newFormData = computeNewViewPermissions(formData, ['1', '2']);

    expect(newFormData.permissions?.view.value).toStrictEqual(['1', '2']);
    expect(newFormData.permissions?.edit.value).toStrictEqual(['1', '2']);
    expect(newFormData.permissions?.own.value).toStrictEqual(['1', '2']);
  });

  test('it removes with cascade a view permission', () => {
    const newFormData = computeNewViewPermissions(formData, ['1', '5']);

    expect(newFormData.permissions?.view.value).toStrictEqual(['1', '5']);
    expect(newFormData.permissions?.edit.value).toStrictEqual(['1']);
    expect(newFormData.permissions?.own.value).toStrictEqual(['1']);
  });

  test('it removes with cascade an edit permission', () => {
    const newFormData = computeNewEditPermissions(formData, ['2']);

    expect(newFormData.permissions?.view.value).toStrictEqual(['1', '2', '5']);
    expect(newFormData.permissions?.edit.value).toStrictEqual(['2']);
    expect(newFormData.permissions?.own.value).toStrictEqual(['2']);
  });

  test('it adds with cascade an edit permission', () => {
    const newFormData = computeNewEditPermissions(formData, ['1', '2', '8']);

    expect(newFormData.permissions?.view.value).toStrictEqual(['1', '2', '5', '8']);
    expect(newFormData.permissions?.edit.value).toStrictEqual(['1', '2', '8']);
    expect(newFormData.permissions?.own.value).toStrictEqual(['1', '2']);
  });

  test('it adds with cascade an own permission', () => {
    const newFormData = computeNewOwnPermissions(formData, ['1', '2', '8']);

    expect(newFormData.permissions?.view.value).toStrictEqual(['1', '2', '5', '8']);
    expect(newFormData.permissions?.edit.value).toStrictEqual(['1', '2', '8']);
    expect(newFormData.permissions?.own.value).toStrictEqual(['1', '2', '8']);
  });
});

const formData: EditCategoryForm = {
  label: {
    en_US: {
      value: 'Cameras',
      fullName: 'pim_category[label][en_US]',
      label: 'English (United States)',
    },
  },
  errors: [],
  _token: {
    value: 'XFC_DnwJvzF5TsnB2-MbiPUjKqmUtZTJt0O1CTQLqMs',
    fullName: 'pim_category[_token]',
  },
  permissions: {
    view: {
      value: ['1', '2', '5'],
      fullName: 'pim_category[permissions][view][]',
      choices: [],
    },
    edit: {
      value: ['1', '2'],
      fullName: 'pim_category[permissions][edit][]',
      choices: [],
    },
    own: {
      value: ['1', '2'],
      fullName: 'pim_category[permissions][own][]',
      choices: [],
    },
    apply_on_children: {
      value: '1',
      fullName: 'pim_category[permissions][apply_on_children]',
    },
  },
};
