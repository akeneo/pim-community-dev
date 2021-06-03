import {EditCategoryForm} from '../../hooks';
import {Category} from '../../models';

const Routing = require('routing');

type EditCategoryResponse = {
  success: boolean;
  form: EditCategoryForm;
  category: Category;
};

const saveEditCategoryForm = async (categoryId: number, formData: EditCategoryForm): Promise<EditCategoryResponse> => {
  const params = new URLSearchParams();
  params.append(formData._token.fullName, formData._token.value);
  for (const [locale, changedLabel] of Object.entries(formData.label)) {
    params.append(formData.label[locale].fullName, changedLabel.value);
  }

  if (formData.permissions) {
    const permissions = formData.permissions;
    if (permissions.apply_on_children.value === '1') {
      params.append(permissions.apply_on_children.fullName, permissions.apply_on_children.value);
    }
    formData.permissions.view.value.map(value => params.append(permissions.view.fullName, value));
    formData.permissions.edit.value.map(value => params.append(permissions.edit.fullName, value));
    formData.permissions.own.value.map(value => params.append(permissions.own.fullName, value));
  }

  const response = await fetch(Routing.generate('pim_enrich_categorytree_edit', {id: categoryId}), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: params,
  });

  const responseContent = await response.json();

  return {
    success: response.ok,
    form: responseContent.form,
    category: responseContent.category,
  };
};

export {saveEditCategoryForm};
