import {useFetch, useRoute} from '@akeneo-pim-community/shared';

const useCountProductsByCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_count_category_products', {id: categoryId.toString()});
  const {data, fetch} = useFetch(url);

  return {numberOfProducts: data, loadNumberOfProducts: fetch};
};

export {useCountProductsByCategory};
