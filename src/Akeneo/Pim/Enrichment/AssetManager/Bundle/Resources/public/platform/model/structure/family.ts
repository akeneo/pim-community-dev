import {AttributeCode} from 'akeneopimenrichmentassetmanager/platform/model/structure/attribute';
import {Labels} from 'akeneopimenrichmentassetmanager/platform/model/label';

export type FamilyCode = string;
export type AttributeRequirements = {
  [key: string]: AttributeCode[];
};

export type Family = {
  code: FamilyCode;
  labels: Labels;
  attributeRequirements: AttributeRequirements;
};
