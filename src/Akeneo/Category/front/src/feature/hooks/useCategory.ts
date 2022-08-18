import {FetchStatus, useFetch, useRoute} from '@akeneo-pim-community/shared';
import {EnrichCategory} from '../models';
import type {EditCategoryForm} from '../models';
import {useCallback, useState} from 'react';

const useCategory = (
  categoryId: number
): [data: EnrichCategory | null, fetch: () => Promise<void>, status: FetchStatus, error: string | null] => {
  // const [data] = useState<EnrichCategory | null>({
  //   id: categoryId,
  //   code: 'toto',
  //   labels: {
  //     'en_US': 'socks',
  //     'fr_FR': 'chaussettes',
  //   },
  //   attributes: [],
  //   permissions: null
  // });
  // const load = useCallback(async () => {}, []);
  // const [status] = useState<FetchStatus>('fetched');
  // const [error] = useState<string | null>(null);

  //TODO: get token
  const url = useRoute('pim_enrich_category_rest_get', {
    id: categoryId.toString(),
  });

  const [categoryData, load, status, error] = useFetch<any>(url);

  console.log(categoryData);

  // const data = {
  //   id: categoryId,
  //   code: 'toto',
  //   labels: {
  //     'en_US': 'socks',
  //     'fr_FR': 'chaussettes',
  //   },
  //   attributes: [],
  //   permissions: null
  // }

  // TODO: get token
  // const url = useRoute('pim_enrich_categorytree_edit', {
  //   id: categoryId.toString(),
  // });

  // const [categoryData, load, status, error] = useFetch<EditCategoryData>(url);

  return [categoryData, load, status, error];
};

export {useCategory};
export type {EditCategoryForm};
