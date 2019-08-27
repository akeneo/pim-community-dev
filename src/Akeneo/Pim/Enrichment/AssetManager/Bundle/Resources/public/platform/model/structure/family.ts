import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';

export type FamilyCode = string;
type AttributeRequirements = {
  [key: string]: AttributeCode[];
};

export type Family = {
  code: FamilyCode;
  attribute_requirements: AttributeRequirements;
};
