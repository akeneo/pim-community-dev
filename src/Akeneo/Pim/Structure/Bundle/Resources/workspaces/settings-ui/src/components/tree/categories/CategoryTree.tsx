import React, {FC} from 'react';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../../providers';

type Props = {
  root: CategoryTreeModel | null;
  rootLabel: string;
  followCategory?: (category: CategoryTreeModel) => void;
  // @todo add "draggable" props
  // @todo define onCategoryMoved action
};

const CategoryTree: FC<Props> = ({root, rootLabel, ...rest}) => {
  /* @todo[PLG-94] show loading feedback when tree is null? */
  if (root === null) {
    return <>Tree {rootLabel}</>;
  }

  return (
    <CategoryTreeProvider root={root}>
      <Node id={root.id} label={root.label} {...rest} />
    </CategoryTreeProvider>
  );
};

export {CategoryTree};
