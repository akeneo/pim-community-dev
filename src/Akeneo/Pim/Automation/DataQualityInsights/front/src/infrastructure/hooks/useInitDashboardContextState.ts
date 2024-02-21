import {useCallback, useEffect, useState} from 'react';
import {DashboardCategoryFilter, DashboardContextState} from '../../application/context/DashboardContext';
import {
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY,
} from '../../application/constant';

const useInitDashboardContextState = (
  familyCode: string | null,
  category: DashboardCategoryFilter | null
): DashboardContextState => {
  const [selectedFamilyCode, setSelectedFamilyCode] = useState<string | null>(null);
  const [selectedCategory, setSelectedCategory] = useState<DashboardCategoryFilter | null>(null);

  useEffect(() => {
    setSelectedFamilyCode(familyCode);
  }, [familyCode]);

  useEffect(() => {
    setSelectedCategory(category);
  }, [category]);

  const updateDashboardFilters = useCallback(
    (familyCode: string | null, category: DashboardCategoryFilter | null) => {
      setSelectedFamilyCode(familyCode);
      setSelectedCategory(category);

      if (category === null) {
        window.dispatchEvent(
          new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY, {
            detail: {
              familyCode: familyCode,
            },
          })
        );
        return;
      }

      window.dispatchEvent(
        new CustomEvent(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY, {
          detail: {
            categoryCode: category.code,
            categoryId: category.id,
            rootCategoryId: category.rootCategoryId,
          },
        })
      );
    },
    [setSelectedFamilyCode, setSelectedCategory]
  );

  return {
    category: selectedCategory,
    familyCode: selectedFamilyCode,
    updateDashboardFilters,
  };
};

export {useInitDashboardContextState};
