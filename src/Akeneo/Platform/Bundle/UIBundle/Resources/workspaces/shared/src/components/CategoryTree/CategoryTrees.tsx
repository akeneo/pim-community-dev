import React from 'react';
import { CategoryTree, CategoryTreeModel } from "./CategoryTree";
import { BooleanInput } from 'akeneo-design-system/lib/components/Input/BooleanInput/BooleanInput';
import { Tree } from 'akeneo-design-system/lib/components/Tree/Tree';
const __ = require('oro/translator');

type CategoryTreeRoot = {
  id: number;
  code: string;
  label: string;
  selected: boolean;
  tree?: CategoryTreeModel;
};

type CategoryTreesProps = {
  init: () => Promise<CategoryTreeRoot[]>;
  initTree: (treeId: number, treeLabel: string, treeCode: string) => Promise<CategoryTreeModel>;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onTreeChange: (treeId: number, treeLabel: string) => void;
  onCategoryClick: (selectedTreeId: number, selectedTreeRootId: number, selectedCategoryLabel: string, selectedTreeLabel: string) => void;
  initialIncludeSubCategories: boolean;
  onIncludeSubCategoriesChange: (value: boolean) => void;
  initialSelectedTreeId: number;
  initCallback?: (treeLabel: string, categoryLabel?: string) => void;
};

const CategoryTrees: React.FC<CategoryTreesProps> = ({
  init,
  initTree,
  childrenCallback,
  onTreeChange,
  onCategoryClick,
  initialIncludeSubCategories,
  onIncludeSubCategoriesChange,
  initialSelectedTreeId,
  initCallback
}) => {
  const [trees, setTrees] = React.useState<CategoryTreeRoot[]>();
  const [includeSubCategories, setIncludeSubCategories] = React.useState<boolean>(initialIncludeSubCategories);
  const [selectedTreeId, setSelectedTreeId] = React.useState<number>(initialSelectedTreeId);

  React.useEffect(() => {
    init().then((categoryTreeRoots) => setTrees(categoryTreeRoots));
  }, []);

  if (!trees) {
    return <div>Loading</div>
  }

  const switchTree = (treeId: number) => {
    setTrees(trees.map((tree) => {
      return { ...tree, selected: treeId === tree.id };
    }));
    setSelectedTreeId(treeId);
    onTreeChange(treeId, (trees.find(tree => tree.id === treeId) || trees[0]).label);
  }

  const handleClick = (selectedNode: { id: number, code: string, label: string }) => {
    setSelectedTreeId(selectedNode.id);
    onCategoryClick(
      selectedNode.id,
      (trees.find(tree => tree.selected) || trees[0]).id,
      selectedNode.label,
      (trees.find(tree => tree.selected) || trees[0]).label
    );
  };

  const handleIncludeSubCategoriesChange = (value: boolean | null) => {
    onIncludeSubCategoriesChange(value as boolean);
    setIncludeSubCategories(value as boolean);
  }

  const handleInitCallback = (treeLabel: string, categoryLabel: string) => {
    if (!initCallback) {
      return undefined;
    }
    return initCallback(treeLabel, categoryLabel ? categoryLabel : __('jstree.products.all'));
  }

  return <div>
    <ul>
      {trees.map((tree) => {
        // These buttons will be updated after @julien's PR
        return <li key={tree.code}><button onClick={() => switchTree(tree.id)}>{tree.label}{tree.selected && ' - current'}</button></li>
      })}
    </ul>
    {trees.map((tree) => {
      return tree.selected &&
        <CategoryTree
          key={tree.code}
          init={() => initTree(tree.id, tree.label, tree.code)}
          childrenCallback={childrenCallback}
          onClick={handleClick}
          selectedCategoryId={selectedTreeId}
          initCallback={handleInitCallback}
        />
    })}
    <Tree
      value={{id: -2, code: 'TODOCHANGETHISNAME'}}
      label={__('jstree.products.all')}
      isLeaf={true}
      onClick={() => handleClick( { id: -2, code: 'TODOCHANGETHISNAME', label: __('jstree.products.all') })}
      selected={selectedTreeId === -2}
    />

    Include sub categories
    <BooleanInput
      value={includeSubCategories}
      readOnly={false}
      yesLabel={'Yes'}
      noLabel={'No'}
      onChange={handleIncludeSubCategoriesChange}
    />
  </div>;
};

export {CategoryTrees};
