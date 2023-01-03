import {Router} from '@akeneo-pim-community/shared';
import {CategoryTreeModel} from '../../models';

const createTemplate = async (categoryTree: CategoryTreeModel, catalogLocale: string, router: Router) => {
  const data = {
    code: categoryTree.code + '_template',
  };

  const url = router.generate('pim_category_template_rest_create', {
    categoryTreeId: categoryTree.id,
  });

  return fetch(url, {
    method: 'POST',
    body: JSON.stringify(data),
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
  });
};

export {createTemplate};
