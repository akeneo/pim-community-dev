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
  selectedTreeId?: number;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({init, childrenCallback, onChange, onClick, selectedTreeId, ...rest}) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  React.useEffect(() => {
    init().then(categoryTree => setTree(categoryTree));
  }, []);

  if (!tree) {
    return <Tree value="" label="" isLoading={true} {...rest} />;
  }

  return <RecursiveCategoryTree
    tree={tree}
    childrenCallback={childrenCallback}
    onChange={onChange}
    onClick={onClick}
    selectedTreeId={selectedTreeId}
    {...rest}
  />;
};

export type {CategoryTreeModel};
export {CategoryTree};
