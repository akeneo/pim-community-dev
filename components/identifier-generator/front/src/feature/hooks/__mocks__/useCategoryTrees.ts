import {useState, useEffect} from 'react';
import {CategoryTreeRoot} from '@akeneo-pim-community/shared';

const useCategoryTrees: (callback: (categoryTreeRoot: CategoryTreeRoot) => void) => CategoryTreeRoot[] = callback => {
  const [isLoaded, setIsLoaded] = useState<boolean>(false);

  useEffect(() => {
    callback({
      id: 69,
      code: 'print',
      label: 'Print',
      selected: true,
    });
    setIsLoaded(true);
  }, [callback, setIsLoaded]);

  return isLoaded
    ? [
        {
          id: 42,
          code: 'masterCatalog',
          label: 'Master Catalog',
          selected: false,
        },
        {
          id: 69,
          code: 'print',
          label: 'Print',
          selected: true,
        },
      ]
    : [];
};

export {useCategoryTrees};
