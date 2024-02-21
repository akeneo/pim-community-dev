import React from 'react';
import {CategoryValue, RecursiveCategoryTree} from './RecursiveCategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type ParentCategoryTree = {
  code: string;
  parent: ParentCategoryTree;
} | null;

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
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
  isCategorySelected?: (category: CategoryValue, parentCategory: ParentCategoryTree) => boolean;
  isCategoryReadOnly?: (category: CategoryTreeModel, parentCategory: ParentCategoryTree) => boolean;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  categoryTreeCode,
  init,
  childrenCallback,
  onChange,
  onClick,
  initCallback,
  isCategorySelected,
  isCategoryReadOnly,
  ...rest
}) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  const recursiveGetFirstSelectedCategoryLabel: (category: CategoryTreeModel) => string | undefined = category => {
    if (
      isCategorySelected &&
      isCategorySelected(
        {
          id: category.id,
          code: category.code,
          label: category.label,
        },
        null
      )
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

  const recursiveCallback = (
    tree: CategoryTreeModel,
    value: string,
    callback: (node: CategoryTreeModel) => CategoryTreeModel
  ) => {
    let newTree = {...tree};
    if (newTree.code === value) {
      newTree = callback(newTree);
    }
    if (newTree.children) {
      newTree.children = newTree.children.map(child => recursiveCallback(child, value, callback));
    }

    return newTree;
  };

  const internalSetChecked = (value: string, selected: boolean) => {
    onChange?.(value, selected);
    setTree(tree => (tree ? recursiveCallback(tree, value, node => ({...node, selected})) : undefined));
  };

  const internalSetChildren = (value: string, children: CategoryTreeModel[]) => {
    setTree(tree => (tree ? recursiveCallback(tree, value, node => ({...node, children})) : undefined));
  };

  if (!tree) {
    return <Tree value="" label="" isLoading={true} {...rest} />;
  }

  return (
    <RecursiveCategoryTree
      tree={tree}
      parentTree={null}
      childrenCallback={childrenCallback}
      onClick={onClick}
      isCategorySelected={isCategorySelected}
      isCategoryReadOnly={isCategoryReadOnly}
      internalSetChecked={internalSetChecked}
      internalSetChildren={internalSetChildren}
      {...rest}
    />
  );
};

export type {CategoryTreeModel, ParentCategoryTree};
export {CategoryTree};
