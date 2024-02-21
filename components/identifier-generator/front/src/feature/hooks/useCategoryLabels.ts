import {useState, useEffect} from 'react';
import {CategoryCode, useRouter} from '@akeneo-pim-community/shared';

type CategoryLabels = {[categoryCode: string]: string | null};

const useCategoryLabels: (categoryCodes: CategoryCode[]) => CategoryLabels = categoryCodes => {
  const router = useRouter();
  const [categoryLabels, setCategoryLabels] = useState<CategoryLabels>({});

  useEffect(() => {
    fetch(router.generate('akeneo_identifier_generator_get_category_labels', {categoryCodes}), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(response => {
      response.json().then(json => {
        setCategoryLabels(
          categoryCodes.reduce((categoryCodes, categoryCode) => {
            categoryCodes[categoryCode] = json[categoryCode] ?? null;

            return categoryCodes;
          }, {})
        );
      });
    });
  }, [categoryCodes, router]);

  return categoryLabels;
};

export {useCategoryLabels};
