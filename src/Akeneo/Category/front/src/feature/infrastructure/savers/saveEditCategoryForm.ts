import {Router} from '@akeneo-pim-community/shared';

import {EnrichCategory} from '../../models';

interface EditCategoryResponseOK {
  success: true;
  category: EnrichCategory;
}

interface EditCategoryResponseKO {
  success: false;
  errors: EditCategoryValidationErrors;
}

interface EditCategoryValidationErrors {
  general?: I18nMessageSpec,
  validation?: {
    properties?: ValidationErrors,
    attributes?: ValidationErrors,
  }
}

interface ValidationErrors {
  [attributeCode: string]: ValidationError,
}

interface ValidationError {
  path: string[];
  locale?: string;
  message: I18nMessageSpec;
}

interface I18nMessageSpec {
  key: string;
  args?: {
    [name: string]: string | number
  }
}

type EditCategoryResponse = EditCategoryResponseOK | EditCategoryResponseKO;

const saveEditCategoryForm = async (router: Router, enrichCategory: EnrichCategory): Promise<EditCategoryResponse> => {
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

  let responseContent: EditCategoryResponse | null = null;
  try {
    // *ideally* in case of 4XX or 5XX the response should always be valid json
    responseContent = await response.json();
  } catch (e) {}

  if (responseContent && response.ok) {
    const category = (responseContent as EditCategoryResponseOK).category;
    return {success: true, category};
  }

  // TODO use(/create) real i18n keys below
  const errors = responseContent
    ? (responseContent as EditCategoryResponseKO).errors || { general: { key: 'unspecified error'}}
    : { general: { key: 'Response is invalid JSON' } };
  return {success: false, errors};
};

export {saveEditCategoryForm};
