import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';

export type FamilyCode = string;
export type AttributeRequirements = {
  [key: string]: AttributeCode[];
};

export type Family = {
  code: FamilyCode;
  attributeRequirements: AttributeRequirements;
};
