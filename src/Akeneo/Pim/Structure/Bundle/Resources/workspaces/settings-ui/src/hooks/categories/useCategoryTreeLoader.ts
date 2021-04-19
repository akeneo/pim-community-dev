import {useCallback} from "react";
import {useRouter} from "@akeneo-pim-community/legacy-bridge";
import {baseFetcher, CategoryTreeModel} from "@akeneo-pim-community/shared";
import {CategoryResponse, parseResponse} from "pimui/js/CategoryTreeFetcher";

const useCategoryTreeLoader = (treeId: number) => {
  const router = useRouter();

  const generateUrl = useCallback((id: number, isRoot: boolean = false) => {
    return router.generate('pim_enrich_categorytree_children', {
      _format: 'json',
      id: id.toString(),
      select_node_id: isRoot ? id.toString() : '-1',
      with_items_count: '0',
      include_parent: isRoot ? '1' : '0',
      include_sub: isRoot ? '1' : '0',
    })
  }, [router]);

  const loadRoot = useCallback(async () => {
    const url = generateUrl(treeId, true);
    const data: CategoryResponse = await baseFetcher(url);

    return parseResponse({
      ...data,
      state: data.state.replace('closed'),
    }, {
      isRoot: true,
    });
  }, [treeId]);

  const loadChildren = useCallback(async (categoryId: number): Promise<CategoryTreeModel[]> => {
    const url = generateUrl(categoryId);
    const data: CategoryResponse[] = await baseFetcher(url);

    return data.map((child) => parseResponse(child));
  }, []);

  return {
    loadRoot,
    loadChildren
  }
}

export {useCategoryTreeLoader}
