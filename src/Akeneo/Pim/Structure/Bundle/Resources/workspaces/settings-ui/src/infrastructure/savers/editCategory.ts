import {EditCategoryForm} from "../../hooks";
import {Category} from "../../models";

const Routing = require('routing');

type EditCategoryResponse = {
  success: boolean;
  form: EditCategoryForm;
};

// @todo rename?
// @todo define a type for "data"
// @todo fix dependency to hooks for EditCategoryResponse
const editCategory = async (id: number, formData: EditCategoryForm): Promise<EditCategoryResponse> => {
  // @todo: find a better way to do that
  let editedFormData = {};
  editedFormData[formData._token.fullName] = formData._token.value;
  for (const [locale, changedLabel] of Object.entries(formData.label)) {
    editedFormData[formData.label[locale].fullName] = changedLabel.value;
  }

  console.log('edit Category', editedFormData);

  const response = await fetch(Routing.generate('pim_enrich_categorytree_edit', {id}), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
    },
    body: new URLSearchParams(editedFormData),
  });

  const responseContent = await response.json();

  return {
    success: response.ok,
    form: responseContent.form,
  };
};

export {editCategory};
