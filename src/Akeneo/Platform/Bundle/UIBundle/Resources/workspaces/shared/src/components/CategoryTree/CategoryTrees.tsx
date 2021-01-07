import React from 'react';
import { CategoryTree, CategoryTreeModel } from "./CategoryTree";
import { BooleanInput } from 'akeneo-design-system/lib/components/Input/BooleanInput/BooleanInput';
import { Tree } from 'akeneo-design-system/lib/components/Tree/Tree';

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
  onTreeChange: (treeId: number) => void;
  onClick: (selectedTreeId: number, selectedTreeRootId: number) => void;
  initialIncludeSubCategories: boolean;
  onIncludeSubCategoriesChange: (value: boolean) => void;
  initialSelectedTreeId: number;
};

const CategoryTrees: React.FC<CategoryTreesProps> = ({
  init,
  initTree,
  childrenCallback,
  onTreeChange,
  onClick,
  initialIncludeSubCategories,
  onIncludeSubCategoriesChange,
  initialSelectedTreeId
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
    onTreeChange(treeId);
  }

  const handleClick = (selectedNode: { id: number, code: string }) => {
    setSelectedTreeId(selectedNode.id);
    onClick(selectedNode.id, (trees.find(tree => tree.selected) || trees[0]).id);
  };

  const handleIncludeSubCategoriesChange = (value: boolean | null) => {
    onIncludeSubCategoriesChange(value as boolean);
    setIncludeSubCategories(value as boolean);
  }

  return <div>
    <ul>
      {trees.map((tree) => {
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
          selectedTreeId={selectedTreeId}
        />
    })}
    <Tree
      value={{
        id: -2,
        code: 'TODOCHANGETHISNAME'
      }}
      label={'All products'}
      isLeaf={true}
      onClick={() => handleClick( { id: -2, code: 'TODOCHANGETHISNAME' })}
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
