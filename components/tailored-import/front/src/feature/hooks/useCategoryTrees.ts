import {useEffect, useState} from 'react';
import {useRoute} from '@akeneo-pim-community/shared';
import {CategoryTree} from '../models';
import {filterErrors, ValidationError} from '@akeneo-pim-community/shared/lib/models/validation-error';

const useCategoryTrees = (validationErrors: ValidationError[]): CategoryTree[] => {
  const [categoryTrees, setCategoryTrees] = useState<CategoryTree[]>([]);
  const categoryTreesRoute = useRoute('pimee_tailored_import_get_category_trees_action');

  useEffect(() => {
    const categoryCodesWithError = filterErrors(validationErrors, '[mapping]').map(validationError =>
      validationError.propertyPath.slice(1, -1)
    );

    const fetchCategoryTrees = async () => {
      const response = await fetch(categoryTreesRoute, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
          category_codes_with_error: categoryCodesWithError,
        }),
      });

      const result = await response.json();

      setCategoryTrees(result);
    };

    fetchCategoryTrees();
  }, [categoryTreesRoute, validationErrors]);

  return categoryTrees;
};

export {useCategoryTrees};
