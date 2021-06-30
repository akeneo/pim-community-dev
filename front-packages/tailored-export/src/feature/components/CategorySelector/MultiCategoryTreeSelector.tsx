import React, {useState} from 'react';
import {TabBar, Badge} from 'akeneo-design-system';
import {getLabel, useUserContext, useTranslate} from '@akeneo-pim-community/shared';
import {CategorySelector} from './CategorySelector';
import {useCategoryTrees} from '../../hooks';

type MultiCategoryTreeSelectorProps = {
  categorySelection: string[];
  shouldIncludeSubCategories: boolean;
  onCategorySelection: (updatedCategorySelection: string[]) => void;
};

const MultiCategoryTreeSelector = ({
  categorySelection,
  onCategorySelection,
  shouldIncludeSubCategories,
}: MultiCategoryTreeSelectorProps) => {
  const [activeCategoryTree, setActiveCategoryTree] = useState<string>('');
  const categoryTrees = useCategoryTrees(categorySelection, setActiveCategoryTree, shouldIncludeSubCategories);
  const translate = useTranslate();
  const catalogLocale = useUserContext().get('catalogLocale');
  const sortedCategoryTrees = categoryTrees.sort((a, b) =>
    getLabel(a.labels, catalogLocale, a.code).localeCompare(getLabel(b.labels, catalogLocale, b.code))
  );

  return (
    <>
      <TabBar moreButtonTitle={translate('pim_common.more')}>
        {sortedCategoryTrees.map(categoryTree => (
          <TabBar.Tab
            key={categoryTree.code}
            isActive={activeCategoryTree === categoryTree.code}
            onClick={() => setActiveCategoryTree(categoryTree.code)}
          >
            {getLabel(categoryTree.labels, catalogLocale, categoryTree.code)}
            <Badge level={categoryTree.selectedCategoryCount > 0 ? 'primary' : 'tertiary'}>
              {categoryTree.selectedCategoryCount}
            </Badge>
          </TabBar.Tab>
        ))}
      </TabBar>
      {activeCategoryTree && (
        <CategorySelector
          categoryTreeCode={activeCategoryTree}
          initialCategoryCodes={categorySelection}
          onChange={onCategorySelection}
          shouldIncludeSubCategories={shouldIncludeSubCategories}
        />
      )}
    </>
  );
};

export {MultiCategoryTreeSelector};
