import React, {FC} from 'react';
import {CategoryTreeModel} from '../../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../../providers';
import {OrderableTreeProvider} from '../../shared/providers/OrderableTreeProvider';
import {Tree} from '../../shared';

type Props = {
  root: CategoryTreeModel | null;
  sortable?: boolean; // @todo find a better name: editable?
  followCategory?: (category: CategoryTreeModel) => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => void;
};

const CategoryTree: FC<Props> = ({root, sortable = false, ...rest}) => {
  if (root === null) {
    return <Tree.Skeleton />;
  }

  return (
    <CategoryTreeProvider root={root}>
      <OrderableTreeProvider isActive={sortable}>
        <Node id={root.id} label={root.label} sortable={sortable} {...rest} />
      </OrderableTreeProvider>
    </CategoryTreeProvider>
  );
};

export {CategoryTree};
