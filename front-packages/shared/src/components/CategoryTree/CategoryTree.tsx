import React from 'react';
import {CategoryValue, RecursiveCategoryTree} from './RecursiveCategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type CategoryTreeModel = {
  id: number;
  code: string;
  label: string;
  selectable: boolean;
  loading?: boolean;
  selected?: boolean;
  readOnly?: boolean;
  children?: CategoryTreeModel[];
};

type CategoryTreeProps = {
  categoryTreeCode?: string;
  init: (categoryTreeCode?: string) => Promise<CategoryTreeModel>;
  childrenCallback: (value: any, parentCategory?: CategoryTreeModel) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
  isCategorySelected?: (category: CategoryValue) => boolean;
  shouldRerender?: boolean;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  categoryTreeCode,
  init,
  childrenCallback,
  onChange,
  onClick,
  initCallback,
  isCategorySelected,
                                                     // @ts-ignore
                                                     shouldRerender,
  ...rest
}: CategoryTreeProps) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  const recursiveGetFirstSelectedCategoryLabel: (category: CategoryTreeModel) => string | undefined = category => {
    if (
      isCategorySelected &&
      isCategorySelected({
        id: category.id,
        code: category.code,
        label: category.label,
      })
    ) {
      return category.label;
    }
    return (category.children || []).reduce(
      (previous, subCategory) => previous || recursiveGetFirstSelectedCategoryLabel(subCategory),
      undefined as string | undefined
    );
  };

  React.useEffect(() => {
    setTree(undefined);
    init(categoryTreeCode).then(tree => {
      setTree(undefined); // We need this in case of tree switch. We should rework this component to make it able to change root
      setTree(tree);
      if (initCallback) {
        initCallback(tree.label, recursiveGetFirstSelectedCategoryLabel(tree));
      }
    });
  }, [categoryTreeCode]);

  if (!tree) {
    return <Tree value="" label="" isLoading={true} {...rest} />;
  }

  return (
    <RecursiveCategoryTree
      tree={tree}
      childrenCallback={childrenCallback}
      onChange={onChange}
      onClick={onClick}
      isCategorySelected={isCategorySelected}
      shouldRerender={shouldRerender}
      {...rest}
    />
  );
};

export type {CategoryTreeModel};
export {CategoryTree};
