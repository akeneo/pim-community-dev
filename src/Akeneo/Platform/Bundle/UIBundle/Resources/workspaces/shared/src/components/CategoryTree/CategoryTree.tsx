import React from 'react';
import {RecursiveTree} from './RecursiveTree';
import {TreeModel} from './CategoryTreeModel';
import {CategoryResponse, parseResponse} from './CategoryTreeRouting';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type CategoryTreeProps = {
  initRoute: string;
  childrenRoute: (value: string) => string;
  onChange?: (value: string, checked: boolean) => void;
  selectable?: boolean;
  lockedCategoryIds?: number[];
  readOnly?: boolean;
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  initRoute,
  childrenRoute,
  onChange,
  selectable,
  lockedCategoryIds = [],
  readOnly = false,
  ...rest
}) => {
  const [treeState, setTreeState] = React.useState<TreeModel>();

  React.useEffect(() => {
    fetch(initRoute).then(response => {
      response.json().then((json: CategoryResponse[]) => {
        setTreeState(
          Array.isArray(json)
            ? parseResponse(json[0], readOnly, lockedCategoryIds)
            : parseResponse(json, readOnly, lockedCategoryIds)
        );
      });
    });
  }, []);

  if (!treeState) {
    return <Tree value={''} label={''} isLoading={true} {...rest} />;
  }

  return (
    <RecursiveTree
      tree={treeState}
      treeState={treeState}
      setTreeState={setTreeState}
      onChange={onChange}
      childrenRoute={childrenRoute}
      selectable={selectable}
      lockedCategoryIds={lockedCategoryIds}
      readOnly={readOnly}
      {...rest}
    />
  );
};

export {CategoryTree};
