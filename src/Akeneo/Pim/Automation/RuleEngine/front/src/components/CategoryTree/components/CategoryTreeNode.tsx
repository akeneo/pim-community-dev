import React, {useState} from 'react';
import {Category, LocaleCode, CategoryCode} from '../../../models';
import {useGetCategoryChildren} from '../hooks/useGetCategoryChildren';
import {NetworkLifeCycle} from '../hooks/NetworkLifeCycle.types';
import {NodeType, isBranch} from '../../Tree/tree.types';
import {
  TreeCheckbox,
  FolderIcons,
  TreeArrow,
  TreeLabel,
  TreeArrowButton,
  NodeContainer,
  NodeLineContainer,
} from '../../Tree';
import {useTranslate} from '../../../dependenciesTools/hooks';
import {
  CategoryTreeNodeModel,
  CategoryTreeModelWithOpenBranch,
} from '../category-tree.types';

const getCategoryId = (category: CategoryTreeNodeModel) =>
  category.attr.id.toString().replace('node_', '');

const getCategoryLabel = (category: CategoryTreeNodeModel) =>
  category.data ? category.data : '[' + category.attr['data-code'] + ']';

const getOpenedState = (
  nextNode: CategoryTreeModelWithOpenBranch | undefined,
  category: CategoryTreeNodeModel
) => nextNode?.state?.includes('open') || category?.state?.includes('open');

const getNextNodeFromCategoryTreeOpenBranch = (
  categoryCode: string,
  categoryTreeOpenBranchChildren: CategoryTreeModelWithOpenBranch[]
) =>
  categoryTreeOpenBranchChildren.find(
    node => categoryCode === node?.attr?.['data-code']
  );

type Props = {
  categoryCode: CategoryCode;
  categoryId: number;
  categoryLabel: string;
  categoryRootId: number;
  initCategoryTreeOpenBranch?: CategoryTreeModelWithOpenBranch;
  locale: LocaleCode;
  nodeType: NodeType;
  onSelect: (code: CategoryCode) => void;
  opened?: boolean;
  selectedCategories: Category[];
  withCheckbox?: boolean;
};

const CategoryTreeNode: React.FC<Props> = ({
  categoryCode,
  categoryId,
  categoryLabel,
  categoryRootId,
  initCategoryTreeOpenBranch,
  locale,
  nodeType,
  onSelect,
  opened = false,
  selectedCategories,
  withCheckbox,
}) => {
  const translate = useTranslate();
  const [nodeOpened, setNodeOpened] = useState<boolean>(opened);
  const [categoryChildrenFetch, setCategoryChildrenFetch] = useState<
    NetworkLifeCycle<CategoryTreeNodeModel[]>
  >({
    status: !opened || !isBranch(nodeType) ? 'COMPLETE' : 'PENDING',
    data: [],
  });
  useGetCategoryChildren(
    setCategoryChildrenFetch,
    locale,
    categoryId,
    nodeOpened,
    isBranch(nodeType)
  );
  const handlerSelect = () => onSelect(categoryCode);
  const selected = selectedCategories.some(
    category => category.code === categoryCode
  );
  return (
    <li
      role={isBranch(nodeType) ? 'treeitem' : 'none'}
      aria-expanded={nodeOpened}>
      <NodeLineContainer
        opacity={(categoryChildrenFetch.status === 'PENDING' && 0.5) || 1}>
        {isBranch(nodeType) && (
          <TreeArrowButton
            data-testid={`tree-arrow-button-${categoryCode}`}
            type='button'
            onClick={() => setNodeOpened(!nodeOpened)}>
            <TreeArrow opened={nodeOpened} translate={translate} />
          </TreeArrowButton>
        )}
        {withCheckbox && (
          <TreeCheckbox
            onClick={handlerSelect}
            selected={selected}
            translate={translate}
          />
        )}
        <FolderIcons
          nodeType={nodeType}
          selected={selected}
          translate={translate}
          onClick={handlerSelect}
        />
        <TreeLabel onClick={handlerSelect} selected={selected}>
          {categoryLabel}
        </TreeLabel>
      </NodeLineContainer>
      {nodeOpened && isBranch(nodeType) && (
        <NodeContainer>
          {categoryChildrenFetch.data?.map(
            (category: CategoryTreeNodeModel) => {
              const categoryCode = category.attr['data-code'];
              let nextNodeCategoryTreeOpenBranch = undefined;
              if (initCategoryTreeOpenBranch?.children) {
                nextNodeCategoryTreeOpenBranch = getNextNodeFromCategoryTreeOpenBranch(
                  categoryCode,
                  initCategoryTreeOpenBranch.children
                );
              }
              return (
                <CategoryTreeNode
                  categoryCode={categoryCode}
                  categoryId={Number(getCategoryId(category))}
                  categoryLabel={getCategoryLabel(category)}
                  categoryRootId={categoryRootId}
                  key={`${category.attr.id}`}
                  locale={locale}
                  onSelect={onSelect}
                  opened={getOpenedState(
                    nextNodeCategoryTreeOpenBranch,
                    category
                  )}
                  nodeType={
                    category.state !== 'leaf' ? NodeType.BRANCH : NodeType.LEAF
                  }
                  selectedCategories={selectedCategories}
                  withCheckbox={withCheckbox}
                  initCategoryTreeOpenBranch={nextNodeCategoryTreeOpenBranch}
                />
              );
            }
          )}
        </NodeContainer>
      )}
    </li>
  );
};

export {CategoryTreeNode};
