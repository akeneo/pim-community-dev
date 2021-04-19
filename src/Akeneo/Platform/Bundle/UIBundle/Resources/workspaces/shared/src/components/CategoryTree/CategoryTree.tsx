import React, {ReactNode} from 'react';
import {CategoryValue, RecursiveCategoryTree} from './RecursiveCategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';
import {TreeStyle} from "akeneo-design-system";

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
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
  isCategorySelected?: (category: CategoryValue) => boolean;
  actions?: (category: CategoryValue, isRoot: boolean) => ReactNode[];
  style?: TreeStyle,
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  init,
  childrenCallback,
  onChange,
  onClick,
  initCallback,
  isCategorySelected,
  ...rest
}) => {
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
    init().then(tree => {
      setTree(tree);
      if (initCallback) {
        initCallback(tree.label, recursiveGetFirstSelectedCategoryLabel(tree));
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
      isCategorySelected={isCategorySelected}
      isRoot={true}
      {...rest}
    />
  );
};

export type {CategoryTreeModel};
export {CategoryTree};
