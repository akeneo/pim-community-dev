import React from 'react';
import {Tree} from './Tree';

type TreeModel = {
  value: string;
  label: string;
  children?: TreeModel[];
  loading?: boolean;
  selected?: boolean;
};

const finalTree: TreeModel = {
  value: 'master',
  label: 'Master',
  children: [
    {value: 'tvs', label: 'TVs and projectors'},
    {
      value: 'cameras',
      label: 'Cameras',
      children: [
        {value: 'digital', label: 'Digital cameras', children: []},
        {value: 'camcorders', label: 'Camcorders', children: []},
        {value: 'subcategory', label: 'Subcategory', children: []},
      ],
    },
    {value: 'audio', label: 'Audio and Video', children: []},
    {value: 'print', label: 'Print and scan', children: []},
    {value: 'office', label: 'Office', children: []},
  ],
};

const setNodeLoading: (tree: TreeModel, value: string) => TreeModel = (tree, value) => {
  if (tree.value === value) {
    return {...tree, loading: true};
  }

  if (tree.children) {
    return {...tree, children: tree.children.map(subTree => setNodeLoading(subTree, value))};
  }

  return tree;
};

const setNodeChecked: (tree: TreeModel, value: string, checked: boolean) => TreeModel = (tree, value, checked) => {
  if (tree.value === value) {
    return {...tree, selected: checked};
  }

  if (tree.children) {
    return {...tree, children: tree.children.map(subTree => setNodeChecked(subTree, value, checked))};
  }

  return tree;
};

const setNodeLoaded: (tree: TreeModel, finalNode: TreeModel, value: string) => TreeModel = (
  tree,
  inMemoryTree,
  value
) => {
  if (tree.value === value) {
    return {
      ...tree,
      loading: false,
      children: inMemoryTree.children
        ? inMemoryTree.children.map(inMemorySubTree => {
            return {value: inMemorySubTree.value, label: inMemorySubTree.label};
          })
        : [],
    };
  }

  if (tree.children) {
    return {
      ...tree,
      children: tree.children.map((subTree, i) => {
        if (!inMemoryTree.children || !inMemoryTree.children[i]) {
          return subTree;
        }
        const inMemorySubTree = inMemoryTree.children[i];
        return setNodeLoaded(subTree, inMemorySubTree, value);
      }),
    };
  }

  return tree;
};

const isLoaded: (tree: TreeModel, value: string) => boolean = (tree, value) => {
  if (tree.value === value) {
    return typeof tree.children !== 'undefined';
  }

  if (tree.children) {
    return tree.children.some(subTree => isLoaded(subTree, value));
  }

  return false;
};

const onOpen = (value: string, tree: TreeModel, setTree: (tree: TreeModel) => void) => {
  if (isLoaded(tree, value)) {
    return;
  }
  setTree(setNodeLoading(tree, value));

  window.setTimeout(() => {
    setTree(setNodeLoaded(tree, finalTree, value));
  }, 400);
};

const onCheck = (value: string, checked: boolean, tree: TreeModel, setTree: (tree: TreeModel) => void) => {
  setTree(setNodeChecked(tree, value, checked));
};

export const RecursiveTree: React.FC<{
  tree: TreeModel;
  treeState: TreeModel;
  setTreeState: (tree: TreeModel) => void;
}> = ({tree, treeState, setTreeState, ...rest}) => {
  return (
    <Tree
      value={tree.value}
      label={tree.label}
      onOpen={value => onOpen(value, treeState, setTreeState)}
      isLoading={!!tree.loading}
      isLeaf={Array.isArray(tree.children) && tree.children.length == 0}
      selectable={true}
      onSelect={checked => onCheck(tree.value, checked, treeState, setTreeState)}
      selected={!!tree.selected}
      {...rest}
    >
      {tree.children &&
        tree.children.map(childNode => {
          return (
            <RecursiveTree tree={childNode} treeState={treeState} setTreeState={setTreeState} key={childNode.value} />
          );
        })}
    </Tree>
  );
};
