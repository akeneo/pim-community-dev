import React from 'react';
import {CategoryTreeModel} from './CategoryTree';
import {Tree} from 'akeneo-design-system/lib/components/Tree/Tree';

type RecursiveCategoryTreeProps = {
  tree: CategoryTreeModel;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onChange?: (value: string, checked: boolean) => void;
  onClick?: any;
  selectedTreeId?: number;
};

type CategoryTreeValue = {
  id: number;
  code: string;
};

const RecursiveCategoryTree: React.FC<RecursiveCategoryTreeProps> = ({tree, childrenCallback, onChange, onClick, selectedTreeId}) => {
  const [treeState, setTreeState] = React.useState<CategoryTreeModel>(tree);

  const handleOpen = React.useCallback(() => {
    if (typeof treeState.children === 'undefined') {
      setTreeState(currentTreeState => ({...currentTreeState, loading: true}));
      childrenCallback(treeState.id).then(children => {
        setTreeState(currentTreeState => ({...currentTreeState, loading: false, children}));
      });
    }
  }, [treeState, setTreeState, childrenCallback]);

  const handleChange = (value: CategoryTreeValue, checked: boolean) => {
    setTreeState({...treeState, selected: checked});
    if (onChange) {
      onChange(value.code, checked);
    }
  };

  return (
    <Tree<CategoryTreeValue>
      label={treeState.label}
      value={{
        id: treeState.id,
        code: treeState.code,
      }}
      selected={typeof selectedTreeId === 'undefined' ? treeState.selected : selectedTreeId === treeState.id}
      isLoading={treeState.loading}
      readOnly={treeState.readOnly}
      selectable={treeState.selectable}
      isLeaf={Array.isArray(treeState.children) && treeState.children.length === 0}
      onChange={handleChange}
      onOpen={handleOpen}
      onClick={onClick}
    >
      {treeState.children &&
      treeState.children.map(childNode => {
          return (
            <RecursiveCategoryTree
              key={childNode.id}
              tree={childNode}
              onChange={onChange}
              childrenCallback={childrenCallback}
              onClick={onClick}
              selectedTreeId={selectedTreeId}
            />
          );
        })}
    </Tree>
  );
};

export {RecursiveCategoryTree};
