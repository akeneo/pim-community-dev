type RequirementType =
  | 'string'
  | 'boolean'
  | 'integer'
  | 'number'
  | 'date'
  | 'url'
  | 'string_collection'
  | 'measurement'
  | 'price'
  | 'limited_string';

type Requirement = {
  code: string;
  label: string;
  help: string;
  group: string;
  examples: string[];
  type: RequirementType;
  required: boolean;
  options?: {
    [key: string]: any;
  };
};

type RequirementCollection = Requirement[];

const getRequirementLabel = (requirement: Requirement): string =>
  !requirement.label ? requirement.code : requirement.label;

const searchRequirements = (requirements: Requirement[], searchValue: string): Requirement[] =>
  requirements.filter(
    ({code, label}) =>
      (code ?? '').toLowerCase().includes(searchValue.toLowerCase()) ||
      (label ?? '').toLowerCase().includes(searchValue.toLowerCase())
  );

const supportMultipleSources = (requirement: Requirement) => ['string'].includes(requirement.type);

export {supportMultipleSources, searchRequirements, getRequirementLabel};
export type {RequirementCollection, Requirement, RequirementType};
