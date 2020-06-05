import React, { useEffect, useState } from 'react';
import styled from 'styled-components';
import { VisuallyHidden } from 'reakit/VisuallyHidden';
import { usePopoverState, Popover, PopoverDisclosure } from 'reakit/Popover';
import { Translate } from '../../../../dependenciesTools';
import { CategoryTree } from '../../../../components/CategoryTree/components/CategoryTree';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from '../../../../components/CategoryTree/category-tree.types';
import { AkeneoSpinner } from '../../../../components';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import { getInitCategoryTreeOpenedNode } from '../../../../components/CategoryTree/category-tree.getters';
import { useBackboneRouter } from '../../../../dependenciesTools/hooks';

const CategoryPopover = styled(Popover)`
  background: white;
  height: 354px;
  width: 340px;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  overflow: auto;
`;

type Props = {
  translate?: Translate;
  categoryTree?: CategoryTreeModel;
  onClickCategory: any;
  //   selectedCategories: Category[];
  selectedCategoriesLength: number;
};

const AddToCategoryTree: React.FC<Props> = ({
  translate = (x: string) => x,
  categoryTree,
  onClickCategory,
  selectedCategoriesLength,
}) => {
  const popover = usePopoverState({
    gutter: 0,
    placement: 'auto-start',
    modal: true,
  });
  // Styled components and reakit doesn't work here for some unknown reasons.
  // So the css is inlined in the component.
  const PopoverButtonProps = {
    style: {
      background: 'none',
      cursor: 'pointer',
      height: '40px',
      padding: '0',
      width: '100%',
    },
  };

  const router = useBackboneRouter();

  const [initCategoryTreeOpenBranch, setInitCategoryTreeOpenBranch] = useState<
    NetworkLifeCycle<CategoryTreeModelWithOpenBranch[]>
  >({
    status: 'PENDING',
    data: [],
  });

  useEffect(() => {
    const initTree = async () => {
      if (categoryTree) {
        await getInitCategoryTreeOpenedNode(
          router,
          categoryTree,
          [],
          setInitCategoryTreeOpenBranch
        );
      }
    };
    initTree();
  }, []);
  if (
    !categoryTree ||
    !initCategoryTreeOpenBranch.data ||
    initCategoryTreeOpenBranch.status === 'PENDING'
  ) {
    return <AkeneoSpinner />;
  }

  console.log({ selectedCategoriesLength });

  const PopoverButton = (
    <button type='button'>
      {categoryTree.code}&nbsp;
      {selectedCategoriesLength}
      <VisuallyHidden>
        {translate('pimee_catalog_rule.form.category.open_tree')}
      </VisuallyHidden>
    </button>
  );

  return (
    <>
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
        <CategoryTree
          initCategoryTreeOpenBranch={initCategoryTreeOpenBranch.data[0]}
          locale='en_US'
          categoryTree={categoryTree}
          onSelectCategory={value => onClickCategory(categoryTree.id, value)}
          selectedCategories={[]}
        />
      </CategoryPopover>
    </>
  );
};

export { AddToCategoryTree };
