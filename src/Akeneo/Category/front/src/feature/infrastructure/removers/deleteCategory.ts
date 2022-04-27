const Routing = require('routing');

const ROUTE_NAME = 'pim_enrich_categorytree_remove';

type Response = {
  ok: boolean;
  errorMessage: string;
};

const deleteCategory = async (categoryId: number): Promise<Response> => {
  const response = await fetch(Routing.generate(ROUTE_NAME, {id: categoryId}), {
    method: 'DELETE',
    headers: [['X-Requested-With', 'XMLHttpRequest']],
  });

  const errorMessage = response.ok ? '' : (await response.json()).message;

  return {
    ok: response.ok,
    errorMessage,
  };
};

export {deleteCategory};
