import {useFetch, useRoute} from '@akeneo-pim-community/shared';

const useCountProductsByCategory = (categoryId: number) => {
  const url = useRoute('pim_enrich_categorytree_count_category_products', {id: categoryId.toString()});
  const [numberOfProducts, loadNumberOfProducts] = useFetch(url);

  return {numberOfProducts, loadNumberOfProducts};
};

export {useCountProductsByCategory};
