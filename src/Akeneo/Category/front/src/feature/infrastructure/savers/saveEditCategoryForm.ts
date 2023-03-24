import {Router, translate} from '@akeneo-pim-community/shared';
import {set} from 'lodash/fp';

import {EnrichCategory} from '../../models';

interface EditCategoryResponseOK {
  success: true;
  category: EnrichCategory;
}

interface EditCategoryResponseKO {
  success: false;
  error: {
    code?: string;
    message: string;
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

  if (responseContent) {
    if (Array.isArray(responseContent)) {
      for (let i = 0; i < responseContent.length; i++) {
        return {success: false, error: (responseContent[i] as EditCategoryResponseKO).error};
      }
    }

    return {success: false, error: (responseContent as EditCategoryResponseKO).error};
  }

  return {success: false, error: {message: translate('pim_enrich.entity.category.content.edit.fail')}};
};

export {saveEditCategoryForm};
