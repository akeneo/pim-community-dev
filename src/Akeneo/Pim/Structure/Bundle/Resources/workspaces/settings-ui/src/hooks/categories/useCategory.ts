import {useFetch, useRoute} from '@akeneo-pim-community/shared';
import {Category, EditCategoryForm} from '../../models';

type EditCategoryData = {
  category: Category;
  form: EditCategoryForm;
};

const useCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_edit', {
    id: categoryId.toString(),
  });

  const {data, fetch, error, status} = useFetch<EditCategoryData>(url);

  return {
    categoryData: data,
    load: fetch,
    status,
    error,
  };
};

export {useCategory, EditCategoryForm};
