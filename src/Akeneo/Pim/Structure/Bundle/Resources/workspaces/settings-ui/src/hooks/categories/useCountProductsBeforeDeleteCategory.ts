import {useCountProductsByCategory} from './useCountProductsByCategory';
import {useEffect, useState} from 'react';

type CategoryDeletion = {
  callback: (nbProducts: number) => void;
  status: 'pending' | 'ready';
};

// Wrap the category deletion to count its number of products at the last moment.
const useCountProductsBeforeDeleteCategory = (categoryId: number) => {
  const [categoryDeletion, setCategoryDeletion] = useState<CategoryDeletion | null>(null);
  const {numberOfProducts, loadNumberOfProducts} = useCountProductsByCategory(categoryId);

  const beforeDelete = (deleteCategory: (nbProducts: number) => void) => {
    setCategoryDeletion({callback: deleteCategory, status: numberOfProducts !== null ? 'ready' : 'pending'});
  };

  useEffect(() => {
    if (categoryDeletion === null) {
      return;
    }

    if (categoryDeletion.status === 'pending') {
      loadNumberOfProducts();
      return;
    }

    if (categoryDeletion.status === 'ready' && numberOfProducts !== null) {
      categoryDeletion.callback(numberOfProducts as number);
      setCategoryDeletion(null);
    }
  }, [categoryDeletion]);

  useEffect(() => {
    if (categoryDeletion !== null && numberOfProducts !== null) {
      setCategoryDeletion({...categoryDeletion, status: 'ready'});
    }
  }, [numberOfProducts]);

  return beforeDelete;
};

export {useCountProductsBeforeDeleteCategory};
