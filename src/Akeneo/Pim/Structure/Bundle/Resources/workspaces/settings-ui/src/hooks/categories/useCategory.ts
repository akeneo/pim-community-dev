import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import {Category, EditCategoryForm} from '../../models';

type EditCategoryData = {
  category: Category;
  form: EditCategoryForm;
};

const useCategory = (
  categoryId: number
): [data: EditCategoryData | null, fetch: () => Promise<void>, status: FetchStatus, error: string | null] => {
  const url = useRoute('pim_enrich_categorytree_edit', {
    id: categoryId.toString(),
  });

  const [categoryData, load, status, error] = useFetch<EditCategoryData>(url);

  return [categoryData, load, status, error];
};

export {useCategory, EditCategoryForm};
