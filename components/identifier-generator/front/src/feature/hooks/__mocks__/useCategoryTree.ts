import {CategoryTreeModel, CategoryTreeRoot} from '@akeneo-pim-community/shared';

const useCategoryTree: (currentTree: CategoryTreeRoot | undefined) => {
  init: () => Promise<CategoryTreeModel>;
  childrenCallback: (id: number) => Promise<CategoryTreeModel[]>;
} = () => {
  const init = () => {
    return {
      code: 'masterCatalog',
      id: 42,
      label: 'Master Catalog',
      selectable: false,
      children: [
        {
          children: [],
          code: 'subCategory',
          id: 69,
          label: 'Sub category',
          readOnly: false,
          selectable: true,
          selected: false,
        },
      ],
    };
  };

  const childrenCallback = () => {
    const child: CategoryTreeModel = {
      children: [],
      code: 'subCategory',
      id: 69,
      label: 'Sub category',
      readOnly: false,
      selectable: true,
      selected: false,
    };
    return [child];
  };

  return {init, childrenCallback};
};

export {useCategoryTree};
