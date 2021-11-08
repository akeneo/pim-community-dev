import React, {useState} from 'react';
import styled from 'styled-components';
import {VisuallyHidden} from 'reakit/VisuallyHidden';
import {usePopoverState, Popover, PopoverDisclosure} from 'reakit/Popover';
import {Category, CategoryCode, LocaleCode} from '../../models';
import {
  CategoryTreeModel,
  CategoryTreeModelWithOpenBranch,
} from '../CategoryTree/category-tree.types';
import {useBackboneRouter, useTranslate} from '../../dependenciesTools/hooks';
import {CategoryTree} from '../CategoryTree/components/CategoryTree';
import {AkeneoSpinner} from '../AkeneoSpinner';
import {getInitCategoryTreeOpenedNode} from '../CategoryTree/category-tree.getters';
import {NetworkLifeCycle} from '../CategoryTree/hooks/NetworkLifeCycle.types';

const ContainerCategoryTree = styled.div`
  margin: 10px 20px;
`;

const InputCategory = styled.div`
  position: relative;
  min-height: 40px;
  height: 100%;
  max-width: 300px;
`;

const CategoryArtifact = styled.div`
  z-index: 2;
  margin-bottom: 5px;
  color: ${({theme}): string => theme.color.grey120};
  padding-left: 14px;
`;

const CategoryArtifactIcon = styled.span`
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

const CategoryArtifactDelete = styled(CategoryArtifactIcon)`
  background-image: url('/bundles/pimui/images/icon-remove.svg') !important;
  z-index: 1;
`;

const CategoryArtifactDropdown = styled(CategoryArtifactIcon)`
  background-image: url('/bundles/pimui/images/icon-down.svg') !important;
`;

const getCategoryLabel = (category: Category, locale: LocaleCode) => {
  return category.labels[locale] || `[${category.code}]`;
};

const CategoryPopover = styled(Popover)`
  background: white;
  max-height: 354px;
  width: 458px;
  box-shadow: 0px 0px 4px 0px rgba(0, 0, 0, 0.3);
  overflow: auto;
  z-index: 1;
`;

type Props = {
  locale: LocaleCode;
  onDelete?: (categoryCode: CategoryCode) => void;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  selectedCategory?: Category;
  categoryTreeSelected: CategoryTreeModel;
};

const CategorySelector: React.FC<Props> = ({
  onDelete,
  selectedCategory,
  onSelectCategory,
  locale,
  categoryTreeSelected,
  ...remainingProps
}) => {
  const router = useBackboneRouter();
  const translate = useTranslate();
  const popover = usePopoverState({
    gutter: 0,
    placement: 'bottom-start',
    modal: true,
    unstable_offset: [1, 0],
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
      height: '40px',
      left: 0,
      padding: '0',
      position: 'absolute',
      width: '300px',
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

  const handleDelete = (categoryCode: CategoryCode) => {
    if (onDelete) {
      onDelete(categoryCode);
    }
  };

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
              onSelectCategory={categoryCode => {
                popover.hide();
                onSelectCategory(categoryCode);
              }}
              selectedCategories={selectedCategory ? [selectedCategory] : []}
            />
          )}
        </ContainerCategoryTree>
      </CategoryPopover>
      <CategoryArtifact
        {...remainingProps}
        className='AknTextField category-artifact'>
        {selectedCategory ? (
          <>
            {getCategoryLabel(selectedCategory, locale)}
            <CategoryArtifactDelete
              tabIndex={0}
              onClick={() => handleDelete(selectedCategory.code)}
              role='button'
            />
          </>
        ) : (
          <>
            {translate(
              'pimee_catalog_rule.form.edit.actions.category.select_category'
            )}
            <CategoryArtifactDropdown tabIndex={0} role='button' />
          </>
        )}
      </CategoryArtifact>
    </InputCategory>
  );
};

export {CategorySelector};
