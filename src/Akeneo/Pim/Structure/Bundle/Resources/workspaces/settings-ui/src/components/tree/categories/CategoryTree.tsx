import React, {FC} from 'react';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../../providers';

type Props = {
  tree: CategoryTreeModel;
  followCategory?: (category: CategoryTreeModel) => void;
};

const CategoryTree: FC<Props> = ({tree, ...rest}) => {
  return (
    <CategoryTreeProvider tree={tree}>
      <Node id={tree.id} label={tree.label} {...rest} />
    </CategoryTreeProvider>
  );
};

export {CategoryTree};
