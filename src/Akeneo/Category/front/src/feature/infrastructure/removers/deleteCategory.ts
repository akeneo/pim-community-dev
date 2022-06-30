import {Router} from '@akeneo-pim-community/shared';

const ROUTE_NAME = 'pim_enrich_categorytree_remove';

type Response = {
  ok: boolean;
  errorMessage: string;
};

const deleteCategory = async (router: Router, id: number): Promise<Response> => {
  const response = await fetch(router.generate(ROUTE_NAME, {id}), {
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
