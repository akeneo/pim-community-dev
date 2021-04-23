const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_categorytree_create';

type ValidationErrors = {
  [fieldCode: string]: string;
};

const saveNewCategoryTree = async (code: string): Promise<ValidationErrors> => {
  const response = await fetch(Routing.generate(ROUTE_NAME), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/x-www-form-urlencoded',
      Accept: 'application/json',
    },
    body: JSON.stringify({code}),
  });

  if (!response.ok) {
    return await response.json();
  }

  return {};
};

export {saveNewCategoryTree, ValidationErrors};
