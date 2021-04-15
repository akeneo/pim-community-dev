import {useRoute} from '@akeneo-pim-community/legacy-bridge';
import {useFetch} from '@akeneo-pim-community/shared';
import {Category} from '../../models';

const useCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_edit', {
    id: categoryId.toString(),
  });

  const {data, fetch, error, status} = useFetch<Category>(url);

  return {
    category: data,
    load: fetch,
    status,
    error,
  };
};

export {useCategory};
