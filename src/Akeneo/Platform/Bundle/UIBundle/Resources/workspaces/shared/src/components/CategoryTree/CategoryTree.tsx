import React from 'react';
import {RecursiveTree} from './RecursiveTree';
import {TreeModel} from './CategoryTreeModel';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type CategoryTreeProps = {
  initialTree: TreeModel;
  childrenRoute: (value: string) => string;
  onChange?: (value: string, checked: boolean) => void;
  selectable?: boolean;
  lockedCategoryIds?: number[];
  readOnly?: boolean;
  onClick?: (value: string) => void;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  initialTree,
  childrenRoute,
  onChange,
  selectable,
  lockedCategoryIds = [],
  readOnly = false,
  onClick,
  ...rest
}) => {
  const [treeState, setTreeState] = React.useState<TreeModel>();
  React.useEffect(() => setTreeState(initialTree), [initialTree]);

  if (!treeState) {
    return <Tree value={''} label={''} isLoading={true} {...rest} />;
  }

  return (
    <RecursiveTree
      tree={treeState}
      treeState={treeState}
      setTreeState={setTreeState}
      onChange={onChange}
      onClick={onClick}
      childrenRoute={childrenRoute}
      selectable={selectable}
      lockedCategoryIds={lockedCategoryIds}
      readOnly={readOnly}
      {...rest}
    />
  );
};

export {CategoryTree};
