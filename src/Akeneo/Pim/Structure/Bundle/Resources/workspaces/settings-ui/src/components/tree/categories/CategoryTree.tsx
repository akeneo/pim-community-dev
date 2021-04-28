import React, {FC} from 'react';
import {CategoryTreeModel as CategoryTreeModel} from '../../../models';
import {Node} from './Node';
import {CategoryTreeProvider} from '../../providers';

type Props = {
  root: CategoryTreeModel;
  followCategory?: (category: CategoryTreeModel) => void;
};

const CategoryTree: FC<Props> = ({root, ...rest}) => {
  return (
    <CategoryTreeProvider root={root}>
      <Node id={root.id} label={root.label} {...rest} />
    </CategoryTreeProvider>
  );
};

export {CategoryTree};
