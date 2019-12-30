import {AttributeCode} from 'akeneoassetmanager/platform/model/structure/attribute';
import {Labels} from 'akeneoassetmanager/platform/model/label';

export type FamilyCode = string;
export type AttributeRequirements = {
  [key: string]: AttributeCode[];
};

export type Family = {
  code: FamilyCode;
  labels: Labels;
  attributeRequirements: AttributeRequirements;
};
