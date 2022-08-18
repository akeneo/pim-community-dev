import {Router} from '@akeneo-pim-community/shared';

import {EnrichCategory} from '../../models';

type EditCategoryResponse = {
  success: boolean;
  category?: EnrichCategory;
  error?: string
};

const saveEditCategoryForm = async (
  router: Router,
  enrichCategory: EnrichCategory
): Promise<EditCategoryResponse> => {

  console.log(enrichCategory);
  // const params = new URLSearchParams();
  // params.append(formData._token.fullName, formData._token.value);
  // for (const [locale, changedLabel] of Object.entries(formData.label)) {
  //   params.append(formData.label[locale].fullName, changedLabel.value);
  // }
  //
  //
  // if (formData.permissions) {
  //   const permissions = formData.permissions;
  //   if (permissions.apply_on_children.value === '1') {
  //     params.append(permissions.apply_on_children.fullName, permissions.apply_on_children.value);
  //   }
  //   formData.permissions.view.value.map(value => params.append(permissions.view.fullName, value));
  //   formData.permissions.edit.value.map(value => params.append(permissions.edit.fullName, value));
  //   formData.permissions.own.value.map(value => params.append(permissions.own.fullName, value));
  // }

  const response = await fetch(router.generate('pim_category_rest_update', {id: enrichCategory.id}), {
    method: 'POST',
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    body: JSON.stringify(enrichCategory),
  });

  const responseContent = await response.json();

  console.log(responseContent);

  return {
    success: response.ok,
    category: responseContent.category,
  };
};

export {saveEditCategoryForm};
