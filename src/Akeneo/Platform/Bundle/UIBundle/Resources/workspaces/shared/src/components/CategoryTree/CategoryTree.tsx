import React from 'react';
import {RecursiveCategoryTree} from './RecursiveCategoryTree';
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
  init: () => Promise<CategoryTreeModel>;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  selectedCategoryId?: number;
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  init,
  childrenCallback,
  onChange,
  onClick,
  selectedCategoryId,
  initCallback,
  ...rest
}) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  const recursiveGetSelectedCategoryLabel: (categoryTree: CategoryTreeModel) => string | undefined = categoryTree => {
    if (categoryTree.id === selectedCategoryId) {
      return categoryTree.label;
    }
    if (categoryTree.children) {
      return categoryTree.children?.reduce((previous, subCategoryTree) => {
        return typeof previous !== 'undefined' ? previous : recursiveGetSelectedCategoryLabel(subCategoryTree);
      }, undefined);
    }
    return undefined;
  };

  React.useEffect(() => {
    init().then(tree => {
      setTree(tree);
      if (initCallback) {
        initCallback(tree.label, recursiveGetSelectedCategoryLabel(tree));
      }
    });
  }, []);

  if (!tree) {
    return <Tree value="" label="" isLoading={true} {...rest} />;
  }

  return (
    <RecursiveCategoryTree
      tree={tree}
      childrenCallback={childrenCallback}
      onChange={onChange}
      onClick={onClick}
      selectedCategoryId={selectedCategoryId}
      {...rest}
    />
  );
};

export type {CategoryTreeModel};
export {CategoryTree};
