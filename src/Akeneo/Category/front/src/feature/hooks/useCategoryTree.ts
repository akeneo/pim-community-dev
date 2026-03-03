import {useRoute} from '@akeneo-pim-community/shared';
import {useQuery} from 'react-query';
import {BackendCategoryTree, convertToCategoryTree} from '../models';
import {apiFetch} from '../tools/apiFetch';

export const useCategoryTree = (treeId: string) => {
  const url = useRoute('pim_enrich_categorytree_children', {
    _format: 'json',
    id: treeId,
    select_node_id: '-1',
    with_items_count: '0',
    include_parent: '1',
    include_sub: '0',
    context: 'manage',
  });

  return useQuery(['get-category-tree', treeId], async () => {
    const tree = await apiFetch<BackendCategoryTree>(url, {});

    return convertToCategoryTree(tree);
  });
};
