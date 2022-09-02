import {useCallback} from 'react';
import {useRouter} from '@akeneo-pim-community/shared';
import {Category} from '../models';

const useCategoryChildrenFetcher = () => {
  const router = useRouter();

  return useCallback(
    async (parentId: number): Promise<Category[]> => {
      const route = router.generate(`pim_enrich_categorytree_children`, {
        _format: 'json',
        id: parentId,
      });

      const response = await fetch(route, {
        headers: {
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });

      const children = await response.json();

      return children.map((child: any): Category => {
        return {
          id: parseInt(child.attr.id.replace('node_', '')),
          code: child.attr['data-code'],
          label: child.data,
          isLeaf: child.state === 'leaf',
        };
      });
    },
    [router]
  );
};

export {useCategoryChildrenFetcher};
