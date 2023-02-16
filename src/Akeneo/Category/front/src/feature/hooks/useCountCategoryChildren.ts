import {useMemo} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';

type Result = {
  data: number | undefined;
  isLoading: boolean;
};

export const useCountCategoryChildren = (categoryId: number): Result => {
  const router = useRouter();

  const url = useMemo(() => router.generate('pim_enrich_category_rest_list_children', {id: categoryId}), [categoryId]);

  const countCategoryChildren = async (categoryId: number): Promise<number> => {
    return fetch(url).then(response => response.json().then((categoriesIds: number[]) => categoriesIds.length));
  };

  return useQuery(['countCategoryChildren', categoryId], () => countCategoryChildren(categoryId));
};
