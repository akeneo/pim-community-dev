import {useCallback} from 'react';
import {
  CategoryResponse,
  CategoryTreeModel,
  CategoryTreeRoot,
  parseResponse,
  useRouter
} from '@akeneo-pim-community/shared';

const useCategoryTree: (currentTree: CategoryTreeRoot | undefined) => {
  init: () => Promise<CategoryTreeModel>,
  childrenCallback: (id: number) => Promise<CategoryTreeModel[]>,
} = currentTree => {
  const router = useRouter();

  const getChildrenUrl = useCallback((id: number) => {
    return router.generate('pim_enrich_categorytree_children', {_format: 'json', id});
  }, [router]);

  const init = useCallback(async () => {
    if (currentTree) {
      const response = await fetch(getChildrenUrl(currentTree.id));
      const json: CategoryResponse[] = await response.json();

      return {
        id: currentTree.id,
        code: currentTree.code,
        label: currentTree.label,
        selectable: false,
        children: json.map(child =>
          parseResponse(child, {
            selectable: true,
          })
        ),
      };
    }
    throw new Error('Not possible');
  }, [currentTree, getChildrenUrl]);

  const childrenCallback = useCallback(async (id: number) => {
    const response = await fetch(getChildrenUrl(id));
    const json: CategoryResponse[] = await response.json();

    return json.map(child =>
      parseResponse(child, {
        selectable: true,
      })
    );
  }, [getChildrenUrl]);

  return {init, childrenCallback};
};

export {useCategoryTree};
