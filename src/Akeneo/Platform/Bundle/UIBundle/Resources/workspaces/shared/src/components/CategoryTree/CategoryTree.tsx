import React from "react";
import { RecursiveTree } from "./RecursiveTree";
import { TreeModel } from "./CategoryTreeModel";
import { CategoryResponse, parseResponse } from "./CategoryTreeRouting";
import { Tree } from "akeneo-design-system/lib/components/Tree/Tree";

type CategoryTreeProps = {
  initRoute: string;
  childrenRoute: (value: string) => string;
  onSelect?: (value: string) => void;
  onUnselect?: (value: string) => void;
  selectable?: boolean;
  lockedCategoryIds?: number[];
};

const CategoryTree: React.FC<CategoryTreeProps> = ({
  initRoute,
  childrenRoute,
  onSelect,
  onUnselect,
  selectable,
  lockedCategoryIds = [],
  ...rest
}) => {
  const [treeState, setTreeState] = React.useState<TreeModel>();

  React.useEffect(() => {
    fetch(initRoute).then((response) => {
      response.json().then((json: CategoryResponse[]) => {
        setTreeState(
          Array.isArray(json) ? parseResponse(json[0], lockedCategoryIds) : parseResponse(json, lockedCategoryIds)
        );
      });
    });
  }, []);

  if (!treeState) {
    return <Tree value={''} label={''} isLoading={true} {...rest}/>
  }

  return <RecursiveTree
    tree={treeState}
    treeState={treeState}
    setTreeState={setTreeState}
    onSelect={onSelect}
    onUnselect={onUnselect}
    childrenRoute={childrenRoute}
    selectable={selectable}
    lockedCategoryIds={lockedCategoryIds}
    {...rest}
  />;
}

export { CategoryTree };
