const Routing = require('routing');

type MovedCategory = {
  identifier: number;
  parentId: number;
  previousCategoryId: number | null;
};

const moveCategory = async (movedCategory: MovedCategory): Promise<boolean> => {
  const url = Routing.generate('pim_enrich_categorytree_movenode', {
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
