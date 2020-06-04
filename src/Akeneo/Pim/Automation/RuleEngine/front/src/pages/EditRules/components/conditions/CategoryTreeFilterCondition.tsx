import React from 'react';
import styled from 'styled-components';
import { VisuallyHidden } from 'reakit/VisuallyHidden';
import { usePopoverState, Popover, PopoverDisclosure } from 'reakit/Popover';
import { Translate } from '../../../../dependenciesTools';
import { ValueColumn } from './style';
import { CategoryTreeFilter } from '../../../../components/CategoryTree/CategoryTreeFilter';
import { Category, CategoryCode, LocaleCode } from '../../../../models';
import { NetworkLifeCycle } from '../../../../components/CategoryTree/hooks/NetworkLifeCycle.types';
import {
  CategoryTreeModelWithOpenBranch,
  CategoryTreeModel,
} from '../../../../components/CategoryTree/category-tree.types';

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
`;

type Props = {
  locale: LocaleCode;
  onDelete: (categoryCode: CategoryCode) => void;
  onSelectCategory: (categoryCode: CategoryCode) => void;
  selectedCategories: Category[];
  translate: Translate;
  initCategoryTreeOpenBranch: NetworkLifeCycle<
    CategoryTreeModelWithOpenBranch[]
  >;
  categoriesTrees: NetworkLifeCycle<CategoryTreeModel[]>;
  categoryTreeSelected?: CategoryTreeModel;
  setCategoryTreeSelected: (category: CategoryTreeModel) => void;
};

const CategoryTreeFilterCondition: React.FC<Props> = ({
  onDelete,
  selectedCategories,
  onSelectCategory,
  locale,
  translate,
  initCategoryTreeOpenBranch,
  categoriesTrees,
  categoryTreeSelected,
  setCategoryTreeSelected,
}) => {
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
          categoriesTrees={categoriesTrees}
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

export { CategoryTreeFilterCondition };
