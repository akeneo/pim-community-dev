import React, {useCallback, useState} from 'react';
import styled from 'styled-components';
import {
  Category,
  CategoryTree,
  CategoryTreeModel,
  CategoryValue,
  getLabel,
  useRouter,
  useUserContext,
} from '@akeneo-pim-community/shared';
import {CategoryResponse, parseResponse} from './CategoryTreeFetcher';

const CategoryTreeContainer = styled.div`
  height: calc(100vh - 300px);
  overflow: hidden auto;
`;

type CategorySelectorProps = {
  categoryTreeCode: string;
  onChange: (value: string[]) => void;
  initialCategoryCodes: string[];
};

const CategorySelector = ({categoryTreeCode, onChange, initialCategoryCodes}: CategorySelectorProps) => {
  const [selectedCategoryCodes, setSelectedCategoryCodes] = useState<string[]>(initialCategoryCodes);
  const catalogLocale = useUserContext().get('catalogLocale');
  const router = useRouter();

  const isCategorySelected: (category: CategoryValue) => boolean = category => {
    return selectedCategoryCodes.includes(category.code);
  };

  const handleCheckCategory = (value: string) => {
    const categoryCodeIsSelected = selectedCategoryCodes.includes(value);
    if (!categoryCodeIsSelected) {
      const newSelectedCategoryCodes = [...selectedCategoryCodes, value];
      setSelectedCategoryCodes(newSelectedCategoryCodes);
      onChange(newSelectedCategoryCodes);
    }
  };

  const handleUncheckCategory = (value: string) => {
    const newSelectedCategoryCodes = selectedCategoryCodes.filter(selectedCategoryCode => {
      return selectedCategoryCode !== value;
    });

    setSelectedCategoryCodes(newSelectedCategoryCodes);
    onChange(newSelectedCategoryCodes);
  };

  const handleChange = (value: string, checked: boolean) => {
    if (checked) {
      handleCheckCategory(value);
    } else {
      handleUncheckCategory(value);
    }
  };

  const childrenCallback: (id: number) => Promise<CategoryTreeModel[]> = async id => {
    const childrenUrl = router.generate('pim_enrich_categorytree_children', {_format: 'json', id});
    const response = await fetch(childrenUrl);
    const json: CategoryResponse[] = await response.json();

    return json.map(child =>
      parseResponse(child, {
        selectable: true,
      })
    );
  };

  const init = useCallback(
    async (categoryTreeCode?: string) => {
      const categoryRoute = router.generate('pim_enrich_category_rest_get', {identifier: categoryTreeCode});
      const categoryResponse = await fetch(categoryRoute);
      const category: Category = await categoryResponse.json();

      const childrenUrl = router.generate('pim_enrich_categorytree_children', {_format: 'json', id: category.id});
      const response = await fetch(childrenUrl);
      const json: CategoryResponse[] = await response.json();

      return {
        id: category.id,
        code: category.code,
        label: getLabel(category.labels, catalogLocale, category.code),
        selectable: true,
        children: json.map(child =>
          parseResponse(child, {
            selectable: true,
          })
        ),
      };
    },
    [catalogLocale, router]
  );

  return (
    <CategoryTreeContainer>
      <CategoryTree
        onChange={handleChange}
        childrenCallback={childrenCallback}
        categoryTreeCode={categoryTreeCode}
        init={init}
        isCategorySelected={isCategorySelected}
      />
    </CategoryTreeContainer>
  );
};

export {CategorySelector};
