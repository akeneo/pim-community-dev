import React, {useCallback, useState} from 'react';
import styled from 'styled-components';
import {
  Category,
  CategoryTree,
  CategoryTreeModel,
  CategoryValue,
  ParentCategoryTree,
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
  initialCategoryCodes: string[];
  shouldIncludeSubCategories: boolean;
  onChange: (value: string[]) => void;
};

const CategorySelector = ({
  categoryTreeCode,
  onChange,
  initialCategoryCodes,
  shouldIncludeSubCategories,
}: CategorySelectorProps) => {
  const [selectedCategoryCodes, setSelectedCategoryCodes] = useState<string[]>(initialCategoryCodes);
  const catalogLocale = useUserContext().get('catalogLocale');
  const router = useRouter();

  const isParentCategoryIsSelected = (parentCategory: ParentCategoryTree): boolean => {
    if (parentCategory === null) {
      return false;
    }

    if (selectedCategoryCodes.includes(parentCategory.code)) {
      return true;
    }

    return isParentCategoryIsSelected(parentCategory.parent);
  };

  const isCategorySelected = (category: CategoryValue, parentCategory: ParentCategoryTree): boolean => {
    if (selectedCategoryCodes.includes(category.code)) {
      return true;
    }

    if (!shouldIncludeSubCategories) {
      return false;
    }

    return isParentCategoryIsSelected(parentCategory);
  };

  const isCategoryReadOnly = (_category: CategoryTreeModel, parentCategory: ParentCategoryTree): boolean => {
    if (!shouldIncludeSubCategories) return false;

    return isParentCategoryIsSelected(parentCategory);
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

  const childrenCallback = async (id: number): Promise<CategoryTreeModel[]> => {
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
        isCategoryReadOnly={isCategoryReadOnly}
      />
    </CategoryTreeContainer>
  );
};

export {CategorySelector};
