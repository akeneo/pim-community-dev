import React from 'react';
import {RecursiveCategoryTree} from './RecursiveCategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type CategoryTreeModel = {
  id: number;
  code: string;
  label: string;
  children?: CategoryTreeModel[];
  selectable: boolean;
  loading?: boolean;
  selected?: boolean;
  readOnly?: boolean;
};

type CategoryTreeProps = {
  init: () => Promise<CategoryTreeModel>;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange: (value: string, checked: boolean) => void;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({init, childrenCallback, onChange, ...rest}) => {
  const [tree, setTree] = React.useState<CategoryTreeModel>();

  React.useEffect(() => {
    init().then(categoryTree => setTree(categoryTree));
  }, []);

  if (!tree) {
    return <Tree value="" label="" isLoading={true} {...rest} />;
  }

  return <RecursiveCategoryTree tree={tree} childrenCallback={childrenCallback} onChange={onChange} {...rest} />;
};

export type {CategoryTreeModel};
export {CategoryTree};
