import {useCallback, useState} from 'react';
import {DashboardCategoryFilter, DashboardContextState} from '../../application/context/DashboardContext';

const useInitDashboardContextState = (): DashboardContextState => {
  const [familyCode, setFamilyCode] = useState<string | null>(null);
  const [category, setCategory] = useState<DashboardCategoryFilter | null>(null);

  const updateDashboardFilters = useCallback(
    (familyCode: string | null, category: DashboardCategoryFilter | null) => {
      setFamilyCode(familyCode);
      setCategory(category);
    },
    [setFamilyCode, setCategory]
  );

  return {
    familyCode,
    category,
    updateDashboardFilters,
  };
};

export {useInitDashboardContextState};
