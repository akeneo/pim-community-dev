import React, {createContext, FC, useContext} from 'react';
import {useInitDashboardContextState} from '../../infrastructure/hooks/useInitDashboardContextState';

export type DashboardCategoryFilter = {
  id: string;
  code: string;
  rootCategoryId: string;
};

export type DashboardContextState = {
  familyCode: string | null;
  category: DashboardCategoryFilter | null;
  updateDashboardFilters: (familyCode: string | null, category: DashboardCategoryFilter | null) => void;
};
export const DashboardContext = createContext<DashboardContextState | undefined>(undefined);

export const useDashboardContext = () => {
  const dashboardContext = useContext(DashboardContext);
  if (!dashboardContext) {
    throw new Error('[DashboardContext]: dashboard context has not been properly initiated');
  }

  return dashboardContext;
};

export const DashboardContextProvider: FC = ({children}) => {
  const state = useInitDashboardContextState();

  return <DashboardContext.Provider value={state}>{children}</DashboardContext.Provider>;
};
