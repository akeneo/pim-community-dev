import React, {useEffect, useState} from 'react';
import {TabBar} from 'akeneo-design-system';
import {useIsMounted, useRoute, getLabel, useUserContext, Category, useTranslate} from '@akeneo-pim-community/shared';
import {CategorySelector} from './CategorySelector';

type MultiCategoryTreeSelectorProps = {
  categoriesSelected: string[];
  onCategorySelected: (categoriesSelected: string[]) => void;
};

const MultiCategoryTreeSelector = ({categoriesSelected, onCategorySelected}: MultiCategoryTreeSelectorProps) => {
  const translate = useTranslate();
  const [activeCategoryTree, setActiveCategoryTree] = useState<string>('');
  const [categoryTrees, setCategoryTrees] = useState<Category[]>([]);
  const isMounted = useIsMounted();
  const route = useRoute('pim_enrich_category_rest_list');
  const catalogLocale = useUserContext().get('catalogLocale');

  useEffect(() => {
    const fetchCategories = async () => {
      const response = await fetch(route);
      const json = await response.json();

      if (isMounted()) {
        setCategoryTrees(json);
        setActiveCategoryTree(json[0].code);
      }
    };

    fetchCategories();
  }, [route, setCategoryTrees, isMounted]);

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
          </TabBar.Tab>
        ))}
      </TabBar>
      {activeCategoryTree && (
        <CategorySelector
          categoryTreeCode={activeCategoryTree}
          initialCategoryCodes={categoriesSelected}
          onChange={onCategorySelected}
        />
      )}
    </>
  );
};

export {MultiCategoryTreeSelector};
