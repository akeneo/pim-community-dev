import React from 'react';
import {CategoryTree, CategoryTreeModel} from './CategoryTree';
import {BooleanInput} from 'akeneo-design-system/lib/components/Input/BooleanInput/BooleanInput';
import {Tree, getColor} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {CategoryTreeSwitcher} from './CategoryTreeSwitcher';
import styled from 'styled-components';

const CategoryTreesContainer = styled.div`
  height: calc(100vh - 110px);
  border-bottom: 1px solid ${getColor('grey', 80)};
  margin-bottom: 10px;
`;

const CategoryTreeContainer = styled.div`
  max-height: calc(100vh - 223px);
  overflow: hidden auto;
`;

type CategoryTreeRoot = {
  id: number;
  code: string;
  label: string;
  selected: boolean;
  tree?: CategoryTreeModel;
};

type CategoryTreesProps = {
  init: () => Promise<CategoryTreeRoot[]>;
  initTree: (
    treeId: number,
    treeLabel: string,
    treeCode: string,
    includeSubCategories: boolean
  ) => Promise<CategoryTreeModel>;
  childrenCallback: (value: any) => Promise<CategoryTreeModel[]>;
  onTreeChange: (treeId: number, treeLabel: string) => void;
  onCategoryClick: (
    selectedTreeId: number,
    selectedTreeRootId: number,
    selectedCategoryLabel: string,
    selectedTreeLabel: string
  ) => void;
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
  initCallback,
}) => {
  const translate = useTranslate();
  const [trees, setTrees] = React.useState<CategoryTreeRoot[]>();
  const [includeSubCategories, setIncludeSubCategories] = React.useState<boolean>(initialIncludeSubCategories);
  const [selectedTreeId, setSelectedTreeId] = React.useState<number>(initialSelectedTreeId);

  // This will reload the tree when includeSubCategories change.
  const customInitTree = React.useMemo(
    () => (tree: CategoryTreeRoot) => {
      return initTree(tree.id, tree.label, tree.code, includeSubCategories);
    },
    [includeSubCategories]
  );

  React.useEffect(() => {
    setTrees(undefined);
    init().then(categoryTreeRoots => setTrees(categoryTreeRoots));
  }, [includeSubCategories]);

  if (!trees) {
    return <Tree isLoading={true} label="" value="" />;
  }

  const switchTree = (treeId: number) => {
    setTrees(
      trees.map(tree => {
        return {...tree, selected: treeId === tree.id};
      })
    );
    setSelectedTreeId(treeId);
    onTreeChange(treeId, (trees.find(tree => tree.id === treeId) || trees[0]).label);
  };

  const handleClick = (category: {id: number; code: string; label: string}) => {
    setSelectedTreeId(category.id);
    const selectedTree = trees.find(tree => tree.selected) || trees[0];
    onCategoryClick(
      category.id,
      selectedTree.id,
      category.id === selectedTree.id ? '' : category.label,
      selectedTree.label
    );
  };

  const handleIncludeSubCategoriesChange = (value: boolean | null) => {
    onIncludeSubCategoriesChange(value as boolean);
    setIncludeSubCategories(value as boolean);
  };

  const handleInitCallback = (treeLabel: string, categoryLabel: string) => {
    if (!initCallback) {
      return undefined;
    }
    return initCallback(treeLabel, categoryLabel ? categoryLabel : translate('jstree.all'));
  };

  const AllProductsTree = (
    <Tree
      value={{id: -2, code: 'all_products'}}
      label={translate('jstree.all')}
      isLeaf={true}
      onClick={() => handleClick({id: -2, code: 'all_products', label: translate('jstree.all')})}
      selected={selectedTreeId === -2}
    />
  );

  return (
    <div>
      <CategoryTreesContainer>
        <CategoryTreeSwitcher trees={trees} onClick={switchTree} />
        <CategoryTreeContainer>
          {trees.map(tree => {
            return (
              tree.selected && (
                <CategoryTree
                  key={tree.code}
                  init={() => customInitTree(tree)}
                  childrenCallback={childrenCallback}
                  onClick={handleClick}
                  categoryId={selectedTreeId}
                  initCallback={handleInitCallback}
                />
              )
            );
          })}
          {AllProductsTree}
        </CategoryTreeContainer>
      </CategoryTreesContainer>
      <label>{translate('jstree.include_sub')}</label>
      <BooleanInput
        value={includeSubCategories}
        readOnly={false}
        yesLabel={translate('pim_common.yes')}
        noLabel={translate('pim_common.no')}
        onChange={handleIncludeSubCategoriesChange}
      />
    </div>
  );
};

export type {CategoryTreeRoot};
export {CategoryTrees};
