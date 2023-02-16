import {Router} from '@akeneo-pim-community/shared';
import {set} from 'lodash/fp';

import {EnrichCategory} from '../../models';
import {CategoryPermissions} from '../../models/CategoryPermission';

interface EditCategoryResponseOK {
  success: true;
  category: EnrichCategory;
}

interface EditCategoryResponseKO {
  success: false;
  errors: EditCategoryValidationErrors;
}

interface EditCategoryValidationErrors {
  general?: I18nMessageSpec;
  validation?: {
    properties?: ValidationErrors;
    attributes?: ValidationErrors;
  };
}

interface ValidationErrors {
  [attributeCode: string]: ValidationError;
}

interface ValidationError {
  path: string[];
  locale?: string;
  message: I18nMessageSpec;
}

interface I18nMessageSpec {
  key: string;
  args?: {
    [name: string]: string | number;
  };
}

type EditCategoryResponse = EditCategoryResponseOK | EditCategoryResponseKO;

interface SaveOptions {
  applyPermissionsOnChildren: boolean;
  populateResponseCategory: (c: EnrichCategory) => EnrichCategory;
}

const saveEditCategoryForm = async (
  router: Router,
  category: EnrichCategory,
  options: SaveOptions
): Promise<EditCategoryResponse> => {
  const {applyPermissionsOnChildren, populateResponseCategory = x => x} = options;

  // this is for keeping compatibility at the moment, ideally it should not go into the category data
  // because it is a modality for saving, not a part of a category state
  let payload = set(['permissions', 'apply_on_children'], applyPermissionsOnChildren, category);

  const response = await fetch(router.generate('pim_enriched_category_rest_update', {id: category.id}), {
    method: 'POST',
    headers: [
      ['Content-type', 'application/json'],
      ['X-Requested-With', 'XMLHttpRequest'],
    ],
    body: JSON.stringify(payload),
  });

  let responseContent: EditCategoryResponse | null = null;
  try {
    // *ideally* in case of 4XX or 5XX the response should always be valid json
    responseContent = await response.json();
  } catch (e) {}

  if (responseContent && response.ok) {
    const category = (responseContent as EditCategoryResponseOK).category;

    return {success: true, category: populateResponseCategory(category)};
  }

  // TODO use(/create) real i18n keys below
  const errors = responseContent
    ? (responseContent as EditCategoryResponseKO).errors || {general: {key: 'unspecified error'}}
    : {general: {key: 'Response is invalid JSON'}};
  return {success: false, errors};
};

export {saveEditCategoryForm};
