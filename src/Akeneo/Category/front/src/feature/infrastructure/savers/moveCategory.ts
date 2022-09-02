import {Router} from '@akeneo-pim-community/shared';

type MovedCategory = {
  identifier: number;
  parentId: number;
  previousCategoryId: number | null;
};

const moveCategory = async (router: Router, movedCategory: MovedCategory): Promise<boolean> => {
  const url = router.generate('pim_enrich_categorytree_movenode', {
    id: movedCategory.identifier.toString(),
    parent: movedCategory.parentId.toString(),
    prev_sibling: movedCategory.previousCategoryId ? movedCategory.previousCategoryId.toString() : '',
  });

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: [['X-Requested-With', 'XMLHttpRequest']],
    });

    return response.ok;
  } catch (e) {
    return false;
  }
};

export {moveCategory};
