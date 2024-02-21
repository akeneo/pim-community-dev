import {useCallback} from 'react';
import {
  CategoryResponse,
  CategoryTreeModel,
  CategoryTreeRoot,
  parseResponse,
  useRouter,
} from '@akeneo-pim-community/shared';

const useCategoryTree: (currentTree: CategoryTreeRoot | undefined) => {
  init: () => Promise<CategoryTreeModel>;
  childrenCallback: (id: number) => Promise<CategoryTreeModel[]>;
} = currentTree => {
  const router = useRouter();

  const getChildrenUrl = useCallback(
    (id: number) => {
      return router.generate('pim_enrich_categorytree_children', {_format: 'json', id});
    },
    [router]
  );

  const init = useCallback(async () => {
    const validCurrentTree = currentTree as CategoryTreeRoot;
    const response = await fetch(getChildrenUrl(validCurrentTree.id), {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });
    const json: CategoryResponse[] = await response.json();

    return {
      id: validCurrentTree.id,
      code: validCurrentTree.code,
      label: validCurrentTree.label,
      selectable: false,
      children: json.map(child =>
        parseResponse(child, {
          selectable: true,
        })
      ),
    };
  }, [currentTree, getChildrenUrl]);

  const childrenCallback = useCallback(
    async (id: number) => {
      const response = await fetch(getChildrenUrl(id), {
        method: 'GET',
        headers: [['X-Requested-With', 'XMLHttpRequest']],
      });
      const json: CategoryResponse[] = await response.json();

      return json.map(child =>
        parseResponse(child, {
          selectable: true,
        })
      );
    },
    [getChildrenUrl]
  );

  return {init, childrenCallback};
};

export {useCategoryTree};
