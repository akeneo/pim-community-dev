import React, {FC} from 'react';
import {CategoryTreeModel} from '../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../providers';
import {OrderableTreeProvider} from '../providers';
import {Tree} from './base';

type Props = {
  root: CategoryTreeModel | null;
  orderable?: boolean;
  followCategory?: (category: CategoryTreeModel) => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (
    identifier: number,
    label: string,
    code: string,
    numberOfProducts: number,
    onDelete: () => void
  ) => void;
};

const CategoryTree: FC<Props> = ({root, orderable = false, ...rest}) => {
  if (root === null) {
    return <Tree.Skeleton />;
  }

  return (
    <CategoryTreeProvider root={root}>
      <OrderableTreeProvider isActive={orderable}>
        <Node id={root.id} label={root.label} code={root.code} orderable={orderable} {...rest} />
      </OrderableTreeProvider>
    </CategoryTreeProvider>
  );
};

export {CategoryTree};
