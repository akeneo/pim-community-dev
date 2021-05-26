import {EditCategoryForm} from "../../hooks";
import {Category} from "../../models";

const Routing = require('routing');

type EditCategoryResponse = {
  success: boolean;
  form: EditCategoryForm;
};

const saveEditCategoryForm = async (categoryId: number, formData: EditCategoryForm): Promise<EditCategoryResponse> => {
  const params = new URLSearchParams();
  params.append(formData._token.fullName, formData._token.value);
  for (const [locale, changedLabel] of Object.entries(formData.label)) {
    params.append(formData.label[locale].fullName, changedLabel.value);
  }
  if (formData.permissions) {
    for (const [permissionField, changedPermission] of Object.entries(formData.permissions)) {
      // @todo Find how to handle apply_on_children when its value is "0" (unchecked)
      if (permissionField === 'apply_on_children') {
        // @fixme
        if (changedPermission.value === '1') {
          params.append(formData.permissions[permissionField].fullName, changedPermission.value);
        }
      } else {
        changedPermission.value.map(value => params.append(formData.permissions[permissionField].fullName, value));
      }
    }
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
  };
};

export {saveEditCategoryForm};
