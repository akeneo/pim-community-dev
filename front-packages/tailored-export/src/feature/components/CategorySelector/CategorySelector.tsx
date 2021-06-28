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
  const [shouldUpdateChildren, forceUpdateChildren] = useState<boolean>(false);
  const catalogLocale = useUserContext().get('catalogLocale');
  const router = useRouter();

  const isCategorySelected: (category: CategoryValue, parentCategory?: CategoryTreeModel | null) => boolean = (
    category,
    parentCategory
  ) => {
    if (selectedCategoryCodes.includes(category.code)) {
      return true;
    }

    return shouldIncludeSubCategories
      ? parentCategory !== undefined && parentCategory !== null && isCategorySelected(parentCategory, parentCategory.parent)
      : false;
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
    if (shouldIncludeSubCategories) {
      forceUpdateChildren(!shouldUpdateChildren);
    }
  };

  const childrenCallback: (id: number, parentTree?: CategoryTreeModel) => Promise<CategoryTreeModel[]> = async (id, parentTree) => {
    const childrenUrl = router.generate('pim_enrich_categorytree_children', {_format: 'json', id});
    const response = await fetch(childrenUrl);
    const json: CategoryResponse[] = await response.json();

    return json.map(child =>
      parseResponse(child, {
        selectable: true,
        parent: parentTree
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

      const categoryTree = {
        id: category.id,
        code: category.code,
        label: getLabel(category.labels, catalogLocale, category.code),
        selectable: true,
      };

      return {
        ...categoryTree,
        children: json.map(child =>
          parseResponse(child, {
            selectable: true,
            parent: categoryTree,
          })
        ),
      }
    },
    [catalogLocale, router]
  );

  return (
    <CategoryTreeContainer>
      <CategoryTree
        categoryTreeCode={categoryTreeCode}
        init={init}
        onChange={handleChange}
        childrenCallback={childrenCallback}
        isCategorySelected={isCategorySelected}
        shouldRerender={shouldUpdateChildren}
      />
    </CategoryTreeContainer>
  );
};

export {CategorySelector};
