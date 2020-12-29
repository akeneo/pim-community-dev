import React from 'react';
import {Tree} from 'akeneo-design-system';
import {TreeModel} from './CategoryTreeModel';
import {CategoryResponse, parseResponse} from './CategoryTreeRouting';

type RecursiveTreeProps = {
  tree: TreeModel;
  treeState: TreeModel;
  setTreeState: (tree: TreeModel) => void;
  onSelect?: (value: string) => void;
  onUnselect?: (value: string) => void;
  childrenRoute: (value: string) => string;
  selectable?: boolean;
  lockedCategoryIds?: number[];
  readOnly?: boolean;
  _isRoot?: boolean;
};

const RecursiveTree: React.FC<RecursiveTreeProps> = ({
  tree,
  treeState,
  setTreeState,
  onSelect,
  onUnselect,
  childrenRoute,
  selectable,
  lockedCategoryIds = [],
  readOnly = false,
  _isRoot = true,
  ...rest
}) => {
  const recursiveSet: (node: TreeModel, value: string, f: (node: TreeModel) => TreeModel) => TreeModel = (
    node,
    value,
    f
  ) => {
    if (node.value === value) {
      return f(node);
    }
    if (node.children) {
      return {...node, children: node.children.map(child => recursiveSet(child, value, f))};
    }
    return node;
  };

  const recursiveHas: (node: TreeModel, value: string, f: (node: TreeModel) => boolean) => boolean = (
    node,
    value,
    f
  ) => {
    if (node.value === value) {
      return f(node);
    }
    if (node.children) {
      return node.children.some(child => {
        return recursiveHas(child, value, f);
      });
    }
    return false;
  };

  const recursiveFilter: (node: TreeModel, f: (node: TreeModel) => boolean) => TreeModel[] = (node, f) => {
    let result: TreeModel[] = [];
    if (f(node)) {
      result.push(node);
    }
    if (node.children) {
      node.children.forEach(child => {
        result = result.concat(recursiveFilter(child, f));
      });
    }
    return result;
  };

  const handleOpen = (value: string) => {
    if (recursiveHas(treeState, value, node => typeof node.children !== 'undefined')) {
      return;
    }

    setTreeState(
      recursiveSet(treeState, value, (node: TreeModel) => {
        return {...node, loading: true};
      })
    );

    fetch(childrenRoute(value)).then(response => {
      response.json().then((json: CategoryResponse) => {
        const children: TreeModel[] = json.children
          ? json.children.map(child => {
              return parseResponse(child, readOnly, lockedCategoryIds);
            })
          : [];

        setTreeState(
          recursiveSet(treeState, value, node => {
            return {...node, loading: false, children};
          })
        );
      });
    });
  };

  const handleCheck = (value: string, selected: boolean) => {
    setTreeState(
      recursiveSet(treeState, value, node => {
        return {...node, selected};
      })
    );
    if (onSelect && selected) {
      onSelect(value);
    }
    if (onUnselect && !selected) {
      onUnselect(value);
    }
  };

  return (
    <Tree
      value={tree.value}
      label={tree.label}
      onOpen={handleOpen}
      isLoading={!!tree.loading}
      isLeaf={Array.isArray(tree.children) && tree.children.length == 0}
      selectable={selectable && !_isRoot}
      // TODO Rename this on onSelect/onUnselect
      onSelect={selected => handleCheck(tree.value, selected)}
      selected={!!tree.selected}
      readOnly={!!tree.readOnly}
      {...rest}
    >
      {tree.children &&
        tree.children.map(childNode => {
          return (
            <RecursiveTree
              tree={childNode}
              treeState={treeState}
              setTreeState={setTreeState}
              key={childNode.value}
              onSelect={onSelect}
              onUnselect={onUnselect}
              childrenRoute={childrenRoute}
              lockedCategoryIds={lockedCategoryIds}
              readOnly={tree.readOnly}
              selectable={selectable}
              _isRoot={false}
            />
          );
        })}
    </Tree>
  );
};

export {RecursiveTree};
