import {Router, LocaleCode} from '@akeneo-pim-community/shared';

const ROUTE_NAME = 'pim_enrich_categorytree_create';

type ValidationErrors = {
  [fieldCode: string]: string;
};

type Category = {
  code: string;
  parent?: string;
  labels?: {
    [locale: string]: string | undefined;
  };
};

const createCategory = async (
  router: Router,
  code: string,
  parent?: string,
  locale?: LocaleCode,
  label?: string
): Promise<ValidationErrors> => {
  let category: Category = {
    code: code,
    parent: parent,
  };

  if (locale && label) {
    category = {
      ...category,
      labels: {
        [locale]: label,
      },
    };
  }

  const response = await fetch(router.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: JSON.stringify(category),
  });

  if (!response.ok) {
    return await response.json();
  }

  return {};
};

export {createCategory};
export type {ValidationErrors};
