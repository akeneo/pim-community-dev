import React, { useState } from 'react';
import styled from 'styled-components';
import { VisuallyHidden } from 'reakit/VisuallyHidden';
import { usePopoverState, Popover, PopoverDisclosure } from 'reakit/Popover';
import { Category, CategoryCode, LocaleCode } from '../../models';
import { CategoryTreeModel, CategoryTreeModelWithOpenBranch } from '../CategoryTree/category-tree.types';
import { useBackboneRouter, useTranslate } from '../../dependenciesTools/hooks';
import { CategoryTree } from "../CategoryTree/components/CategoryTree";
import { AkeneoSpinner } from "../AkeneoSpinner";
import { getInitCategoryTreeOpenedNode } from "../CategoryTree/category-tree.getters";
import { NetworkLifeCycle } from "../CategoryTree/hooks/NetworkLifeCycle.types";

const ContainerCategoryTree = styled.div`
  margin: 0 20px;
  border-top: ${({ theme }) => `1px solid ${theme.color.purple100}`};
`;

const InputCategory = styled.div`
  position: relative;
  min-height: 40px;
  height: 100%;
`;

const CategoryArtifact = styled.div`
  z-index: 2;
  margin-bottom: 5px;
`;

const CategoryArtifactDelete = styled.span`
  background-image: url('/bundles/pimui/images/icon-remove.svg') !important;
  background-repeat: no-repeat;
  background-position: center;
  cursor: pointer;
  height: 18px;
  width: 18px;
  background-size: 100%;
  display: block;
  position: absolute;
  right: 10px;
  top: 10px;
`;

const getCategoryLabel = (category: Category, locale: LocaleCode) => {
  return category.labels[locale] || `[${category.code}]`;
};

const CategoryPopover = styled(Popover)`
  background: white;
  height: 354px;
  width: 340px;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  overflow: auto;
  z-index: 1;
`;

type Props = {
  locale: LocaleCode;
  onDelete: (categoryCode: CategoryCode) => void;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  selectedCategory: Category;
  categoryTreeSelected: CategoryTreeModel;
};

const CategorySelector: React.FC<Props> = ({
  onDelete,
  selectedCategory,
  onSelectCategory,
  locale,
  categoryTreeSelected,
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const popover = usePopoverState({
    gutter: 0,
    placement: 'auto-start',
    modal: true,
  });
  const PopoverButton = (
    <button type='button'>
      <VisuallyHidden>
        {translate('pimee_catalog_rule.form.category.open_tree')}
      </VisuallyHidden>
    </button>
  );
  // Styled components and reakit doesn't work here for some unknown reasons.
  // So the css is inlined in the component.
  const PopoverButtonProps = {
    style: {
      background: 'none',
      border: 'none',
      cursor: 'pointer',
      height: 'inherit',
      left: 0,
      padding: '0',
      position: 'absolute',
      width: '100%',
      zIndex: 1,
    },
  };

  const [initCategoryTreeOpenBranch, setInitCategoryTreeOpenBranch] = useState<
    NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]>
    >({
    status: 'PENDING',
    data: [],
  });

  React.useEffect(() => {
    getInitCategoryTreeOpenedNode(
      router,
      categoryTreeSelected,
      [],
      setInitCategoryTreeOpenBranch
    );
  }, []);


  return (
    <InputCategory>
      <PopoverDisclosure {...popover}>
        {disclosureProps =>
          React.cloneElement(PopoverButton, {
            ...disclosureProps,
            ...PopoverButtonProps,
          })
        }
      </PopoverDisclosure>
      <CategoryPopover
        {...popover}
        aria-label={translate('pimee_catalog_rule.category.tree')}
        hideOnEsc
        hideOnClickOutside>
        <ContainerCategoryTree>
          {initCategoryTreeOpenBranch.status === 'PENDING' ||
          !initCategoryTreeOpenBranch.data ? (
            <AkeneoSpinner />
          ) : (
            <CategoryTree
              initCategoryTreeOpenBranch={initCategoryTreeOpenBranch.data[0]}
              categoryTree={categoryTreeSelected}
              locale={locale}
              onSelectCategory={onSelectCategory}
              selectedCategories={[]}
            />
          )}
        </ContainerCategoryTree>
      </CategoryPopover>
      <CategoryArtifact
        className='AknTextField'
        key={selectedCategory.code}>
        {getCategoryLabel(selectedCategory, locale)}
        <CategoryArtifactDelete
          tabIndex={0}
          onClick={() => {
            onDelete(selectedCategory.code);
          }}
          role='button'
        />
      </CategoryArtifact>
    </InputCategory>
  );
};

export { CategorySelector };
