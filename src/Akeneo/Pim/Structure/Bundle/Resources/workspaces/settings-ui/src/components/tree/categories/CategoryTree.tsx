import React, {FC} from 'react';
import {CategoryTreeModel} from '../../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../../providers';
import {OrderableTreeProvider} from '../../shared/providers/OrderableTreeProvider';

type Props = {
  root: CategoryTreeModel | null;
  rootLabel: string;
  sortable?: boolean; // @todo find a better name: editable?
  followCategory?: (category: CategoryTreeModel) => void;
  addCategory?: (parentCode: string, onCreate: () => void) => void;
  deleteCategory?: (identifier: number, label: string, numberOfProducts: number, onDelete: () => void) => void;
  // @todo define onCategoryMoved action
};

const CategoryTree: FC<Props> = ({root, rootLabel, sortable = false, ...rest}) => {
  /* @todo[PLG-94] show loading feedback when tree is null? */
  if (root === null) {
    return <>Tree {rootLabel}</>;
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
