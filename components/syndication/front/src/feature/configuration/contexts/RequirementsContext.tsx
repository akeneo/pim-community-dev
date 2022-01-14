import {RequirementCollection, Requirement} from '../models';
import React, {createContext, useContext, ReactNode} from 'react';

const RequirementsContext = createContext<RequirementCollection | null>(null);

const useRequirement = (name: string): Requirement | null => {
  const requirements = useContext(RequirementsContext);

  if (null === requirements) {
    throw new Error('Requirements are not available');
  }

  const requirement = requirements.find(requirement => requirement.code === name);

  return requirement ?? null;
};

type RequirementsProviderProps = {
  requirements: RequirementCollection;
  children: ReactNode;
};

const RequirementsProvider = ({requirements, children}: RequirementsProviderProps) => {
  return <RequirementsContext.Provider value={requirements}>{children}</RequirementsContext.Provider>;
};

export {RequirementsProvider, useRequirement};

export type {RequirementCollection};
