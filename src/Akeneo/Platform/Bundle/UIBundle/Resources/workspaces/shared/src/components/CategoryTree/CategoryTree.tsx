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
  categoryId?: number;
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  init,
  childrenCallback,
  onChange,
  onClick,
  categoryId,
  initCallback,
  ...rest
}) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  const recursiveGetSelectedCategoryLabel: (categoryTree: CategoryTreeModel) => string | undefined = category => {
    if (category.id === categoryId) {
      return category.label;
    }
    return (category.children || []).reduce(
      (previous, subCategory) => previous || recursiveGetSelectedCategoryLabel(subCategory),
      undefined as string | undefined
    );
  };

  React.useEffect(() => {
    setTree(undefined);
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
      selectedCategoryId={categoryId}
      {...rest}
    />
  );
};

export type {CategoryTreeModel};
export {CategoryTree};
