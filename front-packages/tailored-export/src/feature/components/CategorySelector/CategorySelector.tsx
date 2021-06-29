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
  const [shouldUpdateChildren, forceUpdateChildren] = useState<boolean>(false);
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

  const isCategoryReadOnly = (category: CategoryTreeModel, parentCategory: ParentCategoryTree): boolean => {
    if (category.readOnly) return true;
    if (!shouldIncludeSubCategories || selectedCategoryCodes.includes(category.code)) return false;

    return isParentCategoryIsSelected(parentCategory);
  }

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

  const childrenCallback = async (id: number, parentTree: ParentCategoryTree): Promise<CategoryTreeModel[]> => {
    const childrenUrl = router.generate('pim_enrich_categorytree_children', {_format: 'json', id});
    const response = await fetch(childrenUrl);
    const json: CategoryResponse[] = await response.json();
console.log(json.map(child =>
  parseResponse(child, {
    selectable: true,
    parent: parentTree
  })
));
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
        parent: null,
      };

      return {
        ...categoryTree,
        children: json.map(child =>
          parseResponse(child, {
            selectable: true,
            parent: {
              code: category.code,
              parent: null
            },
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
        isCategoryReadOnly={isCategoryReadOnly}
        shouldRerender={shouldUpdateChildren}
      />
    </CategoryTreeContainer>
  );
};

export {CategorySelector};
