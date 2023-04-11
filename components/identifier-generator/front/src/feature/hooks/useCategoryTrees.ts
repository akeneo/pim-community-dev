import {useState, useEffect} from 'react';
import {CategoryTreeCode, CategoryTreeModel, CategoryTreeRoot, useRouter} from '@akeneo-pim-community/shared';

const useCategoryTrees: (callback: (categoryTreeRoot: CategoryTreeRoot) => void) => CategoryTreeRoot[] = callback => {
  const router = useRouter();
  const [trees, setTrees] = useState<CategoryTreeRoot[]>([]);

  useEffect(() => {
    const url = router.generate('pim_enrich_categorytree_listtree', {
      _format: 'json',
      dataLocale: undefined,
      include_sub: 0,
      context: 'view',
    });

    fetch(url, {
      method: 'GET',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    }).then(response => {
      response.json().then(json => {
        const trees: CategoryTreeRoot[] = json.map(
          (tree: {
            id: number;
            code: CategoryTreeCode;
            label: string;
            selected: 'true' | 'false';
            tree?: CategoryTreeModel;
          }) => ({...tree, selected: tree.selected === 'true'})
        );
        setTrees(trees);
        const currentTree = trees.find(tree => tree.selected);
        if (currentTree) {
          callback(currentTree);
        }
      });
    });
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [callback]);

  return trees;
};

export {useCategoryTrees};
