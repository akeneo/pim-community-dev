import React from 'react';
import {CategoryTree, CategoryTreeModel, ParentCategoryTree} from './CategoryTree';
import {BooleanInput} from 'akeneo-design-system/lib/components/Input/BooleanInput/BooleanInput';
import {Tree, getColor} from 'akeneo-design-system';
import {useTranslate} from '../../hooks';
import {CategoryTreeSwitcher} from './CategoryTreeSwitcher';
import styled from 'styled-components';
import {CategoryValue} from './RecursiveCategoryTree';

const CategoryTreesContainer = styled.div`
  height: calc(100vh - 110px);
  border-bottom: 1px solid ${getColor('grey', 80)};
  margin-bottom: 10px;
`;

const CategoryTreeContainer = styled.div`
  max-height: calc(100vh - 223px);
  overflow: hidden auto;
`;

type CategoryTreeCode = string;

type CategoryTreeRoot = {
  id: number;
  code: CategoryTreeCode;
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
  onTreeChange: (treeId: number, treeLabel: string, selectedCategoryId: number) => void;
  onCategoryClick: (
    selectedTreeId: number,
    selectedTreeRootId: number,
    selectedCategoryLabel: string,
    selectedTreeLabel: string
  ) => void;
  initialIncludeSubCategories: boolean;
  onIncludeSubCategoriesChange: (value: boolean) => void;
  initialSelectedNodeId: number;
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
  initialSelectedNodeId,
  initCallback,
}) => {
  const translate = useTranslate();
  const [trees, setTrees] = React.useState<CategoryTreeRoot[]>();
  const [includeSubCategories, setIncludeSubCategories] = React.useState<boolean>(initialIncludeSubCategories);
  const [selectedNodeId, setSelectedNodeId] = React.useState<number>(initialSelectedNodeId);

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
    // Keep the selected filter if "All Products" (-2) or "Unclassified Products" (-1) were selected, select CategoryTree otherwise
    setSelectedNodeId(previousSelectedNodeId => {
      return previousSelectedNodeId > 0 ? treeId : previousSelectedNodeId;
    });
    onTreeChange(treeId, (trees.find(tree => tree.id === treeId) || trees[0]).label, selectedNodeId);
  };

  const handleClick = (category: {id: number; code: string; label: string}) => {
    setSelectedNodeId(category.id);
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

  const handleInitCallback = (treeLabel: string, categoryLabel?: string) => {
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
      selected={selectedNodeId === -2}
    />
  );

  const isCategorySelected: (category: CategoryValue, _: ParentCategoryTree) => boolean = category => {
    return category.id === selectedNodeId;
  };

  return (
    <>
      <CategoryTreesContainer>
        <CategoryTreeSwitcher trees={trees} onClick={switchTree} />
        <CategoryTreeContainer>
          {trees.map(tree => {
            return (
              tree.selected && (
                <CategoryTree
                  key={tree.code}
                  categoryTreeCode={tree.code}
                  init={() => customInitTree(tree)}
                  childrenCallback={childrenCallback}
                  onClick={handleClick}
                  isCategorySelected={isCategorySelected}
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
    </>
  );
};

export type {CategoryTreeRoot, CategoryTreeCode};
export {CategoryTrees};
