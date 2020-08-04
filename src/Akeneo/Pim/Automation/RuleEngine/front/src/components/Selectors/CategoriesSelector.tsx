import React from 'react';
import styled from 'styled-components';
import { VisuallyHidden } from 'reakit/VisuallyHidden';
import { usePopoverState, Popover, PopoverDisclosure } from 'reakit/Popover';
import { ValueColumn } from '../../pages/EditRules/components/conditions/style';
import { CategoryTreeFilter } from '../CategoryTree/CategoryTreeFilter';
import { Category, CategoryCode, LocaleCode } from '../../models';
import { NetworkLifeCycle } from '../CategoryTree/hooks/NetworkLifeCycle.types';
import {
  CategoryTreeModelWithOpenBranch,
  CategoryTreeModel,
} from '../CategoryTree/category-tree.types';
import { useTranslate } from '../../dependenciesTools/hooks';

const InputCategory = styled(ValueColumn)`
  position: relative;
  min-height: 40px;
  height: 100%;
`;

const CategoryArtifact = styled.li`
  z-index: 2;
`;

const CategoryArtifactDelete = styled.span`
  cursor: pointer;
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
  z-index: 2;
`;

type Props = {
  locale: LocaleCode;
  onDelete: (categoryCode: CategoryCode) => void;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  selectedCategories: Category[];
  initCategoryTreeOpenBranch: NetworkLifeCycle<
    CategoryTreeModelWithOpenBranch[]
  >;
  categoryTrees: NetworkLifeCycle<CategoryTreeModel[]>;
  categoryTreeSelected?: CategoryTreeModel;
  setCategoryTreeSelected: (category: CategoryTreeModel) => void;
};

const CategoriesSelector: React.FC<Props> = ({
  onDelete,
  selectedCategories,
  onSelectCategory,
  locale,
  initCategoryTreeOpenBranch,
  categoryTrees,
  categoryTreeSelected,
  setCategoryTreeSelected,
}) => {
  const translate = useTranslate();
  const popover = usePopoverState({
    gutter: 0,
    placement: 'bottom-start',
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
        <CategoryTreeFilter
          categoryTrees={categoryTrees}
          categoryTreeSelected={categoryTreeSelected}
          initCategoryTreeOpenBranch={initCategoryTreeOpenBranch}
          locale={locale}
          onSelectCategory={onSelectCategory}
          selectedCategories={selectedCategories}
          setCategoryTreeSelected={setCategoryTreeSelected}
        />
      </CategoryPopover>
      <div className='select2-container select2-container-multi select2'>
        <ul className='select2-choices'>
          {selectedCategories.map(category => {
            return (
              <CategoryArtifact
                className='select2-search-choice'
                key={category.code}>
                {getCategoryLabel(category, locale)}
                <CategoryArtifactDelete
                  tabIndex={0}
                  onClick={() => {
                    onDelete(category.code);
                  }}
                  className='select2-search-choice-close'
                  role='button'
                />
              </CategoryArtifact>
            );
          })}
        </ul>
      </div>
    </InputCategory>
  );
};

export { CategoriesSelector };
