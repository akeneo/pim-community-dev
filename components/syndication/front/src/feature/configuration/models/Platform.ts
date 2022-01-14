import {uuid} from 'akeneo-design-system';
import {DataMapping, createDataMapping} from './DataMapping';
import {RequirementCollection} from './Requirement';

type Platform = {
  code: string;
  label: string;
  families: {
    code: string;
    label: string;
  }[];
};

type Family = {
  code: string;
  label: string;
  requirements: RequirementCollection;
};

/**
 * We generate data mappings configuration from platform requirements
 */
const getInitialDataMappings = (
  requirements: RequirementCollection,
  initialDataMappings: DataMapping[]
): DataMapping[] => {
  const initialDataMappingTargets = initialDataMappings.map(({target}) => target.name);
  const missingRequirements = requirements.filter(({code}) => !initialDataMappingTargets.includes(code));
  const missingDataMappings = missingRequirements.map(requirement => createDataMapping(requirement, uuid()));

  return [...missingDataMappings, ...initialDataMappings];
};

export {getInitialDataMappings};
export type {Platform, Family};
